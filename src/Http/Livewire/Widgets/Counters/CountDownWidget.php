<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Counters;

use QuickerFaster\LaravelUI\Http\Livewire\Widgets\BaseWidget;

class CountDownWidget extends BaseWidget
{
    public $endTime;
    public $format = 'full'; // full, compact, days_only, hours_only
    public $title = 'Time Remaining';
    public $expiredMessage = "Time's up!";
    public $showExpiredIcon = true;

    // Time calculation properties
    public $remainingTime = [];
    public $hasExpired = false;

    public function mount($widgetId, $config, $initialData = null)
    {
        parent::mount($widgetId, $config, $initialData);
        
        $this->title = $config['title'] ?? 'Time Remaining';
        $this->format = $config['format'] ?? 'full';
        $this->expiredMessage = $config['expired_message'] ?? "Time's up!";
        $this->showExpiredIcon = $config['show_expired_icon'] ?? true;
        
        // Calculate end time from various configuration options
        $this->endTime = $this->calculateEndTime($config);
        
        $this->calculateRemainingTime();
    }

    protected function calculateEndTime($config)
    {
        // Option 1: Direct timestamp
        if (isset($config['end_time'])) {
            return $config['end_time'];
        }
        
        // Option 2: Duration from now (e.g., "+30 days")
        if (isset($config['duration'])) {
            return strtotime($config['duration']);
        }
        
        // Option 3: Specific date string
        if (isset($config['end_date'])) {
            return strtotime($config['end_date']);
        }
        
        // Option 4: Calculate from data (e.g., project deadline)
        if (isset($this->data['end_time'])) {
            return $this->data['end_time'];
        }
        
        // Default: 24 hours from now
        return strtotime('+24 hours');
    }

    public function calculateRemainingTime()
    {
        $now = time();
        $difference = $this->endTime - $now;
        
        if ($difference <= 0) {
            $this->hasExpired = true;
            $this->remainingTime = [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'total_seconds' => 0
            ];
            return;
        }
        
        $this->hasExpired = false;
        $this->remainingTime = [
            'days' => floor($difference / (60 * 60 * 24)),
            'hours' => floor(($difference % (60 * 60 * 24)) / (60 * 60)),
            'minutes' => floor(($difference % (60 * 60)) / 60),
            'seconds' => $difference % 60,
            'total_seconds' => $difference
        ];
    }

    public function getFormattedTime()
    {
        if ($this->hasExpired) {
            return $this->expiredMessage;
        }

        $r = $this->remainingTime;
        
        return match($this->format) {
            'compact' => sprintf('%02d:%02d:%02d:%02d', $r['days'], $r['hours'], $r['minutes'], $r['seconds']),
            'days_only' => "{$r['days']} days",
            'hours_only' => "{$r['hours']} hours",
            'readable' => $this->getReadableFormat(),
            default => "{$r['days']}d {$r['hours']}h {$r['minutes']}m {$r['seconds']}s"
        };
    }

    protected function getReadableFormat()
    {
        $r = $this->remainingTime;
        $parts = [];
        
        if ($r['days'] > 0) $parts[] = "{$r['days']} day" . ($r['days'] > 1 ? 's' : '');
        if ($r['hours'] > 0) $parts[] = "{$r['hours']} hour" . ($r['hours'] > 1 ? 's' : '');
        if ($r['minutes'] > 0) $parts[] = "{$r['minutes']} minute" . ($r['minutes'] > 1 ? 's' : '');
        if (empty($parts) || $r['seconds'] > 0) $parts[] = "{$r['seconds']} second" . ($r['seconds'] > 1 ? 's' : '');
        
        return implode(' ', $parts);
    }

    public function getUrgencyColor()
    {
        if ($this->hasExpired) return 'danger';
        
        $totalHours = $this->remainingTime['days'] * 24 + $this->remainingTime['hours'];
        
        if ($totalHours < 24) return 'danger';
        if ($totalHours < 72) return 'warning';
        return 'success';
    }

    public function render()
    {
        $data = [
            'endTime' => $this->endTime,
            'title' => $this->title,
            'formattedTime' => $this->getFormattedTime(),
            'hasExpired' => $this->hasExpired,
            'urgencyColor' => $this->getUrgencyColor(),
            'expiredMessage' => $this->expiredMessage,
            'showExpiredIcon' => $this->showExpiredIcon,
            'remainingTime' => $this->remainingTime,
            'isLoading' => $this->isLoading,
            'config' => $this->config
        ];

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";

        return view("$layoutPath.counters.count-down", $data)
            ->layout("$layoutPath.app");
    }
}