<?php
namespace QuickerFaster\LaravelUI\Http\Livewire\Auth;

use App\Modules\System\Models\Tenant;
use App\Modules\System\Models\Company;
use App\Models\User;
use App\Modules\Access\Models\Role;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Str;


class QuickConfiguration extends Component
{
    public $employee_count = '1-10';
    public $industry = 'technology';
    public $active_modules = ['hr'];
    public $main_module = 'hr';
    public $dependency_modules = ['system','access'];
    
    public $company;

    // Configuration options (same as before)
    public $employeeCounts = [
        '1-10' => '1-10 employees',
        '11-50' => '11-50 employees', 
        '51-200' => '51-200 employees',
        '201-500' => '201-500 employees',
        '501+' => '501+ employees'
    ];
    
    public $industries = [
        'Technology' => 'Technology',
        'Healthcare' => 'Healthcare',
        'Retail' => 'Retail',
        'Manufacturing' => 'Manufacturing',
        'Professional_Services' => 'Professional_Services',
        'Education' => 'Education',
        'Agricultural' => 'Agricultural',
        'Other' => 'Other'
    ];
    
    public $availableModules = [
        'hr' => 'Core HR',
        'payroll' => 'Payroll',
        'time_tracking' => 'Time Tracking',
        'recruitment' => 'Recruitment',
        'performance' => 'Performance',
        'benefits' => 'Benefits'
    ];

    public function mount()
    {
        // User is NOT logged in yet - we're using session
        $companyId = session('verified_company_id');
        if (!$companyId) {
            return redirect()->route('register');
        }
        
        $this->company = Company::findOrFail($companyId);
        
        if (!$this->company || !$this->company->domain_verified) {
            return redirect()->route('register');
        }
    }

public function submit()
{
    DB::transaction(function () {
        // Update company configuration
        $this->updateCompanyConfiguration();
        
        // Create tenant infrastructure
        $databaseSlot = $this->reserveDatabaseSlot();
        $databaseName = $databaseSlot->name;
        
        $this->ensureDatabaseExists($databaseName);
        $tenant = $this->createTenantWithDatabase($databaseName);
        $this->linkDatabaseToTenant($databaseSlot->id, $tenant->id);
        $this->runTenantMigrations($tenant);
        
        // 👈 MANUALLY SWITCH TO TENANT DATABASE AND KEEP IT SWITCHED
        $this->switchToTenantDatabase($tenant);
        
        // Now create user and roles in tenant context
        $this->seedTenantData();
        $user = $this->createTenantAdminUser($tenant);
        
        // Login the user (now in tenant context)
        auth()->login($user);
        
        $this->activateCompany();
        session()->forget('verified_company_id');

    });

    // 👈 USE URL PARAMETERS INSTEAD OF SESSION
    $loginToken = $this->generateLoginToken();
    return redirect("http://{$this->getFullDomain()}/auto-login?token=$loginToken"); 

}



protected function generateLoginToken()
{
    $token = sha1(random_bytes(32));
    
    // Store in cache for 10 minutes
    \Cache::put('login_token:' . $token, [
        'user_email' => $this->company->billing_email,
        'tenant_id' => $this->company->subdomain,
    ], 600); // 10 minutes
    
    return $token;
}



protected function switchToTenantDatabase(Tenant $tenant)
{
    $databaseName = $tenant->getDatabaseName();
    
    // Configure the tenant connection
    config(['database.connections.tenant' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => $databaseName,
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ]]);
    
    // Set as default connection
    config(['database.default' => 'tenant']);
    
    // Refresh the connection
    DB::purge('tenant');
    DB::reconnect('tenant');
    
    
    \Log::info('Switched to tenant database', ['database' => $databaseName]);
}



protected function seedTenantData()
{
    // Run all necessary seeders for this tenant
    // Since we're already in tenant context, no need for --database parameter
    Artisan::call('db:seed', [
        '--class' => 'App\Modules\Access\Database\Seeders\RoleSeeder',
        '--force' => true,
    ]);
}


protected function createTenantAdminUser(Tenant $tenant)
{

   // Debug: Check which database we're connected to
    $currentDb = DB::connection()->getDatabaseName();
    \Log::info('Current database before creating user:', ['database' => $currentDb]);



    // Get the original password from registration (from central DB)
    $centralUser = \App\Models\User::where('email', $this->company->billing_email)
                                   ->where('company_id', $this->company->id)
                                   ->first();

    // Company name as default password
    $password = $centralUser ? $centralUser->password : Hash::make(Str::ucfirst($this->company->name));

    // Create user in tenant database (we're now in tenant context)
    $user = \App\Models\User::create([
        'name' => 'Admin',
        'email' => $this->company->billing_email,
        'password' => $password,
        'company_id' => $this->company->id,
    ]);

    // Assign admin role (now using tenant database)
    $adminRole = \App\Modules\Access\Models\Role::where('name', 'admin')->first();
    
    if (!$adminRole) {
        // If roles weren't seeded properly, create admin role manually
        $adminRole = \App\Modules\Access\Models\Role::create([
            'name' => 'admin',
            'description' => 'Manage users, settings, and system operations',
            'guard_name' => 'web',
            'editable' => 'No',
        ]);
    }
    
    $user->assignRole($adminRole);

    \Log::info('Created tenant admin user', [
        'tenant' => $tenant->id,
        'user' => $user->email
    ]);

    return $user;
}




    protected function getTenantDashboardUrl($module): string
    {
        return "http://{$this->getFullDomain()}/{$module}/dashboard";
    }



    protected function getOrCreateAdminUser()
    {
        // Check if user already exists (from registration)
        $user = User::where('email', $this->company->billing_email)
                    ->where('company_id', $this->company->id)
                    ->first();
        
        if ($user) {
            return $user;
        }
        
        // Create user if doesn't exist (fallback)
        return User::create([
            'name' => 'Admin',
            'email' => $this->company->billing_email,
            'password' => Hash::make(str_random(16)), // Temporary, user will reset
            'company_id' => $this->company->id,
        ]);
    }


    protected function assignAdminRole($user)
    {
        $adminRole = Role::where('name', 'admin')->first();
        
        if ($adminRole) {
            $user->assignRole($adminRole);
        } else {
            // Fallback: create admin role
            $adminRole = Role::create([
                'name' => 'admin',
                'description' => 'Manage users, settings, and system operations',
                'guard_name' => 'web',
                'editable' => 'No',
            ]);
            $user->assignRole($adminRole);
        }
    }







    protected function updateCompanyConfiguration(): void
    {
        $this->company->update([
            'employee_count' => $this->employee_count,
            'industry' => $this->industry,
            'active_modules' => json_encode($this->active_modules),
        ]);
    }

    protected function linkDatabaseToTenant(int $slotId, string $tenantId): void
    {
        DB::table('available_databases')
            ->where('id', $slotId)
            ->update([
                'tenant_id' => $tenantId,
                'status' => 'assigned',
            ]);
    }

    protected function ensureDatabaseExists(string $databaseName): void
    {
        $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
        
        if (empty($result)) {
            DB::statement("CREATE DATABASE `{$databaseName}`");
            \Log::info("Created database: {$databaseName}");
        }
    }

    protected function createTenantWithDatabase(string $databaseName): Tenant
    {
        $tenant = Tenant::create([
            'id' => $this->company->subdomain,
            'data' => [
                'database_name' => $databaseName,
                'company_id' => $this->company->id,
                'employee_count' => $this->employee_count,
                'industry' => $this->industry,
            ]
        ]);

        $tenant->domains()->create([
            'domain' => $this->getFullDomain()
        ]);

        return $tenant;
    }

    protected function generateDatabaseName(): string
    {
        $dbSlot = DB::table('available_databases')
            ->where('status', 'available')
            ->first();
            
        if ($dbSlot) {
            return $dbSlot->name;
        }

        return 'tenant_' . $this->company->subdomain . '_db';
    }

    protected function reserveDatabaseSlot(): object
    {
        $dbSlot = DB::table('available_databases')
            ->where('status', 'available')
            ->lockForUpdate()
            ->first();

        if (!$dbSlot) {
            throw new Exception('No available databases');
        }

        DB::table('available_databases')
            ->where('id', $dbSlot->id)
            ->update([
                'status' => 'reserved',
            ]);

        return DB::table('available_databases')
            ->where('id', $dbSlot->id)
            ->first();
    }

    protected function runTenantMigrations(Tenant $tenant): void
    {
        $databaseName = $tenant->getDatabaseName();
        \Log::info('Running migrations on database:', ['database' => $databaseName]);

        $tempConnectionName = 'tenant_migration_' . $tenant->id;
        
        config(["database.connections.{$tempConnectionName}" => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);
        
        $paths = $this->getMigrationPaths();
        
        if (!empty($paths)) {
            Artisan::call('migrate', [
                '--path' => $paths,
                '--database' => $tempConnectionName,
                '--force' => true,
                '--realpath' => true,
            ]);
        }
        
        \Log::info('Migrations completed for tenant:', ['tenant' => $tenant->id]);
        
        DB::purge($tempConnectionName);
        config(["database.connections.{$tempConnectionName}" => null]);
    }

    protected function getMigrationPaths(): array
    {
        $paths = [];

        $baseTenantPath = database_path('migrations');
        if (File::exists($baseTenantPath)) {
            $paths[] = $baseTenantPath;
        }

        $active_modules = array_merge($this->dependency_modules, $this->active_modules);
        foreach ($active_modules as $module) {
            $moduleMigrationPath = app_path("Modules/" . ucfirst($module) . "/Database/Migrations");
            if (File::exists($moduleMigrationPath)) {
                $paths[] = $moduleMigrationPath;
            }
        }

        return $paths;
    }

    protected function activateCompany(): void
    {
        $this->company->update(['status' => 'Active']);
    }

    protected function getFullDomain(): string
    {
        $appUrl = config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        return "{$this->company->subdomain}.{$host}";
    }

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $viewPath = "qf::components.livewire.$UIFramework";
        $theView = "$viewPath.auth.quick-configuration";

        return view($theView)->layout("$viewPath.layouts.app");
    }
}