    <div class="row">
        <div>
            <hr class="horizontal dark my-0" />
        </div>
        <div class=" d-flex justify-content-end align-items-center flex-wrap gap-2 p-3">
            <button type="button" class="btn bg-gradient-secondary rounded-pill"
                onclick="Livewire.dispatch('closeModalEvent', [{'modalId': '{{$modalId}}' }])">Close</button>

            @if ($modalId !== "detail")
                <button type="button" class="btn bg-gradient-primary rounded-pill"
                    wire:click="saveRecord('{{$modalId}}')">
                    {{ $isEditMode ? 'Save Changes' : 'Add Record' }}
                </button>
            @endif
        </div>
    </div>


