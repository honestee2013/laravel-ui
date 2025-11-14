<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Counters;

use QuickerFaster\LaravelUI\Http\Livewire\Widgets\BaseWidget;

class CountUpWidget extends BaseWidget
{
    public $countTo = 0;
    public $prefix = '';
    public $suffix = '';
    public $title = 'Counter';
    public $useGrouping = true;
    public $groupingSeparator = ',';
    public $decimalSeparator = '.';
    public $duration = 2;
    public $useEasing = true;

    public function mount($widgetId, $config, $initialData = null)
    {
        parent::mount($widgetId, $config, $initialData);
        
        $this->title = $config['title'] ?? 'Counter';
        $this->prefix = $config['prefix'] ?? '';
        $this->suffix = $config['suffix'] ?? '';
        $this->useGrouping = $config['use_grouping'] ?? true;
        $this->groupingSeparator = $config['grouping_separator'] ?? ',';
        $this->decimalSeparator = $config['decimal_separator'] ?? '.';
        $this->duration = $config['duration'] ?? 2;
        $this->useEasing = $config['use_easing'] ?? true;
        
        $this->calculateCountTo();
    }

    protected function calculateCountTo()
    {
        // Option 1: Direct value from config
        if (isset($this->config['value'])) {
            $this->countTo = $this->config['value'];
            return;
        }
        
        // Option 2: Calculate from data
        if ($this->data && !empty($this->data['data'])) {
            $calculationMethod = $this->config['calculation_method'] ?? 'sum';
            
            $this->countTo = match($calculationMethod) {
                'sum' => array_sum($this->data['data']),
                'average' => count($this->data['data']) > 0 ? array_sum($this->data['data']) / count($this->data['data']) : 0,
                'count' => count($this->data['data']),
                'max' => max($this->data['data']),
                'min' => min($this->data['data']),
                'first' => $this->data['data'][0] ?? 0,
                default => array_sum($this->data['data'])
            };
            
            // Round if needed
            if (isset($this->config['decimals'])) {
                $this->countTo = round($this->countTo, $this->config['decimals']);
            }
            
            return;
        }
        
        // Option 3: Fallback to config count_to
        $this->countTo = $this->config['count_to'] ?? 0;
    }

    public function getFormattedValue()
    {
        $value = $this->countTo;
        
        // Apply number formatting
        if ($this->useGrouping && $value >= 1000) {
            $value = number_format(
                $value, 
                $this->config['decimals'] ?? 0, 
                $this->decimalSeparator, 
                $this->groupingSeparator
            );
        }
        
        return $value;
    }

    public function onDataUpdated($dashboardData)
    {
        $oldValue = $this->countTo;
        parent::onDataUpdated($dashboardData);
        $this->calculateCountTo();
        
        // Dispatch update if value changed
        if ($oldValue != $this->countTo) {
            $this->dispatch('updateCountUp', $this->widgetId, $this->countTo);
        }
    }

    public function render()
    {
        $data = [
            'countTo' => $this->countTo,
            'title' => $this->title,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'useGrouping' => $this->useGrouping,
            'groupingSeparator' => $this->groupingSeparator,
            'decimalSeparator' => $this->decimalSeparator,
            'duration' => $this->duration,
            'useEasing' => $this->useEasing,
            'formattedValue' => $this->getFormattedValue(),
            'isLoading' => $this->isLoading,
            'config' => $this->config
        ];

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";

        return view("$layoutPath.counters.count-up", $data)
            ->layout("$layoutPath.app");
    }
}