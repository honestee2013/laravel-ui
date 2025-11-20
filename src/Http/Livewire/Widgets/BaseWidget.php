<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets;

use Livewire\Component;

class BaseWidget extends Component
{
    public $widgetId;
    public $config;
    public $data;
    public $isLoading = false;

    protected $listeners = [
        'dashboardDataUpdated' => 'onDataUpdated',
        'refreshWidget' => 'refresh'
    ];

    public function mount($widgetId, $config, $initialData = null)
    {
        $this->widgetId = $widgetId;
        $this->config = $config;
        $this->data = $initialData ?? $this->getDefaultData();

    }

    protected function getDefaultData()
    {
        return [
            'data' => [0],
            'labels' => ['No Data'],
            'aggregations' => [
                'count' => 0,
                'sum' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0
            ]
        ];
    }

    public function onDataUpdated($dashboardData)
    {
        
        if (isset($dashboardData[$this->widgetId])) {
            $this->data = $dashboardData[$this->widgetId];
            $this->isLoading = false;
            $this->dispatch('$refresh');
        }
    }

    public function refresh()
    {
        $this->isLoading = true;
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view("dashboard.views::components.widgets.{$this->config['type']}");
    }
}