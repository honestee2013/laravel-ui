{{-- resources/views/components/livewire/bootstrap/auth/quick-configuration.blade.php --}}
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Almost there! Configure your workspace</h5>
                    <small class="text-muted">This helps us customize your experience</small>
                </div>
                
                <div class="card-body">
                    <form wire:submit.prevent="submit">
                        {{-- Employee Count --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">How many employees do you have?</label>
                            <div class="row">
                                @foreach($employeeCounts as $value => $label)
                                    <div class="col-6 col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   wire:model="employee_count" 
                                                   value="{{ $value }}" 
                                                   id="employees_{{ $value }}"
                                                   {{ $value === '1-10' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="employees_{{ $value }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Industry --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">What industry are you in?</label>
                            <select class="form-select" wire:model="industry">
                                @foreach($industries as $value => $label)
                                    <option value="{{ $value }}" {{ $value === 'technology' ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Modules --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Which features interest you?</label>
                            <small class="text-muted d-block mb-2">You can always enable more later</small>
                            
                            <div class="row">
                                @foreach($availableModules as $key => $label)
                                    <div class="col-6 col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   wire:model="active_modules" 
                                                   value="{{ $key }}" 
                                                   id="module_{{ $key }}"
                                                   {{ $key === 'hr' ? 'checked disabled' : '' }}>
                                            <label class="form-check-label" for="module_{{ $key }}">
                                                {{ $label }}
                                                @if($key === 'hr')
                                                    <small class="text-muted">(Required)</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Launch My Workspace
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>