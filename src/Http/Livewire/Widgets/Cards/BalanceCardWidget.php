<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Cards;

use Livewire\Component;

class BalanceCardWidget extends Component
{
    public $widgetId;
    public $config;
    public $current = 0;
    public $total = 0;
    public $percentage = 0;
    public $color = 'primary';
    public $unit = 'days';
    public $isLoading = false;
    
    protected $listeners = [
        'dashboardDataUpdated' => 'onDataUpdated',
        'refreshWidget' => 'refresh'
    ];
    
    public function mount($widgetId, $config, $initialData = null)
    {
        $this->widgetId = $widgetId;
        $this->config = $config;
        $this->color = $config['color'] ?? 'primary';
        $this->unit = $config['unit'] ?? 'days';
    

        if ($initialData) {
            $this->loadData($initialData);
        } else {
            $this->fetchData();
        }
    }
    
    protected function loadData($data)
    {
        // Accept multiple data formats
        if (isset($data['current']) && isset($data['total'])) {
            $this->current = (float) $data['current'];
            $this->total = (float) $data['total'];
        } elseif (isset($data['value'])) {
            // For simple count widgets
            $this->current = (float) $data['value'];
            $this->total = $this->config['total'] ?? $this->current;
        } elseif (is_array($data) && count($data) > 0) {
            // Fallback: first value as current
            $this->current = (float) ($data[0] ?? 0);
            $this->total = $this->config['total'] ?? $this->current;
        }
        
        $this->calculatePercentage();
    }
    
    protected function calculatePercentage()
    {
        if ($this->total > 0) {
            $this->percentage = ($this->current / $this->total) * 100;
        } else {
            $this->percentage = 0;
        }
    }
    
    protected function fetchData()
    {
        // If config has model/calculation, fetch from database
        if (isset($this->config['model']) && isset($this->config['calculation_method'])) {
            $this->isLoading = true;
            // Simulate API call - you'll replace this with your actual data fetching
            // For now, use static values from config
            if (isset($this->config['current']) && isset($this->config['total'])) {
                $this->current = $this->config['current'];
                $this->total = $this->config['total'];
                $this->calculatePercentage();
            }
            
            $this->isLoading = false;
        }
    }
    
    public function onDataUpdated($dashboardData)
    {
        /*if (isset($dashboardData[$this->widgetId])) {
            $this->loadData($dashboardData[$this->widgetId]);
            $this->isLoading = false;
        }*/
    }
    
    public function refresh()
    {
        $this->isLoading = true;
        $this->fetchData();
        $this->dispatch('refreshDashboard');
    }
    
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";
        
        return view("$layoutPath.cards.balance-card", [
            'isLoading' => $this->isLoading,
            'color' => $this->color,
            'unit' => $this->unit,
            'current' => $this->current,
            'total' => $this->total,
            'percentage' => $this->percentage,
        ]);
    }
}