<?php
namespace QuickerFaster\LaravelUI\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class ReplenishTenantDatabases extends Command
{
    protected $signature = 'quickerfaster:replenish-pool {--count=5}';
    protected $description = 'Replenish pre-provisioned tenant database pool';

    // app/Console/Commands/ReplenishTenantDatabases.php

    public function handle()
    {
        $minPoolSize = (int) $this->option('count');
        $available = DB::table('available_databases')
            ->where('status', 'available')
            ->count();

        if ($available >= $minPoolSize) {
            $this->info("Pool healthy ({$available} >= {$minPoolSize})");
            return;
        }

        $toCreate = $minPoolSize - $available;
        $this->info("Preparing {$toCreate} tenant databases...");

        for ($i = 0; $i < $toCreate; $i++) {
            $dbSuffix = now()->format('ymdHis') . Str::random(4);
            $dbName = '_qf_suite_tenant_' . $dbSuffix;

            try {
                if (app()->environment('production')) {
                    // On shared hosting: Assume DB was pre-created
                    // Or call external script
                    $this->createDatabaseInProduction($dbName);
                } else {
                    // Local: create via SQL
                    DB::connection('mysql')->statement("CREATE DATABASE `{$dbName}`");
                }

                // Run migrations on the new DB
                ///$this->migrateDatabase($dbName);

                // Add to pool
                DB::table('available_databases')->insert([
                    'name' => $dbName,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("✅ Activated: {$dbName}");

            } catch (\Exception $e) {
                $this->error("❌ Failed: {$dbName} - " . $e->getMessage());
                \Log::error("DB replenish failed", ['db' => $dbName, 'error' => $e->getMessage()]);
            }
        }
    }

    protected function createDatabaseInProduction(string $dbName)
    {
        // Option A: Call external cPanel script
        $command = "php " . base_path('database/create_db_via_cpanel.php') . " {$dbName}";
        $output = [];
        $exitCode = 0;
        exec($command . " 2>&1", $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("cPanel DB creation failed: " . implode("\n", $output));
        }

        // Option B: If you pre-create DBs manually, just verify existence
        // DB::connection('mysql')->select("SHOW DATABASES LIKE '{$dbName}'");
    }

    protected function migrateDatabase(string $dbName)
    {
        // Temporarily configure a DB connection for this tenant
        config(["database.connections.tenant_temp.database" => $dbName]);
        DB::purge('tenant_temp');

        Artisan::call('migrate', [
            '--database' => 'tenant_temp',
            '--path' => 'database/tenant_migrations/hr', // Only HR for now
            '--force' => true,
        ]);
    }
}