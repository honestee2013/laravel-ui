<?php

namespace QuickerFaster\LaravelUI\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Modules\System\Models\Tenant;

class ManualTenantBootstrap
{
    public function handle($request, Closure $next)
    {
        // Get tenant from domain
        $domain = $request->getHost();
        \Log::info('ManualTenantBootstrap: Looking for tenant for domain', ['domain' => $domain]);

        $tenant = Tenant::whereHas('domains', function ($query) use ($domain) {
            $query->where('domain', $domain);
        })->first();

        if (!$tenant) {
            \Log::error('ManualTenantBootstrap: Tenant not found for domain', ['domain' => $domain]);
            abort(404, 'Tenant not found');
        }

        \Log::info('ManualTenantBootstrap: Found tenant', [
            'tenant_id' => $tenant->id,
            'database_name' => $tenant->getDatabaseName()
        ]);

        // Manually set the tenant in the container
        app()->instance(\Stancl\Tenancy\Contracts\Tenant::class, $tenant);

        // Manually configure the database connection
        $databaseName = $tenant->getDatabaseName();
        $connectionConfig = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        // Set the configuration
        Config::set('database.connections.tenant', $connectionConfig);

        // Purge and reconnect
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Set as default connection
        Config::set('database.default', 'tenant');

        \Log::info('ManualTenantBootstrap: Database configured', [
            'connection' => 'tenant',
            'database' => $databaseName,
            'config' => $connectionConfig
        ]);

        // Verify connection works
        try {
            $connectedDb = DB::connection('tenant')->getDatabaseName();
            \Log::info('ManualTenantBootstrap: Successfully connected to database', ['database' => $connectedDb]);
        } catch (\Exception $e) {
            \Log::error('ManualTenantBootstrap: Failed to connect to tenant database', [
                'error' => $e->getMessage(),
                'database' => $databaseName
            ]);
            abort(500, 'Database connection failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}