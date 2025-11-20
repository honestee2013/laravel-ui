<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Cards;

use QuickerFaster\LaravelUI\Http\Livewire\Widgets\BaseWidget;
use QuickerFaster\LaravelUI\Services\Analytics\Aggregator;
use Illuminate\Support\Facades\Cache;


class IconCardWidget extends BaseWidget
{
    public $trend = 0;
    public $previousValue = 0;

    public function mount($widgetId, $config, $initialData = null)
    {
        parent::mount($widgetId, $config, $initialData);
        $this->calculateTrend();
    }

    public function calculateTrend()
    {
        if (!isset($this->data['data'][0])) {
            $this->trend = 0;
            $this->previousValue = 0;
            return;
        }
        
        $currentValue = $this->data['data'][0] ?? 0;
        $previousValue = $this->getPreviousPeriodValue();
        $this->previousValue = $previousValue;
        
        if ($previousValue == 0) {
            $this->trend = $currentValue > 0 ? 100 : 0;
            return;
        }
        
        // Calculate percentage change
        $change = (($currentValue - $previousValue) / $previousValue) * 100;
        $this->trend = round($change, 1);
    }

    protected function getPreviousPeriodValue()
    {
        $cacheKey = "widget_trend:{$this->widgetId}:" . md5(json_encode($this->config));
        
        ///return Cache::remember($cacheKey, 300, function() { // Cache for 5 minutes
            $previousPeriod = $this->getPreviousPeriod($this->config['time_duration'] ?? 'this_month');
            
            if (!$previousPeriod) {
                return 0;
            }

            $aggregator = new Aggregator();
            
            if (isset($this->config['model'])) {
                $aggregator->setModel($this->config['model']);
            }
            
            $aggregator->setColumn($this->config['column'] ?? 'id')
                      ->setAggregationMethod($this->config['aggregation'] ?? 'count')
                      ->setFilters($this->config['filters'] ?? []);
            
            $timeRange = $this->getTimeRange($previousPeriod);
            if ($timeRange) {
                $aggregator->setTimeRange($timeRange['from'], $timeRange['to']);
            }
            
            if (isset($this->config['pivot'])) {
                $aggregator->setPivotJoin(
                    $this->config['pivot']['table'] ?? null,
                    $this->config['pivot']['model_column'] ?? null,
                    $this->config['pivot']['related_column'] ?? null,
                    $this->config['pivot']['model_type'] ?? null,
                    $this->config['pivot']['related_column_in'] ?? null
                );
            }
            
            try {
                $previousData = $aggregator->fetch();
                return $previousData['data'][0] ?? 0;
            } catch (\Exception $e) {
                \Log::error('Failed to fetch previous period data', [
                    'widget' => $this->widgetId,
                    'error' => $e->getMessage()
                ]);
                return 0;
            }
        ///});
    }

    protected function getPreviousPeriod($currentPeriod)
    {
        return match($currentPeriod) {
            'today' => 'yesterday',
            'yesterday' => 'today',
            'this_week' => 'last_week',
            'last_week' => 'this_week',
            'this_month' => 'last_month',
            'last_month' => 'this_month',
            'this_year' => 'last_year',
            'last_year' => 'this_year',
            default => null
        };
    }

    protected function getTimeRange($duration)
    {
        // Reuse the same method from your DashboardManager or create a helper
        switch ($duration) {
            case 'today':
                return [
                    'from' => now()->startOfDay()->toDateTimeString(),
                    'to' => now()->endOfDay()->toDateTimeString(),
                ];
            case 'yesterday':
                $yesterday = now()->copy()->subDay();
                return [
                    'from' => $yesterday->startOfDay()->toDateTimeString(),
                    'to' => $yesterday->endOfDay()->toDateTimeString(),
                ];
            case 'this_week':
                return [
                    'from' => now()->startOfWeek()->toDateTimeString(),
                    'to' => now()->endOfWeek()->toDateTimeString(),
                ];
            case 'last_week':
                $lastWeek = now()->copy()->subWeek();
                return [
                    'from' => $lastWeek->startOfWeek()->toDateTimeString(),
                    'to' => $lastWeek->endOfWeek()->toDateTimeString(),
                ];
            case 'this_month':
                return [
                    'from' => now()->startOfMonth()->toDateTimeString(),
                    'to' => now()->endOfMonth()->toDateTimeString(),
                ];
            case 'last_month':
                $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
                $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth();
                return [
                    'from' => $lastMonthStart->toDateTimeString(),
                    'to' => $lastMonthEnd->toDateTimeString(),
                ];
            case 'this_year':
                return [
                    'from' => now()->startOfYear()->toDateTimeString(),
                    'to' => now()->endOfYear()->toDateTimeString(),
                ];
            case 'last_year':
                $lastYear = now()->subYear()->startOfYear();
                return [
                    'from' => $lastYear->toDateTimeString(),
                    'to' => $lastYear->endOfYear()->toDateTimeString(),
                ];
            default:
                return null;
        }
    }

    public function onDataUpdated($dashboardData)
    {
        parent::onDataUpdated($dashboardData);
        $this->calculateTrend(); // Recalculate trend when data updates
    }

    public function render()
    {
        $value = $this->data['data'][0] ?? 0;

        $data = [
            'value' => $value,
            'trend' => $this->trend,
            'previousValue' => $this->previousValue,
            'isLoading' => $this->isLoading
        ];

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";

        return view("$layoutPath.cards.icon-card", $data)
            ->layout("$layoutPath.app");
    }
}