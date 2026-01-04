<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Cards;

use Livewire\Component;

class StatusCardWidget extends Component
{
    public $widgetId;
    public $config;
    public $count = 0;
    public $statusText = '';
    public $color = 'info';
    public $showBadge = false;
    public $badgeText = '';
    public $isLoading = false;
    
    protected $listeners = [
        'dashboardDataUpdated' => 'onDataUpdated',
        'refreshWidget' => 'refresh'
    ];
    
    public function mount($widgetId, $config, $initialData = null)
    {
        $this->widgetId = $widgetId;
        $this->config = $config;
        $this->color = $config['color'] ?? 'info';
        
        // Configure badge
        $this->showBadge = $config['show_badge'] ?? true;
        $this->badgeText = $config['badge_text'] ?? '';
        
        // Configure status text
        $this->statusText = $config['status_text'] ?? $config['status'] ?? '';
        
        if ($initialData) {
            $this->loadData($initialData);
        } else {
            $this->fetchData();
        }
    }
    
    protected function loadData($data)
    {
        // Accept multiple data formats
        if (isset($data['count'])) {
            $this->count = (int) $data['count'];
        } elseif (isset($data['value'])) {
            $this->count = (int) $data['value'];
        } elseif (is_array($data) && count($data) > 0) {
            // Fallback: first value as count
            $this->count = (int) ($data[0] ?? 0);
        } elseif (is_numeric($data)) {
            $this->count = (int) $data;
        }
        
        // Auto-generate badge text if count > 0
        if ($this->count > 0 && empty($this->badgeText)) {
            $this->badgeText = $this->generateBadgeText();
        }
        
        // Auto-generate status text if empty
        if (empty($this->statusText)) {
            $this->statusText = $this->generateStatusText();
        }
    }
    
    protected function generateBadgeText()
    {
        if ($this->count === 0) {
            return 'All clear';
        }
        
        $badgeMap = [
            'warning' => ['Needs attention', 'Action required', 'Pending review'],
            'danger' => ['Urgent', 'Overdue', 'Critical'],
            'info' => ['In progress', 'Active', 'Ongoing'],
            'success' => ['Completed', 'Ready', 'Approved'],
            'primary' => ['New', 'Updated', 'Scheduled'],
        ];
        
        $options = $badgeMap[$this->color] ?? ['Items pending'];
        return $this->count === 1 
            ? rtrim($options[0], 's') // Remove 's' for singular
            : $options[0];
    }
    
    protected function generateStatusText()
    {
        $unit = $this->config['unit'] ?? 'items';
        
        if ($this->count === 0) {
            return "No {$unit}";
        } elseif ($this->count === 1) {
            return "1 {$unit}";
        } else {
            return "{$this->count} {$unit}";
        }
    }
    
    protected function fetchData()
    {
        // If config has model/calculation, fetch from database
        if (isset($this->config['model']) && isset($this->config['calculation_method'])) {
            $this->isLoading = true;
            
            // Simulate API call - replace with your actual data fetching
            // For now, use static value from config
            if (isset($this->config['count'])) {
                $this->count = $this->config['count'];
                $this->loadData(['count' => $this->count]);
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
    
    public function triggerAction()
    {
        if (isset($this->config['action']['event'])) {
            $this->dispatch($this->config['action']['event'], $this->widgetId);
        } elseif (isset($this->config['action']['url'])) {
            // You can redirect or emit an event
            $this->dispatch('navigateTo', $this->config['action']['url']);
        }
    }
    
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";
        
        return view("$layoutPath.cards.status-card", [
            'isLoading' => $this->isLoading,
            'color' => $this->color,
            'count' => $this->count,
            'statusText' => $this->statusText,
            'showBadge' => $this->showBadge,
            'badgeText' => $this->badgeText,
        ]);
    }
}