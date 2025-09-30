<div class="col-lg-3 col-6 text-center">
    <div class="border-dashed border-1 border-secondary border-radius-md py-3">
        <h6 class="text-primary text-gradient mb-0">Time Remaining</h6>
        <h4 class="font-weight-bolder" id="countdown{{ $this->id }}"></h4>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/countdown@2.6.0/countdown.min.js"></script>

@script
    <script>
        document.addEventListener('livewire:initialized', function() {


        const elementId = 'countdown{{ $this->id }}';
        const endTime = {{ $endTime }}; // Unix timestamp for the end time

        function updateCountdown() {
            const now = new Date().getTime();
            const difference = endTime * 1000 - now; // Convert seconds to milliseconds

            if (difference > 0) {
                const remaining = countdown(new Date(endTime * 1000));
                document.getElementById(elementId).textContent =
                    `${remaining.years}years ${remaining.months}months  ${remaining.days}days ${remaining.hours}hours ${remaining.minutes}min ${remaining.seconds}sec`;
            } else {
                document.getElementById(elementId).textContent = "Time's up!";
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);


        });
    </script>
@endscript
