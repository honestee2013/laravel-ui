<?php

namespace QuickerFaster\LaravelUI\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class QuickerFasterInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quickerfaster:install {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick installation setup for Laravel project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting QuickerFaster Installation...');

        $force = $this->option('force');

        // 1. Publish vendor files
        $this->publishVendorFiles($force);

        // 2. Run migrations
        $this->runMigrations();

        // 3. Run seeders
        $this->runSeeders();

        // 4. Create symbolic link
        $this->createStorageLink();

        // 5. Clear and cache
        $this->optimizeApplication();

        // 6. Generate application key if not exists
        $this->generateAppKey();

        $this->info('✅ QuickerFaster installation completed successfully!');
    }

    /**
     * Publish vendor files
     */
    protected function publishVendorFiles($force)
    {
        $this->info('📁 Publishing vendor files...');



        // Quicker faster publish
        Artisan::call('vendor:publish --tag=qf-modules');
        // Stancl/tenancy publish
        Artisan::call('tenancy:install');
        // Override tenancy config
        $this->overrideTheDependencyFiles();


        $providers = [
            'Spatie\Activitylog\ActivitylogServiceProvider' => [
                '--tag' => ['activitylog-migrations', 'activitylog-config']
            ],
            // Add more providers as needed
        ];

        foreach ($providers as $provider => $options) {
            try {
                $command = array_merge([
                    '--provider' => $provider
                ], $options);
                
                if ($force) {
                    $command['--force'] = true;
                }

                Artisan::call('vendor:publish', $command);
                $this->info("Published: {$provider} with tags: " . implode(', ', (array)$options['--tag']));
            } catch (\Exception $e) {
                $this->warn("Failed to publish {$provider}: " . $e->getMessage());
            }
        }

        $this->info('✅ Vendor files published successfully!');
    }

    /**
     * Run migrations
     */
    protected function runMigrations()
    {
        $this->info('🗃️ Running migrations...');

        try {
            Artisan::call('migrate');
            $this->info(Artisan::output());
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Run seeders
     */
    protected function runSeeders()
    {
        if ($this->confirm('Do you want to run database seeders?', true)) {
            $this->info('🌱 Running database seeders...');

            $seeders = [
                'Database\\Seeders\\DatabaseSeeder',
                // Add your specific seeders here
                // 'Database\\Seeders\\UserSeeder',
                // 'Database\\Seeders\\RoleSeeder',
            ];

            foreach ($seeders as $seeder) {
                try {
                    Artisan::call('db:seed', ['--class' => $seeder]);
                    $this->info("Seeded: {$seeder}");
                } catch (\Exception $e) {
                    $this->warn("Seeder {$seeder} failed: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Create storage link
     */
    protected function createStorageLink()
    {
        $this->info('🔗 Creating storage link...');

        if (file_exists(public_path('storage'))) {
            $this->info('Storage link already exists.');
            return;
        }

        try {
            Artisan::call('storage:link');
            $this->info('✅ Storage link created successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to create storage link: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application
     */
    protected function optimizeApplication()
    {
        $this->info('⚡ Optimizing application...');

        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        foreach ($commands as $command) {
            try {
                Artisan::call($command);
                $this->info("Executed: {$command}");
            } catch (\Exception $e) {
                $this->warn("Command {$command} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Generate application key
     */
    protected function generateAppKey()
    {
        if (empty(config('app.key'))) {
            $this->info('🔑 Generating application key...');
            Artisan::call('key:generate');
            $this->info('✅ Application key generated!');
        }
    }




    protected function overrideTheDependencyFiles()
    {
        $this->overrideTenancyPackageConfigFiles();
        $this->overrideTenancyPackageRouteFiles();
        $this->overrideDatabaseMigrationFiles();
        $this->overrideDatabaseSeederFiles();
        $this->overrideModelFiles();
        $this->overrideAssetFiles();
        $this->overridMiddlewareFiles();

        $this->copyCpanelDeploymentFile();
    }



    private function overrideTenancyPackageConfigFiles()
    {

        $source = __DIR__ . '/../../dependencies/tenancy/config';
        $destination = base_path('config');

        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ config copied successfully");
            return 0;
        } else {
            $this->error("❌ config Copy failed");
            return 1;
        }
    }

    private function overrideDatabaseSeederFiles()
    {
        $source = __DIR__ . '/../../dependencies/database/seeders';
        $destination = database_path('seeders');

        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Seeder copied successfully");
            return 0;
        } else {
            $this->error("❌ Seeder Copy failed");
            return 1;
        }
    }



    private function overrideDatabaseMigrationFiles()
    {
        $source = __DIR__ . '/../../dependencies/database/migrations';
        $destination = database_path('migrations');

        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Migrations copied successfully");
            return 0;
        } else {
            $this->error("❌ Migrations Copy failed");
            return 1;
        }
    }



    private function overrideTenancyPackageRouteFiles()
    {
        $source = __DIR__ . '/../../dependencies/tenancy/routes';
        $destination = base_path('routes');

        // Then copy
        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Route copied successfully");
            return 0;
        } else {
            $this->error("❌ Route Copy failed");
            return 1;
        }
    }


    private function overrideModelFiles()
    {
        $source = __DIR__ . '/../../dependencies/Models';
        $destination = app_path('Models');

        // Then copy
        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Route copied successfully");
            return 0;
        } else {
            $this->error("❌ Route Copy failed");
            return 1;
        }
    }


    private function overrideAssetFiles()
    {
        $source = __DIR__ . '/../../dependencies/tenancy/tenancy';
        $destination = public_path('tenancy');

        // Then copy
        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Assets copied successfully");
            return 0;
        } else {
            $this->error("❌ Assets Copy failed");
            return 1;
        }
    }



    private function overridMiddlewareFiles()
    {
        $source = __DIR__ . '/../../dependencies/Middleware';
        $destination = app_path("Http/Middleware");// Then copy');

        // Then copy
        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Middleware copied successfully");
            return 0;
        } else {
            $this->error("❌ Middleware Copy failed");
            return 1;
        }
    }



    private function copyCpanelDeploymentFile()
    {
        $source = __DIR__ . '/../../dependencies/deployment';
        $destination = base_path("/");

        // Then copy
        if ($this->copyDirectory($source, $destination)) {
            $this->info("✅ Cpanel Deployment File copied successfully");
            return 0;
        } else {
            $this->error("❌ Cpanel Deployment File Copy failed");
            return 1;
        }
    }





    public function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                // Recursively copy subdirectories
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                // Copy files (overwrites by default)
                copy($sourcePath, $destPath);
            }
        }

        closedir($dir);
        return true;
    }






}