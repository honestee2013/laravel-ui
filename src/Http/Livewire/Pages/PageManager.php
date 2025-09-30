<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Route;

class PageManager extends Component
{
    public $currentPage = "dashboard";
    public $pageParams = [];
    
    protected $listeners = [
        'pageChanged' => 'updatePage',
        'contextChanged' => 'updateContext',
    ];

    
    
    public function mount()
    {
        
        // Get the current route name and set it as the current page
        $routeName = Route::currentRouteName();
        if ($routeName && $routeName !== 'page-manager') {
            $this->currentPage = $routeName;
        }
        
        // Capture any route parameters
        $this->pageParams = request()->route()->parameters();
    }

    
    public function updatePage($page, $params = [])
    {
        
        $this->currentPage = $page;
        $this->pageParams = $params;
        
        // Update browser URL without full page reload
        $this->dispatch('page-changed', [
            'page' => $page,
            'params' => $params
        ]);
    }
    
    public function render()
    {
        
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        
        // Check if the requested page exists
        $viewPath = "qf::components.livewire.{$UIFramework}.pages.{$this->currentPage}";
        
        if (!view()->exists($viewPath)) {
            // Fallback to a 404 page or dashboard
            $this->currentPage = 'dashboard';
            $viewPath = "qf::components.livewire.{$UIFramework}.pages.dashboard";
        }
        
        //return view($viewPath, $this->pageParams)
            //->layout("qf::components.livewire.{$UIFramework}.layouts.app");


        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        return view("qf::components.livewire.$UIFramework.pages.page-manager", ["viewPath" => $viewPath])
            ->layout("qf::components.livewire.$UIFramework.layouts.app"); // 👈 important

    }
}