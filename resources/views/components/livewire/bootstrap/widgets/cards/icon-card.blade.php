<div class="card w-100">
    <div class="card-body p-3">
        <div class="row">
            <div class="col-8">
                <div class="numbers">
                    <p class="text-sm mb-0 text-capitalize font-weight-bold">
                        {{ $config['title'] ?? 'Untitled Widget' }}
                    </p>

                    <h5 class="font-weight-bolder mb-0">
                        @if($isLoading)
                            <i class="fas fa-spinner fa-spin text-primary"></i>
                            <span class="text-sm text-muted">Loading...</span>
                        @else
                            {{ $value ?? 0 }}
                            
                            {{-- Trend indicator with tooltip --}}
                            @if($trend != 0)
                                @if($trend > 0)
                                    <span class="text-success text-sm font-weight-bolder" 
                                          title="Increased by {{ abs($trend) }}% from previous period">
                                        <i class="fas fa-arrow-up"></i> +{{ abs($trend) }}%
                                    </span>
                                @else
                                    <span class="text-danger text-sm font-weight-bolder"
                                          title="Decreased by {{ abs($trend) }}% from previous period">
                                        <i class="fas fa-arrow-down"></i> -{{ abs($trend) }}%
                                    </span>
                                @endif
                            @else
                                <span class="text-muted text-sm" title="No change from previous period">
                                    ±0%
                                </span>
                            @endif
                        @endif
                    </h5>
                    
                    {{-- Previous period comparison --}}
                    @if(!$isLoading && isset($previousValue) && $previousValue > 0)
                        <p class="text-xs text-muted mb-0 mt-1">
                            Previous: {{ $previousValue }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                    <i class="{{ $config['icon'] ?? 'fas fa-chart-bar' }}" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>