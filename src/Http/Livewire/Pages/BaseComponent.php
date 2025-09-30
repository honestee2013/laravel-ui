<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Pages;

use Livewire\Component;

class BaseComponent extends Component
{
    // Make it protected so it's accessible to child classes
    protected $UIFramework;
    
    public static $layout;

    public function mount()
    {
        // Set the property for the instance in the mount() method
        $this->UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');

        // You can also set the static layout here
        self::$layout = "qf::components.livewire." . $this->UIFramework . ".layouts.app";
    }

    public static function getLayout()
    {
        return self::$layout;
    }

}