<?php

namespace QuickerFaster\LaravelUI\Components\Livewire;


use Livewire\Component;


class Dashboard extends Component
{
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::components.livewire.$UIFramework.dashboard")
            ->layout("qf::layouts.livewire.$UIFramework.app"); // 👈 important
    }
}

