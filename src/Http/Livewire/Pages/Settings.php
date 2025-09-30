<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Pages;


use Livewire\Component;


class Settings extends Component
{


   protected $listeners = [
        'pageChanged' => 'updatePage',
        'contextChanged' => 'updateContext',
    ];

    public function updatePage() {
        $this->dispatch('$refresh');
    }


    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::components.livewire.$UIFramework.pages.settings")
            ->layout("qf::components.livewire.$UIFramework.layouts.app"); // 👈 important
    }
}



