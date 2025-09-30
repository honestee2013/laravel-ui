<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Charts;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\Analytics\Data\Dataset;

use App\Modules\Analytics\Data\Aggregator;
use App\Modules\Analytics\Helpers\DataGroupingHelper;
use App\Modules\Core\Traits\DataTable\DataTableFieldsConfigTrait;
use QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\AggregatorAbstractWidget;

class Chart extends AggregatorAbstractWidget
{

    // Chart related
    public $chartType = 'bar'; // Default chart type
    public $chartId; // Chart element ID
    public $chartData = []; // Chart data
    public $chartOptions = []; // Chart options
    public $canvasHeight = "auto";
    public $canvasWidth = "auto";
    public $controls = [];









    public function mount()
    {


        
        //dd($this->pivotTable, $this->pivotModelKey, $this->pivotRelatedKey, $this->pivotModelType);


        if (!isset($this->chartId))
            $this->chartId = 'chart-' . uniqid();

        $this->updateInfo();
    }


    public function updateInfo()
    {
        $this->updateChart();
    }




    private function setUpChartData($aggregationData)
    {

        $data = [];
        $labels = [];
        if (!empty($aggregationData["data"]))
            $data = $aggregationData["data"];
        if (!empty($aggregationData["labels"]))
            $labels = $aggregationData["labels"];

        // Aggregation data
        $this->setUpAggregationValues($data);
        // Increase/Decrease of the record
        $this->setUpChangedValue($data);



        $dataSets = [
            [
                'label' => $this->recordName,
                'data' => $data,
                'fill' => false,

                'backgroundColor' => 'rgba(54, 162, 235, 0.5)', // Semi-transparent blue
                'borderColor' => 'rgba(54, 162, 235, 1)',       // Solid blue

                'tension' => 0.1,
                'pointRadius' => 4,

                'borderWidth' => 2,
                'maxBarThickness' => 60,
                'borderRadius' => 10,
            ]
        ];

        return $this->chartData = [
            'labels' => $labels,
            'datasets' => $dataSets,
        ];

    }


    private function setUpchartOptions()
    {
        return $this->chartOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                    'grid' => [
                        'borderDash' => [5, 5],
                    ],
                ],
                'y' => [
                    'grid' => [
                        'borderDash' => [5, 5],
                    ],

                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ]
            ]
        ];
    }


    public function updateChart()
    {

        $aggregationData = $this->getAggregationData();
        //dd($aggregationData);
        $this->setUpChartData($aggregationData);


        $colors = 'rgba(54, 162, 235, 0.5)';

        // Generate dynamic colors for pie chart datasets
        if ($this->chartType == "pie" || $this->chartType == "doughnut") {
            $colors = $this->generateColors(count($this->chartData["datasets"][0]["data"]));
        }

        $this->chartData["datasets"][0]["backgroundColor"] = $colors;
        $this->chartData["datasets"][0]["hoverBackgroundColor"] = $colors;


        $data = [
            "chartType" => $this->chartType,
            "chartData" => $this->chartData,
            "chartOptions" => $this->chartOptions,
        ];
        $this->dispatch("update-chart-event-{$this->chartId}", $data);
    }



    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.charts.chart');
    }

}
