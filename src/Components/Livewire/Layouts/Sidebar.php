<?php

namespace QuickerFaster\LaravelUI\Components\Livewire\Layouts;

use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems; // <-- Import the trait

class Sidebar extends Component
{

    use HasNavItems; // <-- Use the trait

    // sidebar states: full, icon, hidden
    public string $state = 'full';

    public array $items = [];

    public function mount(array $items = [])
    {
        $this->items = $items ?: $this->defaultNavItems();

    }



    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::layouts.livewire.$UIFramework.sidebar")
            ->layout("qf::layouts.livewire.$UIFramework.app"); // 👈 important
    }
}
