<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Progresses;

use QuickerFaster\LaravelUI\Http\Livewire\Widgets\BaseWidget;

class ProgressBarWidget extends BaseWidget
{
    public $progress = 0;
    public $elementLabel;
    public $progressLabel;
    public $color = 'primary';
    public $showPercentage = true;
    public $progressColors = [
        '25' => 'danger',
        '50' => 'warning', 
        '75' => 'info',
        '100' => 'success',
    ];
    public $progressBarCSS = "height: 1em;";
    public $progressLabelCSS = "font-size: 0.88em;";

    public function mount($widgetId, $config, $initialData = null)
    {
        parent::mount($widgetId, $config, $initialData);
        
        $this->elementLabel = $config['element_label'] ?? $config['title'] ?? 'Progress';
        $this->progressLabel = $config['progress_label'] ?? '';
        $this->color = $config['color'] ?? 'primary';
        $this->showPercentage = $config['show_percentage'] ?? true;
        $this->progressBarCSS = $config['progress_bar_css'] ?? "height: 1em;";
        $this->progressLabelCSS = $config['progress_label_css'] ?? "font-size: 0.88em;";
        
        // Custom progress colors if provided in config
        if (isset($config['progress_colors']) && is_array($config['progress_colors'])) {
            $this->progressColors = $config['progress_colors'];
        }
        
        $this->calculateProgress();
    }

    protected function calculateProgress()
    {
        // First, try to get progress directly from static data in config
    // First, try direct value from config
    if (isset($this->config['progress_value'])) {
        $this->progress = $this->config['progress_value'];
        return;
    }
    
    // Then try static data
    if (isset($this->config['static_data'])) {
        $staticData = $this->config['static_data']['data'] ?? [0];
        $this->progress = $staticData[0] ?? 0;
        return;
    }
        
        // If no static data, try to calculate from widget data
        if (!$this->data || empty($this->data['data'])) {
            $this->progress = 0;
            return;
        }

        $data = $this->data['data'] ?? [];
        
        // Different progress calculation strategies
        $calculationMethod = $this->config['calculation_method'] ?? 'sum_percentage';
        
        switch ($calculationMethod) {
            case 'sum_percentage':
                $this->progress = $this->calculateSumPercentage($data);
                break;
                
            case 'average':
                $this->progress = $this->calculateAverage($data);
                break;
                
            case 'completion_rate':
                $this->progress = $this->calculateCompletionRate($data);
                break;
                
            case 'max_value':
                $this->progress = $this->calculateMaxValue($data);
                break;
                
            case 'custom':
                $this->progress = $this->calculateCustomProgress($data);
                break;
                
            default:
                $this->progress = $this->calculateSumPercentage($data);
        }
        
        // Ensure progress is between 0-100
        $this->progress = max(0, min(100, $this->progress));
    }

    protected function calculateSumPercentage($data)
    {
        if (empty($data)) return 0;
        
        $sum = array_sum($data);
        $maxPossible = $this->config['max_possible_value'] ?? 100;
        
        return ($sum / $maxPossible) * 100;
    }

    protected function calculateAverage($data)
    {
        if (empty($data)) return 0;
        
        $average = array_sum($data) / count($data);
        $maxPossible = $this->config['max_possible_value'] ?? 100;
        
        return ($average / $maxPossible) * 100;
    }

    protected function calculateCompletionRate($data)
    {
        if (empty($data)) return 0;
        
        $completed = array_sum($data);
        $total = $this->config['total_items'] ?? count($data);
        
        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    protected function calculateMaxValue($data)
    {
        if (empty($data)) return 0;
        
        $maxValue = max($data);
        $maxPossible = $this->config['max_possible_value'] ?? 100;
        
        return ($maxValue / $maxPossible) * 100;
    }

    protected function calculateCustomProgress($data)
    {
        // Allow custom calculation via closure in config
        if (isset($this->config['custom_calculation']) && is_callable($this->config['custom_calculation'])) {
            return call_user_func($this->config['custom_calculation'], $data, $this->config);
        }
        
        return $this->calculateSumPercentage($data);
    }

    public function getProgressColor()
    {
        $color = $this->color; // Use configured color as fallback
        
        foreach ($this->progressColors as $threshold => $thresholdColor) {
            if ($this->progress >= (float)$threshold) {
                $color = $thresholdColor;
            }
        }
        
        return $color;
    }

    public function getFormattedProgressLabel()
    {
        $baseLabel = $this->progressLabel;
        
        if ($this->showPercentage) {
            $percentage = round($this->progress, 1) . '%';
            return $baseLabel ? "{$baseLabel} {$percentage}" : $percentage;
        }
        
        return $baseLabel;
    }

    public function onDataUpdated($dashboardData)
    {
        parent::onDataUpdated($dashboardData);
        $this->calculateProgress();
    }

    public function render()
    {
        $data = [
            'progress' => $this->progress,
            'elementLabel' => $this->elementLabel,
            'progressLabel' => $this->getFormattedProgressLabel(),
            'color' => $this->getProgressColor(),
            'showPercentage' => $this->showPercentage,
            'progressBarCSS' => $this->progressBarCSS,
            'progressLabelCSS' => $this->progressLabelCSS,
            'isLoading' => $this->isLoading,
            'config' => $this->config
        ];

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";

        return view("$layoutPath.progresses.progress-bar", $data)
            ->layout("$layoutPath.app");
    }
}