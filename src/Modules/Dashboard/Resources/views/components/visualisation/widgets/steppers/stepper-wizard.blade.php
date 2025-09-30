<div>
    <!-- Step Indicators -->
    <div class="d-flex justify-content-start  mb-4 w-md-90 ms-md-5" >
        @foreach ($steps as $index => $step)
            <!-- Step Circle -->
            <div class="text-center">
                <div class="rounded-circle p-2 {{ $currentStep == $index ? 'bg-primary text-white' : 'bg-secondary text-light' }}"
                     style="width: 40px; height: 40px; line-height: 30px;">
                    {{ $index + 1 }}
                </div>
                <small>{{ $step['label'] ?? 'Step ' . ($index + 1) }}</small>
            </div>

             <!-- Line (Skip for the last step) -->
             @if ($index < count($steps) - 1)
                <div class="flex-grow-1 mx-2 mb-4 align-self-center "
                    style="height: 2px; background-color: {{ $currentStep > $index ? '#0d6efd' : '#6c757d' }};">
                </div>
            @endif
        @endforeach
    </div>

    <!-- Step Content -->
    <div class="border p-4 mb-4">
        <h5>{{ $steps[$currentStep]['label'] ?? 'Step ' . ($currentStep + 1) }}</h5>
        <div>
            {!! $steps[$currentStep]['content'] ?? 'Content for Step ' . ($currentStep + 1) !!}
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between">
        <button class="btn btn-secondary" wire:click="goToPreviousStep" {{ $currentStep == 0 ? 'disabled' : '' }}>Previous</button>
        <button class="btn btn-primary" wire:click="goToNextStep" {{ $currentStep == count($steps) - 1 ? 'disabled' : '' }}>Next</button>
    </div>
</div>
