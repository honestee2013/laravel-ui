<?php 
namespace QuickerFaster\LaravelUI\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class ReplenishTenantDatabases extends Command
{
    protected $signature = 'tenancy:replenish-pool {--count=5}';
    protected $description = 'Replenish pre-provisioned tenant database pool';

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
        $this->info("Creating {$toCreate} new tenant databases...");

        for ($i = 0; $i < $toCreate; $i++) {
            $dbSuffix = now()->format('ymdHis') . Str::random(4);
            $dbName = '_qf_suite_tenant_' . $dbSuffix;

            try {
                // 1. Create DB via raw SQL (if cPanel allows)
                DB::connection('mysql')->statement("CREATE DATABASE `{$dbName}`");

                // 2. Temporarily configure tenancy to use this DB
                config(['tenancy.database.prefix' => '']);
                config(['tenancy.database.suffix' => '']);
                config(['database.connections.tenant_pool.database' => $dbName]);

                // 3. Run migrations
                $exitCode = Artisan::call('migrate', [
                    '--database' => 'tenant_pool',
                    '--force' => true,
                    '--path' => 'database/migrations/tenant',
                ]);

                if ($exitCode !== 0) {
                    throw new \Exception("Migration failed for {$dbName}");
                }

                // 4. Add to pool
                DB::table('available_databases')->insert([
                    'name' => $dbName,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("✅ Created and migrated: {$dbName}");

            } catch (\Exception $e) {
                $this->error("❌ Failed to create {$dbName}: " . $e->getMessage());
                // Optionally alert admin
                \Log::error("DB replenish failed: " . $e->getMessage());
            }
        }
    }
}