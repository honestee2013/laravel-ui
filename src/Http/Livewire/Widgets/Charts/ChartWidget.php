<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Charts;

use QuickerFaster\LaravelUI\Http\Livewire\Widgets\BaseWidget;

class ChartWidget extends BaseWidget
{
    public $chartType;
    public $chartId;
    public $aggregations = [];






    

    public function mount($widgetId, $config, $initialData = null)
    {
        parent::mount($widgetId, $config, $initialData);
        $this->chartType = $config['chart_type'] ?? 'bar';
        $this->chartId = 'chart-' . uniqid();
        $this->calculateAggregations();
    }

    public function updatedChartType($value)
    {
        $this->dispatch('updateChart', $this->chartId, [
            'chartType' => $value,
            'chartData' => $this->prepareChartData(),
            'chartOptions' => $this->prepareChartOptions()
        ]);
    }






    protected function calculateAggregations()
    {
        if (!$this->data || empty($this->data['data'])) {
            $this->aggregations = [
                'count' => 0,
                'sum' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0
            ];
            return;
        }

        $data = $this->data['data'] ?? [];

        $this->aggregations = [
            'count' => count($data),
            'sum' => array_sum($data),
            'average' => count($data) > 0 ? round(array_sum($data) / count($data), 2) : 0,
            'max' => max($data),
            'min' => min($data)
        ];
    }

protected function prepareChartData()
{
    
    // First try to use static data from config if available
    if (isset($this->config['static_data'])) {
        $dataValues = $this->config['static_data']['data'] ?? [0];
        $labels = $this->config['static_data']['labels'] ?? ['Static Data'];
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $this->config['title'] ?? 'Dataset',
                    'data' => $dataValues,
                    'backgroundColor' => $this->generateColors(count($dataValues)),
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    // Otherwise use the data from DashboardManager
    if (!$this->data || empty($this->data['data']) || empty($this->data['labels'])) {
        return $this->getEmptyChartData();
    }

    $dataValues = $this->data['data'] ?? [];
    $labels = $this->data['labels'] ?? [];

    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => $this->config['title'] ?? 'Dataset',
                'data' => $dataValues,
                'backgroundColor' => $this->generateColors(count($dataValues)),
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 2,

                'barPercentage' => 0.1,
                //'barThickness' => 6,
                //'maxBarThickness' => 8,
                'minBarLength' => 2,
            ]
        ]
    ];
}

protected function getEmptyChartData()
{
    return [
        'labels' => ['No Data'],
        'datasets' => [
            [
                'label' => $this->config['title'] ?? 'Dataset',
                'data' => [0],
                'backgroundColor' => $this->generateColors(1),
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 2
            ]
        ]
    ];
}

    protected function prepareChartOptions()
    {
        $baseOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ]
            ]
        ];

        // Only add scales for chart types that support them
        if (in_array($this->chartType, ['bar', 'line'])) {
            $baseOptions['scales'] = [
                'x' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true
                    ]
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true
                    ]
                ]
            ];
        }

        return $baseOptions;
    }

    protected function generateColors($count)
    {
        if ($count === 0) return ['rgba(200, 200, 200, 0.6)'];
        
        $palette = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#33FFF5', '#F5FF33'];
        $colors = [];
        
        for ($i = 0; $i < $count; $i++) {
            $baseColor = $palette[$i % count($palette)];
            $colors[] = $this->hexToRgba($baseColor, 0.6);
        }
        
        return $colors;
    }

    protected function hexToRgba($hex, $alpha)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    }

    public function onDataUpdated($dashboardData)
    {
        parent::onDataUpdated($dashboardData);
        $this->calculateAggregations();
        $this->dispatch('updateChart', $this->chartId, [
            'chartType' => $this->chartType,
            'chartData' => $this->prepareChartData(),
            'chartOptions' => $this->prepareChartOptions()
        ]);
    }

    public function render()
    {




        $chartData = $this->prepareChartData();
        $chartOptions = $this->prepareChartOptions();

        $data = [
            'chartId' => $this->chartId,
            'chartType' => $this->chartType,
            'chartData' => $chartData,
            'chartOptions' => $chartOptions,
            'controls' => $this->config['controls'] ?? [],
            'data' => $this->data,
            'aggregations' => $this->aggregations,
            'isLoading' => $this->isLoading
        ];

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";

        return view("$layoutPath.charts.chart", $data)
            ->layout("$layoutPath.app");
    }
}




