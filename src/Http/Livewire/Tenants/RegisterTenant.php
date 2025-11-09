<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Tenants;

use App\Modules\System\Models\Company;
use App\Models\User as CentralUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use App\Modules\System\Models\Tenant as TenantModel;

class RegisterTenant extends Component
{
    public $company_name;
    public $email;
    public $password;
    public $subdomain;

    protected $reservedSubdomains = ['www', 'api', 'admin', 'mail', 'localhost', 'test', 'billing', 'support', 'demo'];

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'subdomain' => 'required|string|alpha_dash|min:3|max:32|unique:companies,subdomain',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function register()
    {
        $this->validate();
        $this->validateSubdomainFormat();

        try {
            // === STEP 1: Create Central Company ===
            $company = Company::create([
                'name' => $this->company_name,
                'subdomain' => $this->subdomain,
                'status' => 'pending',
                'provisioning_status' => 'in_progress',
                'billing_email' => $this->email,
                'billing_address_line_1' => 'Not provided during signup',
                'billing_city' => 'N/A',
                'billing_postal_code' => 'N/A',
                'billing_country_code' => 'US',
                'timezone' => 'UTC',
                'currency_code' => 'USD',
            ]);

            // === STEP 2: Provision Database ===
            $databaseName = 'saas_' . $this->subdomain;
            $this->provisionDatabase($databaseName);

            // === STEP 3: Configure Tenant Connection ===
            $tenantConnectionName = $this->getTenantConnectionName($databaseName);

            // === STEP 4: Create Tenant Record ===
            $tenant = TenantModel::create([
                'id' => $this->subdomain,
            ]);

            $tenant->domains()->create([
                'domain' => "{$this->subdomain}." . config('app.domain')
            ]);

            // === STEP 5: Create Central User ===
            $centralUser = CentralUser::create([
                'name' => $this->company_name . ' Admin',
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'company_id' => $company->id,
            ]);

            // === STEP 6: Run Tenant Migrations ===
            $this->runTenantMigrations($tenantConnectionName);

            // === STEP 7: Create Tenant User ===
            $tenantUser = \App\Modules\Access\Models\User::on($tenantConnectionName)->create([
                'name' => $this->company_name . ' Admin',
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'company_id' => $company->id,
                'is_active' => true,
            ]);

            // === STEP 8: Assign CEO Role ===
            $ceoRole = \App\Modules\Access\Models\Role::on($tenantConnectionName)
                ->firstOrCreate(
                    ['name' => 'ceo', 'guard_name' => 'web'],
                    ['company_id' => $company->id]
                );
            $tenantUser->assignRole($ceoRole);

            // === STEP 9: Finalize Company ===
            $company->update([
                'database_name' => $databaseName,
                'provisioning_status' => 'success',
            ]);

            // === STEP 10: Redirect ===
            $redirectUrl = "https://{$this->subdomain}." . config('app.domain') . '/onboarding';
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            \Log::error('Tenant registration failed: ' . $e->getMessage(), [
                'subdomain' => $this->subdomain,
                'email' => $this->email,
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($company)) {
                $company->update(['provisioning_status' => 'failed']);
            }

            // Clean up: Drop the database if created
            if (isset($databaseName)) {
                $this->cleanupDatabase($databaseName);
            }

            session()->flash('error', 'Workspace creation failed: ' . $e->getMessage());
            return;
        }
    }

public function runTenantMigrations($tenantConnectionName)
{
    \Log::info("Running all main migrations on tenant database: {$tenantConnectionName}");

    // Run all migrations from the main migrations directory
    $exitCode = \Artisan::call('migrate', [
        '--database' => $tenantConnectionName,
        '--path' => 'database/migrations',
        '--force' => true,
    ]);

    $output = \Artisan::output();
    \Log::info("Main migrations output: " . $output);

    if ($exitCode !== 0) {
        throw new \Exception("Main migrations failed: " . $output);
    }

    // Run module migrations if they exist
    $modulePaths = [
        app_path('Modules/Access/Database/Migrations'),
        app_path('Modules/System/Database/Migrations'),
        app_path('Modules/Hr/Database/Migrations'),
    ];

    foreach ($modulePaths as $path) {
        if (file_exists($path) && count(glob($path . '/*.php')) > 0) {
            \Log::info("Running module migrations: {$path}");
            
            $exitCode = \Artisan::call('migrate', [
                '--database' => $tenantConnectionName,
                '--path' => $path,
                '--force' => true,
            ]);

            $output = \Artisan::output();
            \Log::info("Module migrations output for {$path}: " . $output);

            if ($exitCode !== 0) {
                throw new \Exception("Module migrations failed for {$path}: " . $output);
            }
        } else {
            \Log::warning("No migration files found in: {$path}");
        }
    }
}

    protected function runMigrationPath($connection, $path)
    {
        if (!file_exists($path)) {
            \Log::warning("Migration path does not exist: {$path}");
            return;
        }

        \Log::info("Running migrations for path: {$path}");

        // Test database connection first
        try {
            \DB::connection($connection)->getPdo();
            \Log::info("Database connection successful: " . \DB::connection($connection)->getDatabaseName());
        } catch (\Exception $e) {
            \Log::error("Database connection failed: " . $e->getMessage());
            throw $e;
        }

        $exitCode = \Artisan::call('migrate', [
            '--database' => $connection,
            '--path' => $path,
            '--force' => true,
        ]);

        $output = \Artisan::output();
        \Log::info("Migration output for {$path}: " . $output);

        if ($exitCode !== 0) {
            throw new \Exception("Migration failed for path: {$path}. Output: " . $output);
        }

        \Log::info("Migrations completed successfully for: {$path}");
    }

    protected function validateSubdomainFormat()
    {
        if (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $this->subdomain)) {
            throw ValidationException::withMessages([
                'subdomain' => 'Must start/end with letter/number. Only letters, numbers, hyphens allowed.'
            ]);
        }

        if (in_array(strtolower($this->subdomain), $this->reservedSubdomains)) {
            throw ValidationException::withMessages([
                'subdomain' => 'This subdomain is reserved.'
            ]);
        }
    }

    protected function provisionDatabase(string $dbName)
    {
        if (app()->environment('production')) {
            $response = Http::withBasicAuth(
                env('CPANEL_USERNAME'),
                env('CPANEL_API_TOKEN')
            )->timeout(30)->get("https://" . env('CPANEL_HOST') . ":2083/execute/Mysql/create_database", [
                        'name' => $dbName,
                    ]);

            if (!$response->successful()) {
                throw new \Exception('cPanel API error: ' . $response->body());
            }

            $data = $response->json();
            if (isset($data['errors']) && !empty($data['errors'])) {
                throw new \Exception('cPanel error: ' . implode(', ', $data['errors']));
            }

            if (!isset($data['data']['result']) || $data['data']['result'] !== 1) {
                throw new \Exception('Failed to create database via cPanel.');
            }
        } else {
            // === LOCAL: Create DB manually ===
            \DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");
        }
    }

    protected function cleanupDatabase(string $dbName)
    {
        try {
            if (app()->environment('production')) {
                // cPanel API to drop database
                $response = Http::withBasicAuth(
                    env('CPANEL_USERNAME'),
                    env('CPANEL_API_TOKEN')
                )->timeout(30)->get("https://" . env('CPANEL_HOST') . ":2083/execute/Mysql/delete_database", [
                            'name' => $dbName,
                        ]);
            } else {
                \DB::statement("DROP DATABASE IF EXISTS `$dbName`");
            }
        } catch (\Exception $e) {
            \Log::error('Failed to cleanup database: ' . $e->getMessage());
        }
    }

    protected function getTenantConnectionName(string $databaseName): string
    {
        $connectionName = 'tenant_' . $this->subdomain;

        config([
            "database.connections.{$connectionName}" => [
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
            ]
        ]);

        return $connectionName;
    }

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $viewPath = "qf::components.livewire.$UIFramework";
        return view("$viewPath.tenants.register-tenant");
    }
}