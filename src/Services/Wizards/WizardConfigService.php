<?php

namespace QuickerFaster\LaravelUI\Services\Wizards;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;

class WizardConfigService
{
    public function loadWizardConfig(string $wizardId, string $module)
    {
        $configPath = app_path("Modules/" . ucfirst($module) . "/Data/wizards/{$wizardId}.php");
        
        if (!File::exists($configPath)) {
            throw new \Exception("Wizard config not found: {$configPath}");
        }
        $conf =  include $configPath;
        return $conf;
    }

    public function loadModelConfig(string $fqcn)
    {
        // Extract module and model name from FQCN
        $parts = explode('\\', $fqcn);
        $moduleName = $parts[2] ?? 'system'; // App\Modules\Hr\Models\Employee
        $modelName = end($parts);
        
        $configPath = app_path("Modules/{$moduleName}/Data/" . Str::snake($modelName) . '.php');
        
        if (!File::exists($configPath)) {
            return [];
        }
        
        return include $configPath;
    }


    /*public function loadWizardConfig(string $fqcn)
    {
        return DataTableConfig::getConfigFileFields($fqcn);
    }*/

}