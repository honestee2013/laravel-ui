<?php
namespace QuickerFaster\LaravelUI\Http\Livewire\Auth;

use App\Modules\System\Models\Tenant;
use App\Modules\System\Models\Company;
use App\Models\User;
use App\Modules\Admin\Models\Role;
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
    public $dependency_modules = ['system','Admin'];
    
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
            return redirect()->route('central.client.register');
        }
        
        $this->company = Company::findOrFail($companyId);
        
        if (!$this->company || !$this->company->domain_verified) {
            return redirect()->route('central.client.register');
        }
    }


public function submit()
{
    $databaseSlot = null;
    $tenant = null;

    try {
        // Update company configuration
        $this->updateCompanyConfiguration();
        
        // Create tenant infrastructure
        $databaseSlot = $this->reserveDatabaseSlot();
        $databaseName = $databaseSlot->name;
        
        //$this->ensureDatabaseExists($databaseName);
        $tenant = $this->createTenantWithDatabase($databaseName);

// 👈 VERIFY TENANT RESOLUTION BEFORE PROCEEDING
        $this->verifyTenantResolution($tenant);


        $this->linkDatabaseToTenant($databaseSlot->id, $tenant->id);
        
        // Initialize tenant and run migrations
        $this->initializeTenant($tenant);
        
        // Create user and roles in tenant context
        $user = $this->createTenantAdminUser($tenant);




// Instead of generating token, login directly and redirect
Auth::login($user);

$this->activateCompany();
session()->forget('verified_company_id');

// Redirect directly to tenant dashboard
return redirect("http://{$this->getFullDomain()}/hr/dashboard");


        
        /*$this->activateCompany();
        session()->forget('verified_company_id');

        // Generate login token and redirect
        $loginToken = $this->generateLoginToken();
        return redirect("http://{$this->getFullDomain()}/auto-login?token=$loginToken");*/

    } catch (Exception $e) {
        \Log::error('Tenant creation failed: ' . $e->getMessage());
        
        // Manual rollback
        $this->manualRollback($databaseSlot, $tenant);
        
        session()->flash('error', 'Tenant creation failed: ' . $e->getMessage());
        return back();
    }
}

protected function manualRollback($databaseSlot, $tenant)
{
    try {
        // Release database slot
        if ($databaseSlot) {
            $this->releaseDatabaseSlot();
        }
        
        // Delete tenant if created
        if ($tenant) {
            // You might want to delete the tenant and its database
            // Be careful with this as it's destructive
        }
        
        // Reset company status
        $this->company->update(['status' => 'Pending']);
        
    } catch (Exception $e) {
        \Log::error('Rollback failed: ' . $e->getMessage());
    }
}

    protected function generateLoginToken()
    {
        $token = sha1(random_bytes(32));
        
        // Use database instead of cache
        DB::connection('mysql')->table('login_tokens')->insert([
            'token' => $token,
            'user_email' => $this->company->billing_email,
            'tenant_id' => $this->company->subdomain,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);
        
        \Log::info('Generated login token', [
            'token' => $token,
            'tenant' => $this->company->subdomain,
            'email' => $this->company->billing_email
        ]);
        
        return $token;
    }

    protected function createTenantAdminUser(Tenant $tenant)
    {
        // We're in TENANT context, get password from session or use fallback
        //$password = session('user_password') ?? Hash::make(Str::random(16));
        
    // Get password hash directly from central user
    $passwordHash = $this->getCentralUserPasswordHash($this->company->billing_email);
    
    if (!$passwordHash) {
        \Log::warning('No central user found, using fallback password');
        $passwordHash = Hash::make(Str::random(16));
    }

    // Create user in tenant database with SAME password hash
    $user = \App\Models\User::create([
        'name' => 'Admin',
        'email' => $this->company->billing_email,
        'password' => $passwordHash, // Same hash as central user
        'company_id' => $this->company->id,
    ]);

        // Assign admin role
        $adminRole = \App\Modules\Admin\Models\Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $adminRole = \App\Modules\Admin\Models\Role::create([
                'name' => 'admin',
                'description' => 'Manage users, settings, and system operations',
                'guard_name' => 'web',
                'editable' => 'No',
            ]);
        }
        
        $user->assignRole($adminRole);

        \Log::info('Created tenant admin user', [
            'tenant' => $tenant->id,
            'user' => $user->email,
            'database' => DB::connection()->getDatabaseName()
        ]);

        return $user;
    }


protected function getCentralUserPasswordHash($email)
{
    $currentConnection = DB::getDefaultConnection();
    
    try {
        // Switch to central database
        config(['database.default' => 'mysql']);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        // Get the actual user from central database
        $centralUser = \App\Models\User::where('email', $email)->first();
        
        \Log::info('Central user lookup', [
            'email' => $email,
            'user_found' => !is_null($centralUser),
            'has_password' => $centralUser ? !is_null($centralUser->password) : false
        ]);
        
        return $centralUser ? $centralUser->password : null;
        
    } finally {
        // Switch back
        config(['database.default' => $currentConnection]);
        DB::purge($currentConnection);
        DB::reconnect($currentConnection);
    }
}



protected function reserveDatabaseSlot(): object
{
    return DB::connection('mysql')->transaction(function () {
        $dbSlot = DB::connection('mysql')
            ->table('available_databases')
            ->where('status', 'available')
            ->lockForUpdate()
            ->first();

        if (!$dbSlot) {
            throw new Exception('No available databases in the pool');
        }

        \Log::info('Reserving database slot', [
            'slot_id' => $dbSlot->id,
            'database_name' => $dbSlot->name,
            'current_status' => $dbSlot->status
        ]);

        // Reserve without setting tenant_id first
        DB::connection('mysql')
            ->table('available_databases')
            ->where('id', $dbSlot->id)
            ->update([
                'status' => 'reserved',
            ]);

        $reservedSlot = DB::connection('mysql')
            ->table('available_databases')
            ->where('id', $dbSlot->id)
            ->first();

        \Log::info('Database slot reserved', [
            'slot_id' => $reservedSlot->id,
            'database_name' => $reservedSlot->name,
            'new_status' => $reservedSlot->status
        ]);

        return $reservedSlot;
    });
}

    protected function linkDatabaseToTenant(int $slotId, string $tenantId): void
    {
        // Now we can safely set the tenant_id since the tenant exists
        DB::connection('mysql')
            ->table('available_databases')
            ->where('id', $slotId)
            ->update([
                'tenant_id' => $tenantId,
                'status' => 'assigned',
            ]);
    }

protected function createTenantWithDatabase(string $databaseName): Tenant
{
    \Log::info('Creating tenant with database', [
        'subdomain' => $this->company->subdomain,
        'database' => $databaseName
    ]);

    // Use a transaction to bypass the database existence check
    return DB::connection('mysql')->transaction(function () use ($databaseName) {
        // Create tenant without triggering database operations
        $tenant = new Tenant([
            'id' => $this->company->subdomain,
            'tenancy_db_name' => $databaseName,
        ]);

        // Save without events that might trigger database creation
        $tenant->saveQuietly();

        // Manually create the domain
        $tenant->domains()->create([
            'domain' => $this->getFullDomain()
        ]);

        \Log::info('Tenant created quietly', [
            'tenant_id' => $tenant->id,
            'database' => $databaseName
        ]);

        return $tenant;
    });
}

    protected function releaseDatabaseSlot(): void
    {
        try {
            DB::connection('mysql')
                ->table('available_databases')
                ->where('status', 'reserved')
                ->where(function($query) {
                    $query->where('tenant_id', $this->company->subdomain)
                          ->orWhereNull('tenant_id');
                })
                ->update([
                    'tenant_id' => null,
                    'status' => 'available',
                ]);
        } catch (Exception $e) {
            \Log::error('Failed to release database slot: ' . $e->getMessage());
        }
    }


protected function verifyTenantResolution(Tenant $tenant)
{
    $domain = $this->getFullDomain();
    
    \Log::info('Verifying tenant resolution', [
        'domain' => $domain,
        'tenant_id' => $tenant->id
    ]);

    // Manual domain lookup
    $domainRecord = \Stancl\Tenancy\Database\Models\Domain::where('domain', $domain)->first();
    
    if (!$domainRecord) {
        throw new Exception("Domain record not found for: " . $domain);
    }

    if ($domainRecord->tenant_id !== $tenant->id) {
        throw new Exception("Domain tenant mismatch. Expected: {$tenant->id}, Found: {$domainRecord->tenant_id}");
    }

    // Manual tenant lookup
    $tenantRecord = Tenant::find($tenant->id);
    if (!$tenantRecord) {
        throw new Exception("Tenant record not found: " . $tenant->id);
    }

    \Log::info('Tenant resolution verified', [
        'domain' => $domainRecord->domain,
        'tenant_id' => $tenantRecord->id,
        'database_name' => $tenantRecord->getDatabaseName()
    ]);
}



protected function initializeTenant(Tenant $tenant)
{
    try {
        // Verify domain exists before initializing
        $domainCheck = \Stancl\Tenancy\Database\Models\Domain::where('tenant_id', $tenant->id)->first();
        
        if (!$domainCheck) {
            throw new Exception("Domain not found for tenant: " . $tenant->id);
        }

        \Log::info('Domain verified', [
            'tenant_id' => $tenant->id,
            'domain' => $domainCheck->domain
        ]);

        // Get the expected database name
        $expectedDatabase = $tenant->getDatabaseName();
        \Log::info('Expected database for tenant', [
            'tenant_id' => $tenant->id,
            'expected_database' => $expectedDatabase
        ]);

        // Initialize the tenant
        tenancy()->initialize($tenant);

        $actualDatabase = DB::connection()->getDatabaseName();
        \Log::info('Tenant initialized', [
            'tenant_id' => tenant('id'),
            'expected_database' => $expectedDatabase,
            'actual_database' => $actualDatabase,
            'match' => $expectedDatabase === $actualDatabase
        ]);

        if ($expectedDatabase !== $actualDatabase) {
            throw new Exception("Database mismatch! Expected: {$expectedDatabase}, Actual: {$actualDatabase}");
        }

        // 🔥 NEW: Clean the database before running migrations
        $this->purgeTenantDatabase();

        // Run migrations for the tenant
        $this->runTenantMigrations($tenant);
        
        // Seed tenant data
        $this->seedTenantData();
        
    } catch (Exception $e) {
        \Log::error('Tenant initialization failed: ' . $e->getMessage());
        throw $e;
    }
}


protected function purgeTenantDatabase(): void
{
    \Log::info('Purging existing tables from tenant database');

    $tables = DB::select('SHOW TABLES');
    $connection = config('database.default');
    $schema = collect($tables)->map(function ($table) {
        return (array) $table;
    })->flatten()->values();

    if ($schema->isEmpty()) {
        \Log::info('No tables found in tenant database');
        return;
    }

    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($schema as $table) {
        DB::statement("DROP TABLE IF EXISTS `{$table}`");
    }

    DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    \Log::info('Tenant database purged successfully', ['tables_dropped' => $schema->count()]);
}



    


    protected function runTenantMigrations(Tenant $tenant): void
    {
        $databaseName = $tenant->getDatabaseName();
        \Log::info('Running migrations on database:', ['database' => $databaseName]);

        // Use tenancy's migrate command
        $paths = $this->getMigrationPaths();
        
        foreach ($paths as $path) {
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--path' => $path,
                '--force' => true,
            ]);
        }

        \Log::info('Migrations completed for tenant:', ['tenant' => $tenant->id]);
    }

    protected function seedTenantData()
    {
        try {
            // Run seeders in tenant context
            Artisan::call('tenants:seed', [
                '--tenants' => [tenant('id')],
                '--class' => 'DatabaseSeeder', // Use your main seeder
                '--force' => true,
            ]);
            
            // Run specific module seeders if needed
            Artisan::call('tenants:seed', [
                '--tenants' => [tenant('id')],
                '--class' => 'App\Modules\Admin\Database\Seeders\RoleSeeder',
                '--force' => true,
            ]);
            
            \Log::info('Tenant data seeded successfully');
        } catch (Exception $e) {
            \Log::error('Seeding failed: ' . $e->getMessage());
            // Continue without throwing to allow user creation
        }
    }


protected function getCentralUser()
{
    // Store current connection
    $currentConnection = DB::getDefaultConnection();
    
    try {
        // Switch to central database temporarily
        config(['database.default' => 'mysql']);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        // Get user from central database
        $centralUser = \App\Models\User::where('email', $this->company->billing_email)
                                       ->where('company_id', $this->company->id)
                                       ->first();
        
        return $centralUser;
        
    } finally {
        // Always switch back to tenant connection
        config(['database.default' => $currentConnection]);
        DB::purge($currentConnection);
        DB::reconnect($currentConnection);
    }
}

    protected function ensureDatabaseExists(string $databaseName): void
    {
        $result = DB::connection('mysql')->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", 
            [$databaseName]
        );
        
        if (empty($result)) {
            DB::connection('mysql')->statement("CREATE DATABASE `{$databaseName}`");
            \Log::info("Created database: {$databaseName}");
        } else {
            \Log::info("Database already exists: {$databaseName}");
        }
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