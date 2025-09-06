<?php

namespace QuickerFaster\LaravelUI\Components\Blade;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TestComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::components.blade.".$UIFramework.".test-component");
    }
}
