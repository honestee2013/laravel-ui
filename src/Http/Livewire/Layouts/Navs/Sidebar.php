<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Layouts\Navs;

use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems;

class Sidebar extends Component
{
    use HasNavItems;

    public string $state = 'full'; // sidebar states: full, icon, hidden
    public string $context = 'people'; // Default context
    public string $moduleName = 'hr'; // Default module
    public array $contextItems = [];

    protected $listeners = ['contextChanged' => 'updateContext'];

    public function mount()
    {
        $this->updateContext($this->context);
    }

    public function updateContext($context)
    {
        $this->context = $context;
        $this->contextItems = config("sidebar_contexts.{$context}", []);
    }

    public function toggleState()
    {
        if ($this->state === 'full') {
            $this->state = 'icon';
        } else if ($this->state === 'icon') {
            $this->state = 'hidden';
        } else {
            $this->state = 'full';
        }
    }

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.layouts";

        return view("$layoutPath.navs.sidebar")
            ->layout("$layoutPath.app");
    }
}