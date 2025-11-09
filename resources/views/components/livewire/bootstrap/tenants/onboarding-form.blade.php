<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Company Onboarding</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Please complete your company billing information.</p>

                    <form wire:submit.prevent="save">
                        <!-- Address Line 1 -->
                        <div class="mb-3">
                            <label for="billing_address_line_1" class="form-label">Billing Address</label>
                            <input
                                type="text"
                                class="form-control @error('billing_address_line_1') is-invalid @enderror"
                                id="billing_address_line_1"
                                wire:model="billing_address_line_1"
                                placeholder="Street address"
                            >
                            @error('billing_address_line_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="mb-3">
                            <label for="billing_city" class="form-label">City</label>
                            <input
                                type="text"
                                class="form-control @error('billing_city') is-invalid @enderror"
                                id="billing_city"
                                wire:model="billing_city"
                                placeholder="City"
                            >
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Postal Code -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="billing_postal_code" class="form-label">Postal Code</label>
                                <input
                                    type="text"
                                    class="form-control @error('billing_postal_code') is-invalid @enderror"
                                    id="billing_postal_code"
                                    wire:model="billing_postal_code"
                                    placeholder="12345"
                                >
                                @error('billing_postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="col-md-6">
                                <label for="billing_country_code" class="form-label">Country</label>
                                <select
                                    class="form-select @error('billing_country_code') is-invalid @enderror"
                                    id="billing_country_code"
                                    wire:model="billing_country_code"
                                >
                                    <option value="">Select Country</option>
                                  
                                </select>
                                @error('billing_country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove>Complete Onboarding</span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>