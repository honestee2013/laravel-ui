<?php
// app/Http/Middleware/ManualTenantConnection.php

namespace QuickerFaster\LaravelUI\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;




class PreTenancyConfiguration
{
    public function handle($request, Closure $next)
    {

      
        // Ensure the base tenant connection has a driver BEFORE tenancy initializes
        Config::set('database.connections.tenant.driver', 'mysql');
        
        // Also ensure all other required fields are set
        $baseConfig = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => null, // Will be set by tenancy
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
        
        Config::set('database.connections.tenant', $baseConfig);

        \Log::info('PreTenancyConfiguration: Ensured tenant connection has driver');

        return $next($request);
    }
}