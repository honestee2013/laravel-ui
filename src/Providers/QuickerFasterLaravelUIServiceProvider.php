<?php


namespace QuickerFaster\LaravelUI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

use Illuminate\Support\Facades\Route;


class QuickerFasterLaravelUIServiceProvider extends ServiceProvider
{

    public function boot()
    {

        
        // Check if package routes should be loaded
        if (config('qf_laravel_ui.load_routes')) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }
        // 1. Publish configuration file
        $this->publishes([
            __DIR__.'/../../config/qf_laravel_ui.php' => config_path('qf_laravel_ui.php'),
        ], 'qf-config');



        // 4. Register package views and set namespace
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'qf');

        // 5. Publish views (all frameworks)
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/qf'),
        ], 'qf-views');


        // Load your package's translations
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'qf');
        $this->publishes([
            __DIR__.'/../../lang' => $this->app->langPath('vendor/qf'),
        ], 'qf-translations');



        // Define a custom middleware group for your package
        Route::middleware(['web', 'auth'])->group(__DIR__.'/../../routes/web.php');


        // 2. Register Livewire components
        $this->registerLivewireComponents();
        //Livewire::component('qf::topnav', \QuickerFaster\LaravelUI\Components\Livewire\TopNav::class);

        // 3. Register Blade components
        $this->registerBladeComponents();


        

    }

    public function register()
    {
     
        // Use the snake_case filename as the configuration key
        $this->mergeConfigFrom(
            __DIR__.'/../../config/qf_laravel_ui.php', 'qf_laravel_ui'
        );
    }

    /**
     * Automatically registers Livewire components from the package.
     */
    protected function registerLivewireComponents()
    {
        $livewirePath = __DIR__ . '/../Components/Livewire';
        if (! is_dir($livewirePath)) {
            return;
        }

        foreach ((new Finder)->in($livewirePath)->files() as $file) {
            // Get the component class, e.g., QuickerFaster\LaravelUI\Components\Livewire\DataTable
            $componentClass = 'QuickerFaster\\LaravelUI\\Components\\Livewire\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            // Get the component alias, e.g., 'qf::data-table'
            $alias = 'qf::' . $this->generateAlias($file->getRelativePathname()); // Str::of($file->getRelativePathname())
               
 
            Livewire::component($alias, $componentClass);
        }
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

























    /*protected function registerLivewireComponents(string $livewirePath)
    {
        $namespace = "QuickerFaster\\LaravelUI\\Components\\Livewire";
        $components = $this->scanLivewireComponents($livewirePath, $namespace);

        foreach ($components as $className => $alias) {
            // Directly register the component with the dynamically generated alias
            $alias = strtolower($alias);
            //dd($alias);
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
    }*/

    protected function generateAlias(string $relativePath): string
    {
        // Replace directory separators with dots
        $alias = str_replace(['/', '\\'], '.', $relativePath);

        // Convert PascalCase or camelCase segments to kebab-case
        $alias = preg_replace('/([a-z])([A-Z])/', '$1-$2', $alias);

        // Convert to lowercase and remove the ".php" extension
        $alias = strtolower(str_replace('.php', '', $alias));

        return $alias;
    }






}