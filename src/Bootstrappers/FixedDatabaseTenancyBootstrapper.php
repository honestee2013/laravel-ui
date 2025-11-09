<?php
namespace QuickerFaster\LaravelUI\Bootstrappers;

use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class FixedDatabaseTenancyBootstrapper extends DatabaseTenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        // Get the database name from tenant
        $databaseName = $tenant->getDatabaseName();
        
        // Manually configure the tenant connection (like you did in ModuleSelection)
        Config::set('database.connections.tenant', [
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
        ]);

        // Purge and reconnect
        DB::purge('tenant');
        DB::reconnect('tenant');

        \Log::info('FixedDatabaseTenancyBootstrapper: Configured tenant connection', [
            'tenant' => $tenant->id,
            'database' => $databaseName
        ]);

        // Call parent to handle any additional logic
        parent::bootstrap($tenant);
    }
}