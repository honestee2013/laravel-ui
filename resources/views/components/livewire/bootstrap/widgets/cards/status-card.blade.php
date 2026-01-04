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
        
        // Determine if we should show badge based on count
        $shouldShowBadge = $showBadge && $count > 0 && !empty($badgeText);
    @endphp

    <div class="card w-100 border-0 {{ $gradientClass }} shadow-sm status-card" style="min-height: 140px;">
        <div class="card-body p-3">
            @if($isLoading)
                <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                    <div class="spinner-border text-white" role="status">
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
                                {{ $config['title'] ?? 'Status' }}
                            </p>
                            
                            {{-- Count --}}
                            <h1 class="font-weight-bolder mb-0 text-white">
                                {{ $count }}
                            </h1>
                            
                            {{-- Status text --}}
                            <p class="text-sm text-white opacity-8 mb-0">
                                {{ $statusText }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Right side: Icon --}}
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-white {{ $shadowClass }} text-center rounded-circle">
                            <i class="{{ $config['icon'] ?? 'fas fa-inbox' }} {{ $textColorClass }}" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
                
                {{-- Badge --}}
                @if($shouldShowBadge)
                <div class="mt-2">
                    <span class="badge bg-white {{ $textColorClass }} px-2 py-1">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        {{ $badgeText }}
                    </span>
                </div>
                @endif
                
                {{-- Action button --}}
                @if(isset($config['action']) && $config['action'])
                <div class="mt-3">
                    @if(isset($config['action']['type']) && $config['action']['type'] === 'button')
                        <button type="button" 
                                class="btn btn-sm btn-white w-100 {{ $count === 0 ? 'opacity-75' : '' }}"
                                wire:click="triggerAction"
                                @if($count === 0) disabled @endif>
                            <i class="{{ $config['action']['icon'] ?? 'fas fa-eye' }} me-1 {{ $textColorClass }}"></i>
                            {{ $config['action']['label'] ?? 'View' }}
                            @if($count > 0)
                                <span class="badge bg-{{ $color }} ms-1">{{ $count }}</span>
                            @endif
                        </button>
                    @elseif(isset($config['action']['url']) && $config['action']['url'])
                        <a href="{{ $config['action']['url'] }}" 
                        class="btn btn-sm btn-white w-100 {{ $count === 0 ? 'opacity-75' : '' }}"
                        @if($count === 0) style="pointer-events: none;" @endif>
                            <i class="{{ $config['action']['icon'] ?? 'fas fa-eye' }} me-1 {{ $textColorClass }}"></i>
                            {{ $config['action']['label'] ?? 'View' }}
                            @if($count > 0)
                                <span class="badge bg-{{ $color }} ms-1">{{ $count }}</span>
                            @endif
                        </a>
                    @else
                        <button type="button" 
                                class="btn btn-link text-xs text-white text-decoration-none p-0 border-0 bg-transparent d-flex align-items-center"
                                wire:click="triggerAction">
                            <i class="{{ $config['action']['icon'] ?? 'fas fa-arrow-right' }} me-1"></i>
                            {{ $config['action']['label'] ?? 'Take Action' }}
                        </button>
                    @endif
                </div>
                @endif
            @endif
        </div>
    </div>

</div>