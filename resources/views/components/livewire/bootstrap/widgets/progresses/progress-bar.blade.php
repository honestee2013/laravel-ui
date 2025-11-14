<div class="my-3">
    <div class="progress-wrapper">
        @if($elementLabel)
            <div class="progress-info">
                <div class="progress-percentage">
                    <span class="text-sm font-weight-normal">{{ $elementLabel }}</span>
                    @if($showPercentage && !$isLoading)
                        <span class="text-sm text-muted ms-2">({{ round($progress, 1) }}%)</span>
                    @endif
                    @if($isLoading)
                        <span class="text-sm text-muted ms-2">
                            <i class="fas fa-spinner fa-spin"></i> Calculating...
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <div class="rounded-pill progress" style="height: auto">
            <div class="rounded-pill my-auto progress-bar bg-gradient-{{ $color }}" 
                 role="progressbar" 
                 style="width: {{ $progress }}%; {{ $progressBarCSS }};"
                 aria-valuenow="{{ $progress }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <span style="{{ $progressLabelCSS }}">
                    @if($isLoading)
                        <i class="fas fa-spinner fa-spin"></i>
                    @else
                        {{ $progressLabel }}
                    @endif
                </span>
            </div>
        </div>
        
        {{-- Additional stats if available --}}
        @if(!$isLoading && isset($config['show_stats']) && $config['show_stats'])
            <div class="mt-2 text-center">
                <small class="text-muted">
                    @if(isset($data['aggregations']))
                        @php
                            $aggs = $data['aggregations'];
                        @endphp
                        @if($aggs['count'] > 0)
                            <span class="me-2">Items: {{ $aggs['count'] }}</span>
                        @endif
                        @if($aggs['sum'] > 0)
                            <span class="me-2">Total: {{ $aggs['sum'] }}</span>
                        @endif
                    @endif
                </small>
            </div>
        @endif
    </div>
</div>