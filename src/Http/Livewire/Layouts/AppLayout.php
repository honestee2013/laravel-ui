<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Layouts;

use Livewire\Component;

class AppLayout extends Component
{
    public $activeContext = 'employees';
    
    protected $listeners = ['contextChanged' => 'updateContext'];
    
    public function updateContext($context)
    {
        dd("Applayout");
        $this->activeContext = $context;
        ////$this->emitTo('quicker-faster::layouts.navs.sidebar', 'contextChanged', $context);

    }
    
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.layouts";
        
        return view("$layoutPath.app-layout");
            //->layout("$layoutPath.app");
    }
}