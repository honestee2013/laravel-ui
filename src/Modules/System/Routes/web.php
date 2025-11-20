<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Http\Livewire\AccessControls\AccessControlManager;
/*use App\Modules\System\Http\Controllers\ReportController;




//Route::get('/dashboard', Dashboard::class)->name('dashboard');



use QuickerFaster\LaravelUI\Http\Livewire\Auth\SignupForm;
use QuickerFaster\LaravelUI\Http\Livewire\Auth\ModuleSelection;
use QuickerFaster\LaravelUI\Http\Livewire\Tenants\OnboardingForm;


use QuickerFaster\LaravelUI\Http\Controllers\Central\Auth\VerificationController;*/









/*foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // your actual routes*/

        // These routes should be in a `web` middleware group (Laravel does this by default in RouteServiceProvider)
        /*Route::middleware(['web'])->group(function () {
            Route::get('/client-register', SignupForm::class)->name('register');
            Route::get('/verify', [VerificationController::class, 'show'])->name('verification.notice');
            Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('verification.verify');
            Route::middleware('verified.company')->get('/modules', ModuleSelection::class)->name('module.selection');
        });*/

        //Route::get('/onboarding', OnboardingForm::class)->name('onboarding');


   // });
//}



/*

Route::get('/{module}/{view}/{id?}', function ($module, $view, $id = null) {
    // Validation
  
    Validator::make(['module' => $module, 'view' => $view, 'id' => $id], [
        'module' => 'required|string',
        'view' => 'required|string',
        'id' => 'nullable|integer',
    ])->validate();

    $allowedModules = ['system', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'access'];

    if (!in_array($module, $allowedModules)) {
        abort(404, 'Invalid module');
    }


    // Chech if only admin can access this view. If the user is not admin do not proceed
    if (in_array($view, AccessControlManager::ROLE_ADMIN_ONLY_VIEWS)) {
        // Check if the user has the role
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

    // If user is  not admin, check if the user has the permission
    } else if (auth()->check() && !auth()->user()->hasRole(['admin', 'super_admin'])) {
        // Build a dynamic permission name
        $permission = "view_".AccessControlManager::getViewPerminsionModelName(($view));

        // Check permission or role
        if (!auth()->check() || !auth()->user()->can($permission)) {
            if ($view !=="my-profile") {
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
});



*/











