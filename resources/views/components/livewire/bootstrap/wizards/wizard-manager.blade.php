{{-- resources/views/livewire/wizard-manager.blade.php --}}
<div class="wizard-container">


    @if ($isCompleted)
        <!-- ... completion view ... -->
        <x-qf::livewire.bootstrap.wizards.wizard-step-completion :isCompleted="$isCompleted" :wizardConfig="$wizardConfig" />
    @else
        <div class="card">
            <div class="card-header">
                <h3>{{ $wizardConfig['title'] ?? 'Wizard' }}</h3>
                <p class="text-muted">{{ $wizardConfig['description'] ?? '' }}</p>
            </div>

            <div class="card-body">
<!-- Above your stepper -->
<div class="text-center mb-3">
    <small class="text-muted">
        Step {{ $currentStep + 1 }} of {{ count($wizardConfig['steps']) }}
        <span class="ms-2">({{ round((($currentStep + 1) / count($wizardConfig['steps'])) * 100) }}% complete)</span>
    </small>
</div>


                <!-- Progress Steps -->
<!-- Progress Steps -->
<!-- Progress Steps -->
<div class="wizard-steps active-line" 
     style="--active-width: {{ round((($currentStep + 1) / count($wizardConfig['steps'])) * 100) }}%;">
    @foreach ($wizardConfig['steps'] as $index => $step)
        @php
            $isClickable = $index < $currentStep;
            $stepClasses = 'wizard-step ' . 
                          ($index <= $currentStep ? 'active ' : '') . 
                          ($index < $currentStep ? 'completed clickable' : '');
        @endphp
        
        <div class="{{ $stepClasses }}"
             @if($isClickable) wire:click="goToStep({{ $index }})" @endif>
            <div class="step-content">
                <span class="step-number">{{ $index + 1 }}</span>
                <span class="step-title">{{ $step['title'] }}</span>
            </div>
        </div>
    @endforeach
</div>



@if ($errors->any())
    <div class="alert alert-primary border border-danger rounded-3 shadow-sm mb-4 mx-4" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3 mt-1">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="fas fa-exclamation"></i>
                </div>
            </div>
            <div>
                <h6 class="text-white mb-2 fw-bold">Please correct the following:</h6>
                <ul class="mb-0 ps-3 text-white" style="font-size: 0.9rem; line-height: 1.5;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif


<!-- Current Step Form (REUSING YOUR EXISTING COMPONENTS) -->

@if (!$isConfirmPage)
    @php
        $currentStepConfig = $wizardConfig['steps'][$currentStep];
        if (!empty($currentStepConfig['model'])) {
            // ✅ Get ONLY fields allowed for this wizard
            $filteredFieldDefinitions = $this->getFilteredFieldDefinitionsForStep($currentStep);
            
            // ✅ Build filtered field groups (only groups with visible fields)
            $modelConfig = $this->wizardConfigService->loadModelConfig($currentStepConfig['model']);
            $filteredFieldGroups = [];
            if (!empty($currentStepConfig['groups'])) {
                foreach ($currentStepConfig['groups'] as $groupId) {
                    if (isset($modelConfig['fieldGroups'][$groupId])) {
                        $group = $modelConfig['fieldGroups'][$groupId];
                        // Keep only fields that are in filtered definitions
                        $group['fields'] = array_filter($group['fields'], function ($field) use ($filteredFieldDefinitions) {
                            return isset($filteredFieldDefinitions[$field]);
                        });
                        if (!empty($group['fields'])) {
                            $filteredFieldGroups[] = $group;
                        }
                    }
                }
            }

            $model = $currentStepConfig['model'];
            $modelName = class_basename($model);
            $formData = $this->formData[$model] ?? [];
        } else {
            $filteredFieldGroups = [];
            $filteredFieldDefinitions = [];
            $model = null;
            $modelName = null;
            $formData = [];
        }
    @endphp

    @if(!empty($currentStepConfig['model']))
        <x-qf::livewire.bootstrap.wizards.wizard-step-form 
            :fieldGroups="$filteredFieldGroups" 
            :fieldDefinitions="$filteredFieldDefinitions"
            :formData="$formData" 
            :model="$model" 
            :modelName="$modelName" 
            :multiSelectFormFields="[]" 
            :singleSelectFormFields="[]"
            :readOnlyFields="[]" 
            :wire:key="'step-' . $currentStep" 
            :currentModelAlias="$currentModelAlias" 

            :linkUserFieldValue="$linkUserFieldValue"
            :linkDatabaseFieldValue="$linkDatabaseFieldValue"
            :linkUserFieldName="$linkUserFieldName"
            :linkDatabaseFieldName="$linkDatabaseFieldName"
            :currentStepConfig="$currentStepConfig"
        />
    @else
        <div class="alert alert-info">
            <p>This step doesn't require any input.</p>
        </div>
    @endif
@else
    <x-qf::livewire.bootstrap.wizards.wizard-confirmation 
        :confirmation-data="$this->getConfirmationData()" />
@endif



                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" wire:click="prevStep"
                        @if ($currentStep === 0) disabled @endif>
                        ← Previous
                    </button>

                    <button type="button" class="btn btn-primary" wire:click="nextStep">
                        @if ($isConfirmPage)
                            Confirm & Complete Wizard
                        @elseif($currentStep === count($wizardConfig['steps']) - 2)
                            Review & Confirm →
                        @else
                            Next Step →
                        @endif
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.wizard-container .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.wizard-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
    padding: 0 20px;
}

.wizard-step {
    text-align: center;
    flex: 1;
    position: relative;
    min-width: 120px;
    z-index: 1;
}

/* Progress line */
.wizard-steps::before {
    content: '';
    position: absolute;
    top: 16px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

/* Active progress line */
.wizard-steps.active-line::before {
    background: linear-gradient(to right, 
        var(--bs-primary) 0%, 
        var(--bs-primary) var(--active-width, 0%), 
        #e9ecef var(--active-width, 0%), 
        #e9ecef 100%);
}

.wizard-step .step-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    font-weight: 600;
    font-size: 14px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.wizard-step.active .step-number {
    background: var(--bs-primary);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 0 0 4px rgba(205, 69, 159, 0.2);
    
}

.wizard-step.completed .step-number {
    background: var(--bs-secondary);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.2);
}

.step-title {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-align: center;
    line-height: 1.2;
    max-width: 100px;
}

.wizard-step.active .step-title,
.wizard-step.completed .step-title {
    color: #495057;
    font-weight: 700;
}

/* Responsive design */
@media (max-width: 768px) {
    .wizard-steps {
        padding: 0 10px;
    }
    
    .wizard-step {
        min-width: 80px;
    }
    
    .step-title {
        font-size: 10px;
        max-width: 70px;
    }
    
    .step-number {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* Hover effects */
.wizard-step:not(.completed):not(.active):hover .step-number {
    background: #dee2e6;
    cursor: pointer;
}

/* Clickable steps (optional) */
.wizard-step.clickable {
    cursor: pointer;
}

.wizard-step.clickable:hover .step-title {
    color: var(--bs-primary);
}
</style>
@endpush
