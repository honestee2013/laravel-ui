{{-- resources/views/components/data-table-image-field.blade.php --}}
@props([
    'field',
    'reactivity' => 'defer',
    'readOnlyFields',
    'fieldDefinitions'
])

<div class="row border rounded-3 m-1 p-3">
    <div class="col-9">
        <input type="file" wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}" accept="image/*"
            id="{{ $field }}" class="form-control rounded-pill"
            @if(in_array($field, $readOnlyFields)) disabled @endif>

        @if (isset($fieldDefinitions[$field]) && is_object($fieldDefinitions[$field]) && $fieldDefinitions[$field]->temporaryUrl())
            <span class="text-xs">This is the <strong>Selected Image</strong></span>
        @elseif(!empty($fieldDefinitions[$field]))
            <span class="text-xs">This is the <strong> Current Image</strong></span>
        @endif
    </div>

    <div class="col-2 rounded border border-red p-1 ms-3 image-container" id="image-container-{{ $field }}">
        @if (isset($fieldDefinitions[$field]))
            <img id="image-preview-{{ $field }}"
                @if (is_object($fieldDefinitions[$field]) && $fieldDefinitions[$field]->temporaryUrl())
                    src="{{ $fieldDefinitions[$field]->temporaryUrl() }}"
                @elseif (isset($fieldDefinitions[$field]))
                    src="{{ asset('storage/' . $fieldDefinitions[$field]) }}"
                @endif
                alt="Image Preview" style="width: 100%;" />

            <span
                @if (is_object($fieldDefinitions[$field]) && $fieldDefinitions[$field]->temporaryUrl())
                    wire:click="openCropImageModal('{{ $field }}', '{{ $fieldDefinitions[$field]->temporaryUrl() }}', '{{ $this->getId() }}')"
                @else
                    wire:click="openCropImageModal('{{ $field }}', '{{ asset('storage/' . $fieldDefinitions[$field]) }}', '{{ $this->getId() }}')"
                @endif
                class="mx-2" style="" data-bs-toggle="tooltip" data-bs-original-title="Crop">
                <span style="cursor: pointer;">
                    <i class="fas fa-edit text-primary"></i>
                    <span class="text-xs">Crop</span>
                </span>
            </span>
        @endif
    </div>
</div>
