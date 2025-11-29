@php
    $appUrl = config('app.url'); // Get the full URL from the configuration
    $host = parse_url($appUrl, PHP_URL_HOST);
@endphp



<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Create Your HR Workspace</div>
                <div class="card-body">

                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session()->get('message') }}</div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger">{{ session()->get('error') }}</div>
                    @endif

                    @if (session()->has('warning'))
                        <div class="alert alert-warning">{{ session()->get('warning') }}</div>
                    @endif

                    @if (session()->has('info'))
                        <div class="alert alert-info">{{ session()->get('info') }}</div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" wire:model.live.debounce="company_name">
                            @error('company_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model="billing_email">
                            @error('billing_email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" wire:model="password">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" wire:model="password_confirmation">
                        </div>

                        {{-- Enhanced subdomain field with regeneration option --}}
                        <div class="mb-3">
                            <label class="form-label">Workspace URL</label>
                            <div class="input-group">
                                <span class="input-group-text">https://www.</span>
                                <input type="text" class="form-control" wire:model.live.debounce="subdomain">
                                <span class="input-group-text">.{{ $host }}</span>
                                {{-- - @if (!$auto_generated)
            <button type="button" class="btn btn-outline-secondary" 
                    wire:click="regenerateSubdomain"
                    title="Regenerate from company name">
                🔄
            </button>
        @endif --}}
                            </div>
                            <div class="form-text">
                                @if ($auto_generated)
                                    <small class="text-success">✓ Auto-generated from company name</small>
                                @else
                                    <small class="text-muted">Custom workspace URL
                                        <button type="button" class="btn btn-link p-0 border-0 align-baseline"
                                            wire:click="regenerateSubdomain">
                                            (re-generate) 🔄
                                        </button>
                                    </small>
                                @endif
                            </div>
                            @error('subdomain')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{---------------- SPINNER ------------------}}
    @include('qf::components.livewire.bootstrap.widgets.spinner')
</div>
