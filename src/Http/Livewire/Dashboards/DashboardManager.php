<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboards;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

use QuickerFaster\LaravelUI\Services\Analytics\Aggregator;
use QuickerFaster\LaravelUI\Services\Analytics\BatchAggregator;

class DashboardManager extends Component
{
    public $moduleName;
    public $timeDuration = "this_month";
    public $filters = [];
    public $widgets = [];
    public $dashboardData = [];
    public $lastUpdated;
    public $isLoading = false;
    public $dashboardId;

    protected $listeners = [
        'timeDurationChanged' => 'updateTimeDuration',
        'customFilterChanged' => 'updateCustomFilters',
        'refreshDashboard' => 'refreshData'
    ];

    public function mount($moduleName)
    {
        $this->moduleName = $moduleName;
        $this->loadWidgetsConfiguration();
        $this->refreshData();
    }

protected function loadWidgetsConfiguration()
{
    $dashboardsPath = app_path("Modules/".ucfirst($this->moduleName). '/Data/dashboards');

    // If specific dashboard requested
    if ($this->dashboardId && $this->dashboardId !== 'default') {
        $specificConfigPath = "{$dashboardsPath}/{$this->dashboardId}.php";

        if (file_exists($specificConfigPath)) {
            $this->widgets = require $specificConfigPath;
            // dd($this->widgets);
            return;
        }
    }
    
    // Try default dashboard
    $defaultConfigPath = "{$dashboardsPath}/default.php";
    if (file_exists($defaultConfigPath)) {
        $this->widgets = require $defaultConfigPath;
        return;
    }
    
    // Fallback: load first available dashboard
    if (File::exists($dashboardsPath)) {
        $dashboardFiles = File::files($dashboardsPath);
        if (!empty($dashboardFiles)) {
            $firstDashboard = $dashboardFiles[0];
            $this->widgets = require $firstDashboard->getPathname();
            return;
        }
    }
    
    // Final fallback
    $this->widgets = ['widgets' => []];
}

    public function updateTimeDuration($duration)
    {
        $this->timeDuration = $duration;
        $this->refreshData();
    }

    public function updateCustomFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        $this->refreshData();
    }

    public function refreshData()
    {


        $this->isLoading = true;
        
        $cacheKey = $this->generateCacheKey();
        
        /*$this->dashboardData = Cache::remember($cacheKey, 300, function() {
            return $this->fetchDashboardData();
        });*/

        $this->dashboardData = $this->fetchDashboardData();
        
        $this->lastUpdated = now();
        $this->isLoading = false;
        
        $this->dispatch('dashboardDataUpdated', $this->dashboardData);
    }

    protected function fetchDashboardData()
    {
        $batchAggregator = new BatchAggregator();
        
        foreach ($this->widgets['widgets'] as $widgetId => $config) {
            $aggregator = $this->createAggregatorFromConfig($config);
            $batchAggregator->addQuery($widgetId, $aggregator);
        }

        return $batchAggregator->execute();
    }

    protected function createAggregatorFromConfig(array $config): Aggregator
    {
        $aggregator = new Aggregator();
        
        // Handle static data widgets FIRST
        if (isset($config['static_data'])) {
            $data = $config['static_data']['data'] ?? [0];
            $labels = $config['static_data']['labels'] ?? ['Static Data'];
            $aggregator->setStaticData($data, $labels);
            return $aggregator; // Return early for static data
        }
        
        // Handle database widgets
        if (isset($config['model'])) {
            $aggregator->setModel($config['model']);
        } elseif (isset($config['table'])) {
            $aggregator->setTable($config['table']);
        } else {
            // No model or table and no static data - return empty static data
            $aggregator->setStaticData([0], ['No Data']);
            return $aggregator;
        }
        
        $aggregator->setColumn($config['column'] ?? 'id')
                  ->setAggregationMethod($config['aggregation'] ?? 'count')
                  ->setFilters($config['filters'] ?? [])
                  ->groupBy($config['group_by'] ?? 'daily');
        
        // Apply time range
        $timeRange = $this->getTimeRange($this->timeDuration);
        if ($timeRange) {
            $aggregator->setTimeRange($timeRange['from'], $timeRange['to']);
        }
        // dd($config, $aggregator, $config['pivot']['related_column']);
        // Handle pivot configuration
    // Handle pivot configuration - with validation
    if (isset($config['pivot']) && 
        !empty($config['pivot']['table']) &&
        !empty($config['pivot']['model_column']) &&
        !empty($config['pivot']['related_column'])) {
        
        $aggregator->setPivotJoin(
            $config['pivot']['table'],
            $config['pivot']['model_column'],
            $config['pivot']['related_column'],
            $config['pivot']['model_type'] ?? null,
            $config['pivot']['related_column_in'] ?? null
        );
    }
        
        // Handle group by table
        if (isset($config['group_by_table'])) {
            $aggregator->setGroupByTable($config['group_by_table'])
                      ->setGroupByTableColumn($config['group_by_table_column'] ?? 'name');
        }
        
        return $aggregator;
    }

    protected function calculateAggregations($data)
    {
        if (empty($data)) {
            return [
                'count' => 0,
                'sum' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0
            ];
        }

        return [
            'count' => count($data),
            'sum' => array_sum($data),
            'average' => count($data) > 0 ? round(array_sum($data) / count($data), 2) : 0,
            'max' => max($data),
            'min' => min($data)
        ];
    }

    protected function getTimeRange($duration)
    {
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
                $startOfMonth = now()->startOfMonth();
                $endOfMonth = now()->endOfMonth();
                return [
                    'from' => $startOfMonth->toDateTimeString(),
                    'to' => $endOfMonth->toDateTimeString(),
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

    protected function generateCacheKey()
    {
        return "dashboard:{$this->moduleName}:" . md5(json_encode([
            'timeDuration' => $this->timeDuration,
            'filters' => $this->filters,
            'widgets' => array_keys($this->widgets['widgets'] ?? [])
        ]));
    }

    public function render()
    {
        $view = "{$this->moduleName}.views::dashboard-manager";
        if (!view()->exists($view))
            $view = "system.views::dashboard-manager";

        return view($view, [
            'widgetsConfig' => $this->widgets['widgets'] ?? [],
            'dashboardData' => $this->dashboardData,
            'isLoading' => $this->isLoading
        ]);
    }
}