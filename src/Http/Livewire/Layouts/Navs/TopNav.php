<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Layouts\Navs;

use Livewire\Component;
use QuickerFaster\LaravelUI\Traits\HasNavItems;

class TopNav extends Component
{
    use HasNavItems;

    public array $items = [];
    public string $activeContext = 'employees'; // Default context
    public int $maxDesktop = 5;
    public int $maxMobile = 3;

    public $moduleName;

    public function mount(array $items = [])
    {
        ///$this->items = $items ?: $this->defaultNavItems();
    }



public function logout() {
    // Let Laravel handle the URL generation - it's smart about http vs https
    return redirect(url('/logout'));
}


    // In your TopBar component
    public function selectContext($context)
    {
        //$this->activeContext = $context;
        //$this->dispatch('contextChanged', $context);
        // Navigate using the page manager
    //$this->dispatch('pageChanged', $context);
        //$this->redirect($context, navigate: true);
        //$this->dispatch('$refresh');


    }
    

    /*public function selectContext($contextKey)
    {
        $this->activeContext = $contextKey;
        $this->dispatch('contextChanged', $contextKey);
        
        // Find and navigate to the route associated with this context
        foreach ($this->items as $item) {
            if ($item['key'] === $contextKey && isset($item['route'])) {
                return redirect()->to($item['route']);
            }
        }
    }*/

    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.layouts";
        
        return view("$layoutPath.navs.top-nav")
            ->layout("$layoutPath.app");
    }
}