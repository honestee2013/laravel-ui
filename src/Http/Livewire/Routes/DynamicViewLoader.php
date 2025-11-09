<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Routes;


use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Modules\Access\Http\Livewire\AccessControls\AccessControlManager;

class DynamicViewLoader extends Component
{
    public $module;
    public $view;




    
    public function mount($module, $view)
    {
        Validator::make(['module' => $module, 'view' => $view], [
            'module' => 'required|string',
            'view' => 'required|string',
        ])->validate();
    
        $allowedModules = ['system', 'billing', 'sales', 'organization', 'hr', 'profile', 'item', 'warehouse', 'user', 'access'];
    
        if (!in_array($module, $allowedModules)) {
            abort(404, 'Invalid module');
        }
    
        if (in_array($view, AccessControlManager::ROLE_ADMIN_ONLY_VIEWS)) {
            if (!auth()->check() || !auth()->user()->hasRole(['admin', 'super_admin'])) {
                abort(403, 'Unauthorized');
            }
        } else if (auth()->check() && !auth()->user()->hasRole(['admin', 'super_admin'])) {
            $permission = "view_" . AccessControlManager::getViewPerminsionModelName(($view));
            if (!auth()->user()->can($permission) && $view !== "my-profile") {
                abort(403, 'Unauthorized');
            }
        }
    
        $this->module = $module;
        $this->view = $view;
    }
    


    public function render()
    {
        $viewName = $this->module . '.views::' . $this->view;
        
        if (view()->exists($viewName)) {
            return view("dynamic-view-loader", [
                'viewName' => $viewName,
                'module' => $this->module,
                'view' => $this->view,
            ]);
            //return view($viewName);
            
        }
    
        abort(404, 'View not found');
    }



}
