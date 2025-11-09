<?php
// app/Tenancy/CustomDatabaseConfig.php

namespace QuickerFaster\LaravelUI\Tenancy;

use Stancl\Tenancy\DatabaseConfig;
use Stancl\Tenancy\Contracts\Tenant;

class CustomDatabaseConfig extends DatabaseConfig
{
    protected $config;

    public function __construct(Tenant $tenant)
    {

// In your FixedDatabaseConfig constructor, add more logging
\Log::info('FixedDatabaseConfig received config from tenant:', [
    'original_config' => $tenant->getDatabaseConfig(),
    'tenant_id' => $tenant->id
]);



        // Get the tenant's database configuration
        $this->config = $tenant->getDatabaseConfig();
        
        // Ensure driver is always set
        if (empty($this->config['driver'])) {
            $this->config['driver'] = 'mysql';
        }
        
        // Manually set the configuration in the global config
        config(['database.connections.tenant' => $this->config]);
        
        \Log::info('CustomDatabaseConfig: Ensuring driver is set', [
            'driver' => $this->config['driver'],
            'database' => $this->config['database'] ?? 'null'
        ]);

        parent::__construct($tenant);



        
    }

    /**
     * Override to use our fixed configuration
     */
    public function config(): array
    {
        return $this->config;
    }
}