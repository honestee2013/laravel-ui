<?php

namespace QuickerFaster\LaravelUI\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateSuperUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qf:create-super-user-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans modules and models to generate permissions for the super_user role.';

    /**
     * Permission actions to be created for each model.
     *
     * @var array
     */
    protected $permissionActions = ['view', 'create', 'edit', 'delete', 'print', 'export', 'import'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting permission generation for super_user role...');

        // Find or create the super_user role
        $superUserRole = Role::firstOrCreate(['name' => 'super_user']);

        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            $this->error('The app/Modules directory does not exist.');
            return Command::FAILURE;
        }

        $modules = File::directories($modulesPath);

        $permissionsToAssign = [];

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $modelsPath = $modulePath . '/Models';

            if (File::exists($modelsPath)) {
                $modelFiles = File::files($modelsPath);

                foreach ($modelFiles as $modelFile) {
                    $modelName = basename($modelFile, '.php');
                    $snakeCaseModelName = Str::snake($modelName);

                    foreach ($this->permissionActions as $action) {
                        $permissionName = "{$action}_{$snakeCaseModelName}";
                        $permissionsToAssign[] = $permissionName;
                        $this->line("Creating permission: <info>{$permissionName}</info>");

                        // Create the permission if it doesn't exist
                        Permission::firstOrCreate(['name' => $permissionName]);
                    }
                }
            }
        }

        // Sync all generated permissions to the super_user role
        if (!empty($permissionsToAssign)) {
            $superUserRole->syncPermissions($permissionsToAssign);
            $this->info("\nAll generated permissions have been assigned to the super_user role.");
        } else {
            $this->warn("\nNo permissions were generated. Please check your module and model structure.");
        }

        $this->info("\nCommand finished successfully.");

        return Command::SUCCESS;
    }
}
