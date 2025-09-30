<div class="card z-index-2">
    <div class="card-header pb-0">


        <div class="d-flex justify-content-between">
            <div >
                @if(!$this->title)
                    <h6>{{ str_contains($this->groupBy, "_id")? '': ucfirst($this->groupBy) }} {{$recordName}} <span
                            class="text-primary text-xs fst-italic">{{ $timeDuration !== 'custom' ? ucfirst(str_replace('_', ' ', $timeDuration)) : $fromTime . ' to ' . $toTime }}</span>
                    </h6>
                @else
                    <h6>{{ $this->title }} <span
                            class="text-primary text-xs fst-italic">{{ $timeDuration !== 'custom' ? ucfirst(str_replace('_', ' ', $timeDuration)) : $fromTime . ' to ' . $toTime }}</span>
                    </h6>
                @endif
                <p class="text-sm">
                    @if($this->valueChange > 0 && $timeDuration != 'custom')
                        <i class="fa fa-arrow-up text-success"></i>
                        <span class="font-weight-bold text-success">{{ abs($valueChangePercent) }}% </span><span class="font-weight-bold"> more </span> {{ str_contains($valueChangeTimeDuration, "today")? 'for': 'in'}}  {{str_replace("_", " ",$valueChangeTimeDuration)}}
                    @elseif($this->valueChange < 0 && $timeDuration != 'custom')
                        <i class="fa fa-arrow-down text-danger"></i>
                        <span class="font-weight-bold text-danger">{{ abs($valueChangePercent) }}% </span><span class="font-weight-bold"> less </span> {{ str_contains($valueChangeTimeDuration, "today")? 'for': 'in'}}  {{str_replace("_", " ",$valueChangeTimeDuration)}}
                    @endif
                </p>
            </div>
            <div >
                @if(is_array($controls) && !empty($controls))
                    <div class="row mb-4">
                        @if(array_search('chartType', $controls) !== false)
                            <div class="input-group col-12 w-100 col-sm-auto w-sm-auto mt-2">
                                <select id="roleSelect" x-on:change="$wire.updateChart()" wire:model.live.500ms="chartType"
                                    class="form-select rounded-pill p-1 ps-3 px-sm-3 m-0 small-control">
                                    <option value="" disabled>Select chart...</option>
                                    <option value="bar">Bar chart</option>
                                    <option value="line">Line chart</option>
                                    <option value="pie">Pie chart</option>
                                    <option value="doughnut">Doughnut chart</option>
                                    <option value="polarArea">Polar Area chart</option>
                                    <option value="radar">Radar chart</option>
                                </select>
                            </div>
                        @endif
                        @if(array_search('groupBy', $controls) !== false)
                            <div class="input-group col-12 w-100 col-sm-auto w-sm-auto">
                                <select id="groupBy" wire:model.live.500ms="groupBy"
                                    class="form-select rounded-pill p-1 ps-3  px-sm-3 m-0 mt-2 small-control">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        @endif
                        @if(array_search('timeDuration', $controls) !== false)
                            <!--<div class="input-group col-8  col-sm-auto w-sm-auto border border-light rounded-pill p-1" >-->
                            <div class="input-group  w-90 col-sm-auto w-sm-auto border border-light rounded-pill p-3 p-sm-0 ms-3 mt-2">

                                <select wire:model.live.500ms="timeDuration" id="time_duration"
                                    class="form-select  rounded-pill p-1 pt-0 ps-3  px-sm-3 m-1 small-control">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                    <option value="custom">Custom Range...</option>
                                </select>

                                <div class="input-group col-12 w-100 col-sm-auto w-sm-auto" x-show="$wire.timeDuration === 'custom'">
                                    <span for="from_time" class=" ms-3 me-1 mt-1 text-sm ">From:</span>
                                    <input type="date" wire:model.live.500ms="fromTime" id="from_time"
                                        class="form-control rounded-pill  ps-3  px-sm-3 m-1 " style="height: 2em;">
                                    @error('fromTime')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                    <span for="to_time" class=" ms-3 me-1 mt-1 text-sm">To:</span>
                                    <input type="date" wire:model.live.500ms="toTime" id="to_time"
                                        class="form-control rounded-pill ps-3  px-sm-3 m-1" style="height: 2em;">
                                    @error('toTime')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>











    </div> <!--- Card Header Ends --->









    <div class="card-body p-3">
        <div class="chart text-bold">
            @if (isset($chartData['labels']) && !empty($chartData['labels']))
                <div class="ms-2 mb-3 text-center">

                    @if ($showSum)
                        <span class="ms-3 text-xxs badge bg-gradient-info rounded-pill pt-1" style="height: 1.8em;">Total: {{ $sum }}</span>
                    @endif
                    @if ($showCount)
                        <span class="ms-3 text-xxs badge bg-gradient-success rounded-pill pt-1" style="height: 1.8em">Count: {{ $count }}</span>
                    @endif
                    @if ($showAve)
                        <span class="ms-3 text-xxs badge bg-gradient-secondary rounded-pill pt-1" style="height: 1.8em">Ave: {{ $ave }}</span>
                    @endif
                    @if ($showMax)
                        <span class="ms-3 text-xxs badge bg-gradient-primary rounded-pill pt-1" style="height: 1.8em">Max: {{ $max }}</span>
                    @endif
                    @if ($showMin)
                        <span class="ms-3 text-xxs badge bg-gradient-danger rounded-pill pt-1" style="height: 1.8em">Min: {{ $min }}</span>
                    @endif

                </div>
            @endif
            <canvas id="{{ $chartId }}" class="chart-canvas" wire:ignore height="{{$canvasHeight}}" width="{{$canvasWidth}}"></canvas>
        </div>
    </div>

</div>











@script
    <script>
        document.addEventListener('livewire:initialized', function() {
            const chartId = '{{ $chartId }}';
            let chartInstance = null;

            function initializeChart(chartType, chartData, chartOptions) {
                const ctx = document.getElementById(chartId).getContext('2d');


                // Destroy existing chart instance if it exists
                if (chartInstance) {
                    chartInstance.destroy();
                }

                // Create a new chart instance
                chartInstance = new Chart(ctx, {
                    type: chartType,
                    data: chartData,
                    options: chartOptions,
                });
            }

            // Watch for Livewire property updates
            Livewire.on(`update-chart-event-${chartId}`, (event) => {
                const chartType = event[0]["chartType"];
                const chartData = event[0]["chartData"];
                const chartOptions = event[0]["chartOptions"];

                initializeChart(chartType, chartData, chartOptions);
            });

            // Initialize chart on page load
            initializeChart(@json($chartType), @json($chartData),
                @json($chartOptions));
        });
    </script>
@endscript
