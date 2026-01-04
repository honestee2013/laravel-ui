<div>
    @php
        // Map color to gradient classes
        $gradientMap = [
            'primary' => 'bg-gradient-primary',
            'secondary' => 'bg-gradient-secondary',
            'success' => 'bg-gradient-success',
            'info' => 'bg-gradient-info',
            'warning' => 'bg-gradient-warning',
            'danger' => 'bg-gradient-danger',
            'light' => 'bg-gradient-light',
            'dark' => 'bg-gradient-dark',
        ];
        
        $shadowMap = [
            'primary' => 'shadow-primary',
            'secondary' => 'shadow-secondary',
            'success' => 'shadow-success',
            'info' => 'shadow-info',
            'warning' => 'shadow-warning',
            'danger' => 'shadow-danger',
            'light' => 'shadow-light',
            'dark' => 'shadow-dark',
        ];
        
        $gradientClass = $gradientMap[$color] ?? 'bg-gradient-primary';
        $shadowClass = $shadowMap[$color] ?? 'shadow-primary';
        $textColorClass = "text-{$color}";

        
    @endphp

    <div class="card w-100 border-0 {{ $gradientClass }} shadow-sm" style="min-height: 140px;">
        <div class="card-body p-3">
            @if($isLoading)
                <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                    <div class="spinner-border text-{{ $color }}" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            @else
                <div class="row">
                    {{-- Left side: Text content --}}
                    <div class="col-8">
                        <div class="numbers">
                            {{-- Title --}}
                            <p class="text-sm mb-1 text-uppercase font-weight-bold text-white opacity-8">
                                {{ $config['title'] ?? 'Balance' }}
                            </p>
                            
                            {{-- Current/Total values --}}
                            <h3 class="font-weight-bolder mb-0 text-white">
                                {{ number_format($current, 0) }}
                                <span class="text-sm font-weight-normal opacity-8">
                                    / {{ number_format($total, 0) }} {{ $unit }}
                                </span>
                            </h3>
                            
                            {{-- Remaining text --}}
                            @php
                                $remaining = $total - $current;
                                $remainingText = $remaining >= 0 
                                    ? number_format($remaining, 0) . " {$unit} remaining" 
                                    : "Over by " . number_format(abs($remaining), 0) . " {$unit}";
                            @endphp
                            <p class="text-xs text-white opacity-8 mb-0">
                                {{ $remainingText }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Right side: Icon --}}
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-white {{ $shadowClass }} text-center rounded-circle">
                            <i class="{{ $config['icon'] ?? 'fas fa-percentage' }} {{ $textColorClass }}" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
                
                {{-- Progress bar --}}
                @if($total > 0)
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-white" 
                            role="progressbar" 
                            style="width: {{ min($percentage, 100) }}%;"
                            aria-valuenow="{{ $percentage }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-xs text-white opacity-8">0</span>
                        <span class="text-xs text-white opacity-8">{{ number_format($total, 0) }}</span>
                    </div>
                </div>
                @endif
                
                {{-- Action link --}}
                @if(isset($config['action']) && $config['action'])
                <div class="mt-3">
                    @if(isset($config['action']['url']) && $config['action']['url'])
                        <a href="{{ $config['action']['url'] }}" 
                        class="text-xs text-white text-decoration-none d-flex align-items-center"
                        @if(isset($config['action']['target']) && $config['action']['target'] === '_blank') target="_blank" @endif>
                            <i class="{{ $config['action']['icon'] ?? 'fas fa-arrow-right' }} me-1"></i>
                            {{ $config['action']['label'] ?? 'View Details' }}
                        </a>
                    @elseif(isset($config['action']['event']) && $config['action']['event'])
                        <button type="button" 
                                class="btn btn-link text-xs text-white text-decoration-none p-0 border-0 bg-transparent"
                                wire:click="$dispatch('{{ $config['action']['event'] }}')">
                            <i class="{{ $config['action']['icon'] ?? 'fas fa-arrow-right' }} me-1"></i>
                            {{ $config['action']['label'] ?? 'View Details' }}
                        </button>
                    @endif
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Optional: CSS for better styling --}}


</div>  