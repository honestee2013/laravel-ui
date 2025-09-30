<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Layouts\Navs;

use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems;

class BottomBar extends Component
{
    use HasNavItems;

    public array $items = [];
    public string $context = 'people'; // Default context

    public int $maxVisible = 4; // number of visible buttons before "More"

    public function mount(array $items = [])
    {
        ///$this->items = $items ?: $this->defaultNavItems();
    }

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.layouts";
        
        return view("$layoutPath.navs.bottom-bar")
            ->layout("$layoutPath.app");
    }
}