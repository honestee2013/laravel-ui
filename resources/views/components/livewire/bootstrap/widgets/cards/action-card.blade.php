<div>    
    @php
        // Map color to classes
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
        
        $buttonClassMap = [
            'filled' => [
                'primary' => 'btn-primary',
                'secondary' => 'btn-secondary',
                'success' => 'btn-success',
                'info' => 'btn-info',
                'warning' => 'btn-warning',
                'danger' => 'btn-danger',
                'light' => 'btn-light',
                'dark' => 'btn-dark',
            ],
            'outline' => [
                'primary' => 'btn-outline-primary',
                'secondary' => 'btn-outline-secondary',
                'success' => 'btn-outline-success',
                'info' => 'btn-outline-info',
                'warning' => 'btn-outline-warning',
                'danger' => 'btn-outline-danger',
                'light' => 'btn-outline-light',
                'dark' => 'btn-outline-dark',
            ],
            'white' => [
                'primary' => 'btn-white text-primary',
                'secondary' => 'btn-white text-secondary',
                'success' => 'btn-white text-success',
                'info' => 'btn-white text-info',
                'warning' => 'btn-white text-warning',
                'danger' => 'btn-white text-danger',
                'light' => 'btn-white text-light',
                'dark' => 'btn-white text-dark',
            ]
        ];
        
        $gradientClass = $gradientMap[$color] ?? 'bg-gradient-primary';
        $buttonClass = $buttonClassMap[$buttonVariant][$color] ?? 'btn-primary';
        
        // Button size classes
        $sizeClass = match($buttonSize) {
            'sm' => 'btn-sm',
            'lg' => 'btn-lg',
            default => ''
        };
        
        // Icon size based on button size
        $iconSize = match($buttonSize) {
            'sm' => 'fa-sm',
            'lg' => 'fa-lg',
            default => ''
        };
    @endphp

    <div class="card w-100 border-0 {{ $gradientClass }} shadow-sm action-card" style="min-height: 140px;">
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            @if($isLoading)
                <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                    <div class="spinner-border text-white" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            @else
                {{-- Title and description --}}
                <div>
                    <p class="text-sm mb-1 text-uppercase font-weight-bold text-white opacity-8">
                        {{ $config['title'] ?? 'Quick Action' }}
                    </p>
                    
                    <p class="text-white mb-3">
                        {{ $config['description'] ?? 'Perform this action with one click' }}
                    </p>
                </div>
                
                {{-- Action button --}}
                <div>
                    <button type="button" 
                            class="btn {{ $buttonClass }} {{ $sizeClass }} w-100 position-relative"
                            wire:click="triggerAction"
                            wire:loading.attr="disabled"
                            @if(isset($config['action']['disabled']) && $config['action']['disabled']) disabled @endif
                            title="{{ $config['action']['tooltip'] ?? $config['action']['label'] ?? 'Click to perform action' }}">
                        
                        {{-- Button content --}}
                        <span class="d-flex align-items-center justify-content-center">
                            {{-- Icon --}}
                            @if(isset($config['action']['icon']))
                                <i class="{{ $config['action']['icon'] }} {{ $iconSize }} me-2"></i>
                            @endif
                            
                            {{-- Label --}}
                            <span>
                                @if($isProcessing)
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    {{ $config['action']['processing_label'] ?? 'Processing...' }}
                                @else
                                    {{ $config['action']['label'] ?? 'Take Action' }}
                                @endif
                            </span>
                            
                            {{-- Badge if count provided --}}
                            @if(isset($config['action']['count']) && $config['action']['count'] > 0)
                                <span class="badge bg-white text-{{ $color }} ms-2">
                                    {{ $config['action']['count'] }}
                                </span>
                            @endif
                        </span>
                    </button>
                    
                    {{-- Action result feedback --}}
                    @if($actionResult)
                        <div class="mt-2">
                            <div class="alert alert-{{ $actionResult['type'] }} alert-dismissible fade show py-1 px-2 mb-0" role="alert">
                                <span class="d-flex align-items-center">
                                    @if($actionResult['type'] === 'success')
                                        <i class="fas fa-check-circle me-1"></i>
                                    @elseif($actionResult['type'] === 'error')
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                    @else
                                        <i class="fas fa-info-circle me-1"></i>
                                    @endif
                                    <small>{{ $actionResult['message'] }}</small>
                                </span>
                                <button type="button" class="btn-close p-1" 
                                        wire:click="clearResult"
                                        aria-label="Close">
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Helper text --}}
                    @if(isset($config['action']['helper']))
                        <p class="text-xs text-white opacity-7 mt-1 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ $config['action']['helper'] }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Optional: CSS for better styling --}}
    <style>
        .action-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px 0 rgba(0, 0, 0, 0.2) !important;
        }
        
        .action-card .alert {
            border-radius: 8px;
            font-size: 0.75rem;
            border: none;
        }
        
        .action-card .btn-white {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .action-card .btn-white:hover:not(:disabled) {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .action-card .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .action-card .badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
        }
    </style>

    {{-- JavaScript for browser events --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for new tab open event
            Livewire.on('openNewTab', (data) => {
                if (data.url) {
                    window.open(data.url, '_blank');
                }
            });
            
            // Auto-clear success messages
            Livewire.on('clearActionResult', (data) => {
                setTimeout(() => {
                    const widgetId = data.widgetId;
                    Livewire.find(widgetId).call('clearResult');
                }, 3000); // Clear after 3 seconds
            });
            
            // Handle confirmation dialog
            Livewire.on('showConfirmation', (data) => {
                if (confirm(data.message)) {
                    Livewire.find(data.widgetId).call('executeAction');
                }
            });
        });
    </script>
</div>

