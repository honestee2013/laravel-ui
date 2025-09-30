<div class="col-lg-3 col-6 text-center" wire:key='{{$this->id}}'>
    <div class="border-dashed border-1 border-secondary border-radius-md py-3">
        <h6 class="text-primary text-gradient mb-0">Earnings</h6>
        <h4 class="font-weight-bolder">
            <span class="small">{{$prefix}} </span>
            <span id="count-up{{ $this->id }}" countTo="{{ $countTo }}"> </span> {{$suffix}}
        </h4>
    </div>
    <button wire:click='increase()'>Increase</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/countup@1.8.2/dist/countUp.min.js"></script>

@script
    <script>
        document.addEventListener('livewire:initialized', function() {

            options = {
                useEasing : true,
                useGrouping : {{$useGrouping}},
                separator : '{{$groupingSeparator}}',
                decimal : '.',
                prefix: '',
                suffix: ''
            };

            const elementId = 'count-up{{ $this->id }}';
            const countTo = document.getElementById(elementId).getAttribute('countTo');

            const countUp = new CountUp(elementId, 0, countTo, 0, 0, options);

            if (!countUp.error) {
                countUp.start();
            } else {
                console.error(countUp.error);
            }


            Livewire.on('update-count-up', function(event) {
                const elementId = 'count-up{{ $this->id }}';
                const newCountTo = document.getElementById(elementId).getAttribute('countTo');
                if (countUp) {
                    countUp.update(newCountTo);
                }
            });

        });
    </script>
@endscript
