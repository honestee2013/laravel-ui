<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Layouts;


use Livewire\Component;



class DashboardControl extends Component
{



    public $timeDuration = "this_month";



    public function updatedTimeDuration()
    {
        $this->dispatch("configChangedEvent", ["timeDuration" => $this->timeDuration]);
    }



    public function render()
    {
        $view = "dashboard.views::components.layouts.dashboards.dashboard-control";
        return view($view, []);
    }

}
