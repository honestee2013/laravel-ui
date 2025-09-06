<?php

namespace QuickerFaster\LaravelUI\Components\Livewire\Layouts;

use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems; // <-- Import the trait

class BottomBar extends Component
{
    use HasNavItems; // <-- Use the trait

    public array $items = [];

    public int $maxVisible = 4; // number of visible buttons before "More"

    public function mount(array $items = [])
    {
        $this->items = $items ?: $this->defaultNavItems();
    }


    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::layouts.livewire.$UIFramework.bottom-bar")
            ->layout("qf::layouts.livewire.$UIFramework.app"); // 👈 important
    }
}
