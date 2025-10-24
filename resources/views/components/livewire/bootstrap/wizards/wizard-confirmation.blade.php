{{-- resources/views/components/wizard-confirmation.blade.php --}}
@props(['confirmationData', 'currentStep'])


<div class="confirmation-container">
    <div class="alert alert-primary text-white">
        <h4 class="alert-heading ">Please review your information</h4>
        <p>Make sure all the information below is correct before completing the wizard.</p>
    </div>

    @foreach($confirmationData as $stepTitle => $fields)

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ $stepTitle }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($fields as $label => $value)
                        <div class="col-md-6 mb-3">
                            <strong>{{ $label }}:</strong>
                            <div class="text-muted">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

</div>

<style>
.confirmation-container .card {
    border-left: 4px solid var(--bs-primary);
}
.confirmation-container .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
</style>