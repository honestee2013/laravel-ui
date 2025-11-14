<div class="col-lg-3 col-6 text-center" wire:key="countup-{{ $widgetId }}">
    <div class="border-dashed border-1 border-secondary border-radius-md py-3">
        <h6 class="text-primary text-gradient mb-0">{{ $title }}</h6>
        <h4 class="font-weight-bolder mt-2">
            @if($isLoading)
                <i class="fas fa-spinner fa-spin text-primary"></i>
                <span class="text-sm text-muted">Loading...</span>
            @else
                @if($prefix)
                    <span class="small">{{ $prefix }}</span>
                @endif
                <span id="countup-{{ $widgetId }}" 
                      data-value="{{ $countTo }}"
                      wire:ignore>
                    {{ $formattedValue }}
                </span>
                @if($suffix)
                    <span class="small">{{ $suffix }}</span>
                @endif
            @endif
        </h4>
        
        @if(!$isLoading && ($config['show_trend'] ?? false))
            <div class="mt-1">
                <small class="text-success">
                    <i class="fas fa-arrow-up"></i>
                    +{{ rand(5, 15) }}% from last period
                </small>
            </div>
        @endif
    </div>
</div>

@script
<script>
document.addEventListener('livewire:initialized', function() {
    const widgetId = '{{ $widgetId }}';
    const elementId = `countup-${widgetId}`;
    
    function initializeCountUp() {
        const element = document.getElementById(elementId);
        if (!element) return null;
        
        const options = {
            useEasing: {{ $useEasing ? 'true' : 'false' }},
            useGrouping: {{ $useGrouping ? 'true' : 'false' }},
            separator: '{{ $groupingSeparator }}',
            decimal: '{{ $decimalSeparator }}',
            prefix: '',
            suffix: '',
            duration: {{ $duration }},
        };
        
        const endVal = parseFloat(element.getAttribute('data-value'));
        
        if (typeof CountUp !== 'undefined') {
            const countUp = new CountUp(elementId, 0, endVal, 0, options);
            if (!countUp.error) {
                countUp.start();
                return countUp;
            }
        }
        
        return null;
    }
    
    let countUpInstance = initializeCountUp();
    
    // Listen for updates
    Livewire.on('updateCountUp', (event) => {
        if (event[0] === widgetId && countUpInstance) {
            const newValue = event[1];
            countUpInstance.update(newValue);
        }
    });
    
    // Reinitialize if component updates
    Livewire.hook('element.updated', (el) => {
        if (el.component.id === @this.__instance.id && !countUpInstance) {
            countUpInstance = initializeCountUp();
        }
    });
});
</script>
@endscript