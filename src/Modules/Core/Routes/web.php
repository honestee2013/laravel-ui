<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Access\Http\Livewire\AccessControls\AccessControlManager;
use App\Modules\Core\Http\Controllers\ReportController;


Route::get('/{module}/{view}', function ($module, $view) {
    // Validation
    Validator::make(['module' => $module, 'view' => $view], [
        'module' => 'required|string',
        'view' => 'required|string',
    ])->validate();

    $allowedModules = ['core', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'access'];

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
        return view($viewName);
    }

    abort(404, 'View not found');
});















