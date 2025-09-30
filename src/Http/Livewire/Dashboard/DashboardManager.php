<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard;


use Livewire\Component;



class DashboardManager extends Component
{



    public $moduleName = "dashboard";
  
    public $timeDuration = "this_month";









    public function updatedTimeDuration()
    {
        $this->dispatch("configChangedEvent", ["timeDuration" => $this->timeDuration]);
    }



    public function render()
    {
        $view = $this->moduleName.".views::dashboard-manager";
      
        return view($view, []);
    }

}
