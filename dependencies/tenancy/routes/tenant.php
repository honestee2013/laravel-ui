<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Modules\Admin\Http\Livewire\AccessControls\AccessControlManager;

use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantSessionsController;
use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantRegisterController;
use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantResetController;
use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantInfoUserController;
use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantHomeController;
use QuickerFaster\LaravelUI\Http\Controllers\Tenants\Auth\TenantChangePasswordController;

use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

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








Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,

])->group(function () {




    // Register Livewire routes using the proper method
    $baseUrl = config('app.url');

    Route::post("{$baseUrl}/livewire/update", [HandleRequests::class, 'handleUpdate'])
        ->name('tenant.livewire.update')
        ->middleware('web');

    Route::get("{$baseUrl}/livewire/livewire.js", [FrontendAssets::class, 'returnJavaScriptAsFile'])
        ->name('tenant.livewire.script')
        ->middleware('web');



    Route::get('/login', function () {
        return view('session/login-session');
    })->name('tenant.login');



    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', [TenantHomeController::class, 'home']);
        Route::get('/user-profile', [TenantInfoUserController::class, 'create']);
        Route::post('/user-profile', [TenantInfoUserController::class, 'store']);
        Route::get('/logout', [TenantSessionsController::class, 'destroy'])->name('tenant.logout');
    });

    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [TenantRegisterController::class, 'create']);
        Route::post('/register', [TenantRegisterController::class, 'store']);

        Route::get('/login', [TenantSessionsController::class, 'create']);
        Route::post('/session', [TenantSessionsController::class, 'store']);

        Route::get('/login/forgot-password', [TenantResetController::class, 'create']);
        Route::post('/forgot-password', [TenantResetController::class, 'sendEmail']);
        Route::get('/reset-password/{token}', [TenantResetController::class, 'resetPass'])->name('tenant.password.reset');
        Route::post('/reset-password', [TenantChangePasswordController::class, 'changePassword'])->name('tenant.password.update');

    });








    Route::get('/{module}/{view}/{id?}', function ($module, $view, $id = null) {
        // Validation

        Validator::make(['module' => $module, 'view' => $view, 'id' => $id], [
            'module' => 'required|string',
            'view' => 'required|string',
            'id' => 'nullable|integer',
        ])->validate();

        $allowedModules = ['system', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'admin'];

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
    })->middleware("auth"); // Must login



















    /*Route::get('/', function () {
        return view('home');
    });*/



    /*Route::group(['middleware' => 'auth'], function () {

        Route::get('/', [HomeController::class, 'home']);
        /*Route::get('dashboard', function () {
            return view('dashboard');
        })->name('dashboard');* /

        Route::get('billing', function () {
            return view('billing');
        })->name('billing');

        Route::get('profile', function () {
            //return view('profile');
            // Center the content
            echo "<div style='text-align: center; margin-top: 50px;'>";
            echo "<h1 class = 'text-primary'> Coming Soon! </h1>";
            echo "<a href='/dashboard' class = 'text-info text-decoration-underline'>Go to Dashboard</a>";
            echo "</div>";
        })->name('profile');

        Route::get('rtl', function () {
            return view('rtl');
        })->name('rtl');

        Route::get('user-management', function () {
            return view('laravel-examples/user-management');
        })->name('user-management');

        Route::get('tables', function () {
            return view('tables');
        })->name('tables');

        Route::get('virtual-reality', function () {
            return view('virtual-reality');
        })->name('virtual-reality');

        Route::get('static-sign-in', function () {
            return view('static-sign-in');
        })->name('sign-in');

        Route::get('static-sign-up', function () {
            return view('static-sign-up');
        })->name('sign-up');

        Route::get('/logout', [TenantSessionsController::class, 'destroy'])->name('tenant.logout');

        Route::get('/user-profile', [InfoUserController::class, 'create']);
        Route::post('/user-profile', [InfoUserController::class, 'store']);

        Route::get('/login', function () {
            return view('dashboard');
        })->name('sign-up');

    });*/





});




