

<div class="card z-index-2">
    <div class="card-header pb-0">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">{{ $config['title'] ?? 'Chart' }}</h6>
                @if($isLoading)
                    <p class="text-sm text-muted mb-0">
                        <i class="fas fa-spinner fa-spin"></i> Updating chart...
                    </p>
                @else
                    <p class="text-sm text-primary mb-0">
                        {{ $config['description'] ?? 'Data visualization' }}
                    </p>
                @endif
            </div>
            
            {{-- Chart Controls --}}
            @if(!empty($controls))
                <div class="d-flex flex-wrap gap-2">
                    @if(in_array('chart_type', $controls))
                        <select wire:model.live="chartType" 
                                class="form-select form-select-sm" 
                                style="width: auto;"
                                {{ $isLoading ? 'disabled' : '' }}>
                            <option value="bar">Bar</option>
                            <option value="line">Line</option>
                            <option value="pie">Pie</option>
                            <option value="doughnut">Doughnut</option>
                            <option value="polarArea">Polar Area</option>
                            <option value="radar">Radar</option>
                        </select>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card-body p-3">
        <div class="chart-container" style="position: relative; height: {{ $config['height'] ?? 300 }}px;">
            @if($isLoading)
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <p class="text-muted">Loading chart data...</p>
                    </div>
                </div>
            @else
                <canvas id="{{ $chartId }}" 
                        wire:ignore 
                        style="width: 100%; height: 100%;"></canvas>
            @endif
        </div>
        
        {{-- Aggregation Stats --}}
        @if(!$isLoading && !empty($aggregations))
            <div class="mt-3 text-center">
                @php
                    $showStats = array_filter([
                        'count' => $aggregations['count'] > 0,
                        'sum' => $aggregations['sum'] > 0,
                        'average' => $aggregations['average'] > 0,
                        'max' => $aggregations['max'] > 0 && $aggregations['max'] != $aggregations['min'],
                        'min' => $aggregations['min'] > 0 && $aggregations['max'] != $aggregations['min']
                    ]);
                @endphp
                
                @if(count($showStats) > 0)
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @if($showStats['count'] ?? false)
                            <span class="badge bg-info" title="Number of data points">
                                <i class="fas fa-hashtag me-1"></i>Count: {{ number_format($aggregations['count']) }}
                            </span>
                        @endif
                        
                        @if($showStats['sum'] ?? false)
                            <span class="badge bg-success" title="Total sum of all values">
                                <i class="fas fa-calculator me-1"></i>Total: {{ number_format($aggregations['sum']) }}
                            </span>
                        @endif
                        
                        @if($showStats['average'] ?? false)
                            <span class="badge bg-primary" title="Average value">
                                <i class="fas fa-chart-line me-1"></i>Avg: {{ number_format($aggregations['average'], 2) }}
                            </span>
                        @endif
                        
                        @if($showStats['max'] ?? false)
                            <span class="badge bg-warning text-dark" title="Maximum value">
                                <i class="fas fa-arrow-up me-1"></i>Max: {{ number_format($aggregations['max']) }}
                            </span>
                        @endif
                        
                        @if($showStats['min'] ?? false)
                            <span class="badge bg-secondary" title="Minimum value">
                                <i class="fas fa-arrow-down me-1"></i>Min: {{ number_format($aggregations['min']) }}
                            </span>
                        @endif
                    </div>
                    
                    {{-- Data Summary --}}
                    @if($aggregations['count'] > 0)
                        <div class="mt-2">
                            <small class="text-muted">
                                Showing {{ $aggregations['count'] }} data point{{ $aggregations['count'] > 1 ? 's' : '' }}
                                @if($aggregations['sum'] > 0)
                                    with total of {{ number_format($aggregations['sum']) }}
                                @endif
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-muted">
                        <small>No data available for the selected period</small>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>


@script
<script>
document.addEventListener('livewire:initialized', function() {
    const chartId = '{{ $chartId }}';
    let chartInstance = null;

    function initializeChart(chartType, chartData, chartOptions) {
        const ctx = document.getElementById(chartId);
        
        if (!ctx) {
            console.warn('Chart canvas not found:', chartId);
            return;
        }

        // Destroy existing chart instance if it exists
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        console.log('Initializing chart:', { chartType, chartData, chartOptions });

        // Create a new chart instance
        try {
            chartInstance = new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: chartOptions,
            });
            console.log('Chart initialized successfully');
        } catch (error) {
            console.error('Chart initialization failed:', error);
        }
    }

    // Initialize chart on page load if data is available
    @if(isset($chartData) && isset($chartOptions) && !$isLoading)
        console.log('Initial chart data:', @js($chartData));
        initializeChart(@js($chartType), @js($chartData), @js($chartOptions));
    @else
        console.log('No chart data available for initialization');
    @endif

    // Listen for updates from Livewire
    Livewire.on('updateChart', (event) => {
        console.log('Chart update received:', event);
        if (event[0] === chartId) {
            const config = event[1];
            initializeChart(config.chartType, config.chartData, config.chartOptions);
        }
    });
});
</script>
@endscript