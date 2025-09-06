<?php

namespace QuickerFaster\LaravelUI\Components\Livewire;


use Livewire\Component;


class Settings extends Component
{
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::components.livewire.$UIFramework.settings")
            ->layout("qf::layouts.livewire.$UIFramework.app"); // 👈 important
    }
}

