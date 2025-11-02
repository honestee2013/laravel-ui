<?php


namespace QuickerFaster\LaravelUI\Providers;

use QuickerFaster\LaravelUI\Services\DataTables\DataTableConfigService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableOptionService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableDataSourceService;
use QuickerFaster\LaravelUI\Services\Log\UserActivityLogger;

use QuickerFaster\LaravelUI\Facades\UserActivities\UserActivityLoggerFacade;
use QuickerFaster\LaravelUI\Facades\DataTables\DataTableDataSource;
use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;
use QuickerFaster\LaravelUI\Facades\DataTables\DataTableOption;

use QuickerFaster\LaravelUI\Commands\PackageDevCommand;
use QuickerFaster\LaravelUI\Commands\CreateSuperUserPermissions;

use QuickerFaster\LaravelUI\Formatting\FieldFormattingService;




use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Event;



class QuickerFasterLaravelUIServiceProvider extends ServiceProvider
{

    public function boot()
    {
        
        
        // 1. Load routes from the package's directory (if enabled from the config file)
        if (config('qf_laravel_ui.load_routes')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        // 2. Publish config
        $this->publishes([
            __DIR__ . '/../../config/qf_laravel_ui.php' => config_path('qf_laravel_ui.php'),
        ], 'qf-config');

        // 3. Register package views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'qf');
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/qf'), //  Search (views/vendor/qf) on the host app
        ], 'qf-views');

        // 4. Translations
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'qf');
        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/qf'),
        ], 'qf-translations');

        // 5. Middleware for package routes
        \Route::middleware(['web', 'auth'])->group(__DIR__ . '/../../routes/web.php');

        // 6. Register Livewire + Blade components
        $this->registerPackageLivewireComponents();
        //$this->registerBladeComponents();
        //Livewire::component("qf", __DIR__ . '/../Components/Livewire');


        // 7. Aliases
        //$this->loadAliases();

        // 8. ✅ Vite integration
        $this->configureVite();
        $this->publishes([
            __DIR__.'/../../resources/assets' => public_path('vendor/qf'),
        ], 'qf-assets');


        $this->publishes([
            __DIR__.'/../Modules' => app_path('Modules'),
        ], 'qf-modules');




        ///$this->registerLivewireComponents($livewirePath = __DIR__ .'/../Http/Livewire');

        // Publishing Creative Tim Bootstrap Soft UI Scaffolding
        ///$this->publishScaffold();

        // Load translations
        $this->setupModules();
        $this->loadAliases();
        $this->loadRoutes();

    }



    private function loadRoutes()
    {
        /*$this->loadRoutesFrom(file_exists(base_path('routes/quicker-faster-code-gen-web.php'))
            ? base_path('routes/quicker-faster-code-gen-web.php')
            : __DIR__ . '/../scaffold/ct/soft-ui/bootstrap/v2/routes/web.php');*/

        /*$this->loadRoutesFrom(file_exists(base_path('routes/quicker-faster-code-gen-api.php'))
            ? base_path('routes/quicker-faster-code-gen-api.php')
            : __DIR__ . '/../routes/quicker-faster/code-gen/api.php');*/
    }




protected function configureVite()
{
    // ✅ Manifest lives in: public/vendor/qf/.vite/manifest.json
    Vite::useBuildDirectory('vendor/qf/.vite');

    // ✅ Hot file should be in public/vendor/qf/hot (not vendor/qf/hot)
    ///Vite::useHotFile(public_path('vendor/qf/hot'));
    Vite::useHotFile(public_path('hot'));

    // ✅ Add macro for convenience -> @viteQf()
    Vite::macro('qf', function () {
        return $this->withEntryPoints(['resources/js/app.js'])
            ->toHtml();
    });
}










    public function register()
    {
     
        // Use the snake_case filename as the configuration key
        $this->mergeConfigFrom(
            __DIR__.'/../../config/qf_laravel_ui.php', 'qf_laravel_ui'
        );

        $this->registerServiceClasses(); 

        if ($this->app->runningInConsole()) {
            $this->commands([
                PackageDevCommand::class,
                CreateSuperUserPermissions::class
            ]);
        }
    }


    private function loadAliases()
    {
        $loader = AliasLoader::getInstance();

        if (class_exists(UserActivityLoggerFacade::class)) {
            $loader->alias('UserActivityLoggerFacade', UserActivityLoggerFacade::class);
        }
        
        if (class_exists(DataTableOption::class)) {
            $loader->alias('DataTableOption', DataTableOption::class);
        }
        if (class_exists(DataTableConfig::class)) {
            $loader->alias('DataTableConfig', DataTableConfig::class);
        }
        if (class_exists(DataTableDataSource::class)) {
            $loader->alias('DataTableDataSource', DataTableDataSource::class);
        }

    }



    public function registerServiceClasses() {

        $this->app->singleton('user-activity-logger', function () {
            return new UserActivityLogger();
        });

        $this->app->singleton("DataTableConfigService", function ($app) {
            return new DataTableConfigService();
        });


        $this->app->singleton("DataTableOptionService", function ($app) {
            return new DataTableOptionService();
        });

        $this->app->singleton("DataTableDataSourceService", fn() => new DataTableDataSourceService);

        $this->app->singleton(FieldFormattingService::class, function ($app) {
            return new FieldFormattingService();
        });

    }




    /**
     * Automatically registers Livewire components from the package.
     */
protected function registerPackageLivewireComponents()
{
    $basePath = __DIR__ . '/../Http/Livewire';
    $baseNamespace = 'QuickerFaster\\LaravelUI\\Http\\Livewire';
    
    // Get the real base path without any relative references
    $basePath = realpath($basePath);
    
    // Recursively scan directory
    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
  
    foreach ($files as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') {
            continue;
        }
        
        // Get the full file path
        $filePath = $file->getRealPath();
        
        // Calculate relative path from the base path
        $relativePath = str_replace([$basePath, '.php'], '', $filePath);
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
        
        // Convert path to namespace
        $className = str_replace(
            DIRECTORY_SEPARATOR, 
            '\\', 
            $baseNamespace . '\\' . $relativePath
        );
   
        // Calculate component name (convert to kebab-case)
        $componentName =  str_replace(
            DIRECTORY_SEPARATOR, 
            '.', 
            $relativePath
        );
       
        // Convert to kebab-case for each part of the path
        $parts = explode('.', $componentName);
        $parts = array_map(function($part) {
            return \Illuminate\Support\Str::kebab($part);
        }, $parts);
        $componentName = 'qf::'.implode('.', $parts);
        
        // Register the component
        if (class_exists($className)) {
            //dd($componentName, $className);
            Livewire::component($componentName, $className);
        }
    }
}





protected function generateAlias($filePath) {
    // Get the base name of the file (e.g., "FormManager.php")
    $fileName = basename($filePath);
    
    // Remove the file extension (e.g., "FormManager")
    $className = pathinfo($fileName, PATHINFO_FILENAME);
    
    // Convert the string to snake case and replace underscores with hyphens
    // This assumes the class name is in PascalCase, a common convention.
    $alias = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '-', $className));
   
    return $alias;
}

    /**
     * Automatically registers Blade components from the package.
     */
    protected function registerBladeComponents()
    {
        // Define a namespace for your Blade components
        $namespace = 'QuickerFaster\\LaravelUI\\Components\\Blade';
        // Define the directory path to your Blade components
        $componentPath = __DIR__ . '/../Components/Blade';

        if (! is_dir($componentPath)) {
            return;
        }

        // Register components using a single method call
        Blade::componentNamespace($namespace, 'qf');

        // Note: For anonymous Blade components, you can use Blade::anonymousComponentPath() if needed.
    }



   /**
     * Extract the fully-qualified class name from a file.
     */
    protected function getClassFromFile($moduleName, $directory, $filePath)
    {
        $className = str_replace('.php', '', basename($filePath));
        return "App\\Modules\\{$moduleName}\\{$directory}\\{$className}";
    }

        /**
     * Detect the event from a listener.
     */
    protected function getEventFromListener($class)
    {
        try {
            $reflection = new \ReflectionClass($class);

            // Ensure the class has a handle method
            if ($reflection->hasMethod('handle')) {
                $method = $reflection->getMethod('handle');
                $parameters = $method->getParameters();

                // Extract the event type from the first argument
                if (!empty($parameters)) {
                    $parameterType = $parameters[0]->getType();
                    return $parameterType ? $parameterType->getName() : null;
                }
            }
        } catch (\ReflectionException $e) {
            //dd("Error");
            //\Log::error("Reflection error for class {$class}: " . $e->getMessage());
        }

        return null;
    }



    protected function registerModuleLivewireComponents(string $moduleName, string $livewirePath)
    {

        $namespace = "App\\Modules\\$moduleName\\Http\\Livewire";
        $components = $this->scanLivewireComponents($livewirePath, $namespace);

        foreach ($components as $className => $alias) {
            // Directly register the component with the dynamically generated alias
            $alias = strtolower("$moduleName.$alias");
            Livewire::component($alias, $className);
        }
    }


        protected function scanLivewireComponents(string $path, string $namespace)
    {
        $components = [];
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();

            // Generate class name
            $className = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            // Generate alias manually
            $alias = $this->generateAlias($relativePath);

            $components[$className] = $alias;
        }

        return $components;
    }























     private function setupModules()
    {
        // Get all module directories
        if (!file_exists(base_path('app/Modules'))) {
            return;
        }
            
            
        $modules = File::directories(base_path('app/Modules'));

        // Loop through each module to load views, routes, and config files dynamically
        foreach ($modules as $module) {
            $moduleName = basename($module); // Get the module name from the directory


            // Load Migrations
            /*
                Migrations for each module are loaded dynamically by checking
                for the presence of migration files in each module.
            */
            $migrationPath = $module . '/Database/Migrations';
            if (File::exists($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
            }



            //Post::observe(PostObserver::class);
            // Load Onservers
            /*$observerPath = app_path("Modules/{$moduleName}/Observers");

            if (File::exists($observerPath)) {

                foreach (File::allFiles($observerPath) as $file) {
                    $class = $this->getClassFromFile($moduleName, "Observers", $file->getPathname());
                    // Ensure the class exists and can be reflected
                    if (class_exists($class)) {
                        $model = str_replace("Observer", "", $class);
                        //dd($model, $class);
                        \App\Modules\Inventory\Models\InventoryTransaction::observe(\App\Modules\Inventory\Observers\InventoryTransactionObserver::class);
                    }
                }
            }*/

            // Load Events
            $eventPath = app_path("Modules/{$moduleName}/Listeners");

            if (File::exists($eventPath)) {

                foreach (File::allFiles($eventPath) as $file) {
                    $class = $this->getClassFromFile($moduleName, "Listeners", $file->getPathname());
                    // Ensure the class exists and can be reflected
                    if (class_exists($class)) {
                        $eventType = $this->getEventFromListener($class);
                        if ($eventType) {
                            Event::listen($eventType, $class);
                            \Log::info("Registered event listener: {$class} for event: {$eventType}");
                        }
                    }
                }
            }


            // Load Views
            // This will allow you to reference views using module_name::view-name (e.g., inventory::view-name).
            $viewPath = $module . '/Resources/views'; // Construct the view path for the module
            if (File::exists($viewPath)) {
                $alias = strtolower($moduleName) . '.views';
                view()->addNamespace($alias, $viewPath);
            }

            // Load Components directory namespace expected in the module's main directory eg. /app/Modules/Core/Components
            $componentPath = $module . '/Components';
            if (File::exists($componentPath)) {
                Blade::componentNamespace(strtolower($moduleName) . '.components', $componentPath);
            }


            // Register Livewire components
            $livewirePath = $module . '/Http/Livewire';
            $namespace = "App\\Modules\\$moduleName\\Http\\Livewire";

            if (File::exists($livewirePath)) {
                $this->registerModuleLivewireComponents($moduleName, $livewirePath);
            }



            // Load Routes
            // loads the module’s routes file (web.php), if it exists,
            //so you don’t have to manually include each route file for every module.
            // Suspend loading the 'Core' modules's routes to avoid conflicts
            $routePath = $module . '/Routes/web.php';
            if (File::exists($routePath)) {
                if ($moduleName != 'Core') {
                    Route::middleware('web')
                        ->group($routePath);
                }
            }


            // Load API Routes
            $apiRoutePath = $module . '/Routes/api.php';
            if (File::exists($apiRoutePath)) {
                if ($moduleName != 'Core') {
                    Route::prefix('api')
                        ->middleware('api')
                        ->group($apiRoutePath);
                }

            }



            // Load Assets as Views
            $assetPath = $module . '/Resources/assets';
            if (File::exists($assetPath)) {
                view()->addNamespace(strtolower($moduleName) . '.assets', $assetPath);
            }






            // Load All Configurations
            /*
                Method merges each module’s configuration file (in this case,
                field-definitions.php) with the application’s configuration,
                using a custom namespace like inventory.field-definitions.
            */
            $configPath = $module . '/Config';
            /*if (File::exists($configPath)) {
                // Get all files in the Config folder
                $configFiles = File::files($configPath);

                foreach ($configFiles as $configFile) {
                    $fileName = pathinfo($configFile, PATHINFO_FILENAME); // Get the file name without extension
                    // Merge each config file with the application config

                    // Check if the corresponding database table exists add the config file
                    //if (Schema::hasTable(strtolower(Str::plural($fileName)))
                       // || Schema::hasTable(strtolower(Str::singular($fileName))) // for pivot table singular names
                    //) {
                        $this->mergeConfigFrom($configFile, strtolower($moduleName) . '.' . $fileName);
                   // }
                }
            }*/








        }


        // Now loade the 'core' module's routes
        if (File::exists(app_path("Modules/Core/Routes/web.php"))) {
            Route::middleware('web')->group(app_path("Modules/Core/Routes/web.php"));
        }

        // Core Api loading
        if (File::exists(app_path("Modules/Core/Routes/api.php"))) {
            Route::prefix('api')->middleware('api')->group(app_path("Modules/Core/Routes/api.php"));
        }

    }







}