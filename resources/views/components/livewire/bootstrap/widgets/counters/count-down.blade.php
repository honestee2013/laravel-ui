<div class="col-lg-3 col-6 text-center">
    <div class="border-dashed border-1 border-{{ $urgencyColor }} border-radius-md py-3">
        <h6 class="text-{{ $urgencyColor }} text-gradient mb-0">{{ $title }}</h6>
        <h4 class="font-weight-bolder mt-2">
            @if($isLoading)
                <i class="fas fa-spinner fa-spin text-primary"></i>
                <span class="text-sm text-muted">Loading...</span>
            @else
                @if($hasExpired && $showExpiredIcon)
                    <i class="fas fa-exclamation-triangle text-{{ $urgencyColor }} me-2"></i>
                @endif
                <span id="countdown-{{ $widgetId }}" wire:ignore>
                    {{ $formattedTime }}
                </span>
            @endif
        </h4>
        
        @if(!$isLoading && !$hasExpired && ($config['show_progress'] ?? false))
            <div class="mt-2">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-{{ $urgencyColor }}" 
                         style="width: {{ $remainingTime['total_seconds'] / ($config['total_duration'] ?? $remainingTime['total_seconds']) * 100 }}%">
                    </div>
                </div>
                <small class="text-muted">Time remaining</small>
            </div>
        @endif
    </div>
</div>

@script
<script>
document.addEventListener('livewire:initialized', function() {
    const widgetId = '{{ $widgetId }}';
    const endTime = {{ $endTime }};
    
    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const difference = endTime - now;
        
        const countdownElement = document.getElementById(`countdown-${widgetId}`);
        if (!countdownElement) return;
        
        if (difference <= 0) {
            countdownElement.textContent = '{{ $expiredMessage }}';
            // Notify Livewire that countdown expired
            @this.call('$refresh');
            return;
        }
        
        const days = Math.floor(difference / (60 * 60 * 24));
        const hours = Math.floor((difference % (60 * 60 * 24)) / (60 * 60));
        const minutes = Math.floor((difference % (60 * 60)) / 60);
        const seconds = difference % 60;
        
        // Format based on configuration
        const format = '{{ $config["format"] ?? "full" }}';
        let formattedTime;
        
        switch(format) {
            case 'compact':
                formattedTime = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                break;
            case 'days_only':
                formattedTime = `${days} days`;
                break;
            case 'hours_only':
                formattedTime = `${Math.floor(difference / (60 * 60))} hours`;
                break;
            default:
                formattedTime = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
        
        countdownElement.textContent = formattedTime;
    }
    
    // Update immediately and then every second
    updateCountdown();
    const interval = setInterval(updateCountdown, 1000);
    
    // Cleanup on component removal
    Livewire.on('component-removed', (component) => {
        if (component === widgetId) {
            clearInterval(interval);
        }
    });
});
</script>
@endscript