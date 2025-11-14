<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


use QuickerFaster\LaravelUI\Http\Controllers\Tenant\ProvisioningController;
use QuickerFaster\LaravelUI\Http\Livewire\Tenants\OnboardingForm;
use QuickerFaster\LaravelUI\Http\Middleware\PreTenancyConfiguration;
use QuickerFaster\LaravelUI\Http\Middleware\ManualTenantBootstrap;


use App\Modules\Access\Http\Livewire\AccessControls\AccessControlManager;
use App\Modules\System\Http\Controllers\ReportController;


use QuickerFaster\LaravelUI\Http\Livewire\Auth\SignupForm;
use QuickerFaster\LaravelUI\Http\Livewire\Auth\QuickConfiguration;


use QuickerFaster\LaravelUI\Http\Controllers\Central\Auth\VerificationController;





/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/






Route::get('/auto-login', function (Illuminate\Http\Request $request) {
    $token = $request->get('token');

    if (!$token) {
        return redirect('/login')->with('error', 'Invalid login link');
    }

    // Get from cache
    $tokenData = \Cache::get('login_token:' . $token);

    if (!$tokenData) {
        return redirect('/login')->with('error', 'Invalid or expired login link');
    }

    // Verify tenant matches
    if ($tokenData['tenant_id'] !== tenant('id')) {
        return redirect('/login')->with('error', 'Invalid tenant');
    }

    // Find user in tenant database
    $user = \App\Models\User::where('email', $tokenData['user_email'])->first();

    if ($user) {
        Auth::login($user);

        // Clear the token
        \Cache::forget('login_token:' . $token);

        // return redirect('/hr/dashboard')->with('success', 'Welcome to your workspace!');

        // Compose view path
        $module = "hr";
        $view = "dashboard";
        $viewName = $module . '.views::' . $view;


        // Check view existence
        if (view()->exists($viewName)) {
            return view($viewName);
        }


    }

    return redirect('/login')->with('error', 'User account not found');
});






Route::middleware([
    'web',

    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,

])->group(function () {


  // Livewire routes MUST be registered FIRST
    Livewire::setUpdateRoute(function ($handle) {
        return Route::post('/livewire/update', $handle);
    });


    Livewire::setScriptRoute(function ($handle) {
        return Route::get('/livewire/livewire.js', $handle);
    });







    // Your other application routes...
    Route::get('/test', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });











    Route::get('/test', function () {
//dd(App\Modules\Hr\Models\Department::all(), tenant()->id);

        $currentDb = DB::connection()->getDatabaseName();
        $tenantId = app(\Stancl\Tenancy\Contracts\Tenant::class)->id;
        return "Tenant: {$tenantId} | Database: {$currentDb}";


        /*tenant()->run(function () {
            /*\Artisan::call(
                'tenants:migrate', [
                "--tenants" => 'tenant1'
                ]
            );* /

            \Artisan::call('tenants:seed', [
                "--tenants" => 'tenant1',
                "--force" => true // Add this line
            ]);





        });*/
    });

    Route::get('/test-csrf', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
            'session_domain' => config('session.domain'),
            'current_domain' => request()->getHost(),
            'cookies' => request()->cookies->all()
        ]);
    });



    Route::get('/{module}/{view}/{id?}', function ($module, $view, $id = null) {
        // Validation



        Validator::make(['module' => $module, 'view' => $view, 'id' => $id], [
            'module' => 'required|string',
            'view' => 'required|string',
            'id' => 'nullable|integer',
        ])->validate();









    // This code should move to a more appropriate location
    // Ensure availability of directories per tenant for assets and others

$tenantId = tenant()?->id ?? null;

if ($tenantId) {
    $directories = [
        'framework/cache',
        'framework/views',
        'app/public',
        'app/public/livewire-tmp', // Livewire temporary uploads
        'logs',
    ];


    foreach ($directories as $directory) {
        // Use the correct path - directly in storage/ not storage/app/
        $path = storage_path("{$directory}");
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            //echo "Created: {$path}\n";
        }
    }
}










        $allowedModules = ['system', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'access'];

        if (!in_array($module, $allowedModules)) {
            abort(404, 'Invalid module');
        }
        //dd(auth()->user());

        // Chech if only admin can access this view. If the user is not admin do not proceed
        if (in_array($view, AccessControlManager::ROLE_ADMIN_ONLY_VIEWS)) {
            // Check if the user has the role
            if (!auth()->check() || !auth()->user()->hasRole(['admin', 'super_admin'])) {
                abort(403, 'Unauthorized');
            }

            // If user is  not admin, check if the user has the permission
        } else if (auth()->check() && !auth()->user()->hasRole(['admin', 'super_admin'])) {
            // Build a dynamic permission name
            $permission = "view_" . AccessControlManager::getViewPerminsionModelName(($view));

            // Check permission or role
            if (!auth()->check() || !auth()->user()->can($permission)) {
                if ($view !== "my-profile") {
                    abort(403, 'Unauthorized');
                }
            }
        }



        // Compose view path
        $viewName = $module . '.views::' . $view;



        // Check view existence
        if (view()->exists($viewName)) {
            return view($viewName, ["id" => $id]);
        }

        abort(404, 'View not found');
    });//->middleware("auth"); // Must login






});



