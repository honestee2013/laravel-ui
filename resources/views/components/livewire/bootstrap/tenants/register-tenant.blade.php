<div>
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form wire:submit.prevent="register">
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" wire:model="company_name">
            @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" wire:model="email">
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" wire:model="password">
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Desired Subdomain</label>
            <div class="input-group">
                <input type="text" class="form-control" wire:model="subdomain" placeholder="acme">
                <span class="input-group-text">.{{ config('app.domain') }}</span>
            </div>
            @error('subdomain') <small class="text-danger">{{ $message }}</small> @enderror
            <small class="form-text text-muted">Only lowercase letters, numbers, and hyphens. 3–32 characters.</small>
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Get Started</span>
            <span wire:loading>Creating...</span>
        </button>
    </form>

    <!-- Modal: Preparing workspace -->
    <div wire:ignore.self class="modal fade" id="preparingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="mb-0">Preparing your workspace…</p>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('show-preparing', () => {
            const modal = new bootstrap.Modal(document.getElementById('preparingModal'));
            modal.show();
        });
    </script>
    @endscript
</div>