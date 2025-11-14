<?php


use Illuminate\Support\Facades\Route;

use QuickerFaster\LaravelUI\Http\Livewire\Pages\Dashboard;
use QuickerFaster\LaravelUI\Http\Livewire\Pages\Settings;
use QuickerFaster\LaravelUI\Http\Livewire\Pages\People;
use QuickerFaster\LaravelUI\Http\Livewire\Pages\Rule;











Route::middleware(['auth'])->group(function () {

    //Route::get('/dashboard', Dashboard::class)->name('dashboard');




    Route::get('/', Dashboard::class)->name('home');
    //Route::get('/home', Dashboard::class)->name('home');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    //Route::get('/people', People::class)->name('people');
    //Route::get('/rules', Rule::class)->name('rules');
    Route::get('/settings', Settings::class)->name('settings');



    Route::get('/employees', function () {
        return view('employees.index');
    })->name('employees.index');

    Route::get('/attendance', function () {
        return view('attendance.index');
    })->name('attendance.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/help', function () {
        return view('help');
    })->name('help');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

});




// Instead of individual routes for each page, use a catch-all route
/*Route::get('/{page?}', \QuickerFaster\LaravelUI\Http\Livewire\Pages\PageManager::class)
    ->where('page', '.*')
    ->name('page-manager');*/

// Or if you want to keep specific routes for certain pages:
/*Route::get('/dashboard', function () {
    return app()->make(\QuickerFaster\LaravelUI\Http\Livewire\Pages\PageManager::class)
        ->callAction('updatePage', ['dashboard', []]);
})->name('dashboard');

Route::get('/employees', function () {
    return app()->make(\QuickerFaster\LaravelUI\Http\Livewire\Pages\PageManager::class)
        ->callAction('updatePage', ['employees', []]);
})->name('employees');*/




/*Route::get('/{module}/{view}', function ($module, $view) {
    // Validation
    Validator::make(['module' => $module, 'view' => $view], [
        'module' => 'required|string',
        'view' => 'required|string',
    ])->validate();

    $allowedModules = ['system', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'access'];

    if (!in_array($module, $allowedModules)) {
        abort(404, 'Invalid module');
    }


    // Chech if only admin can access this view. If the user is not admin do not proceed
    /*if (in_array($view, AccessControlManager::ROLE_ADMIN_ONLY_VIEWS)) {
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
    }* /



    // Compose view path
    $viewName = $module . '.views::' . $view;



    // Check view existence
    if (view()->exists($viewName)) {
        return view($viewName);
    }

    abort(404, 'View not found');
});*/



