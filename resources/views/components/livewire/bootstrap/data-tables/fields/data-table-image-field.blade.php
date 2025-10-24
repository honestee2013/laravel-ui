{{-- resources/views/components/data-table-image-field.blade.php --}}
@props([
    'field',
    'reactivity' => 'defer',
    'readOnlyFields',
    'fields',
    'fieldDefinitions',
    'binding', 

])


<div class="row border rounded-3 m-1 p-3">

    <div class="col-9">
        <input 
            type="file" 
            wire:model.{{ $reactivity }}="{{ $binding }}" 
            accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, $fieldDefinitions[$field]['fileTypes'] ?? [])) }}"
            id="{{ $field }}" 
            class="form-control rounded-pill"
            @if(in_array($field, $readOnlyFields)) disabled @endif
        />

        @if (isset($fields[$field]) && is_object($fields[$field]) && $fields[$field]->temporaryUrl())
            <span class="text-xs">This is the <strong>Selected Image</strong></span>
        @elseif(!empty($fields[$field]))
            <span class="text-xs">This is the <strong>Current Image</strong></span>
        @endif
    </div>

    <div class="col-2 rounded border border-red p-1 ms-3 image-container" id="image-container-{{ $field }}">
        @if (isset($fields[$field]))
            <img id="image-preview-{{ $field }}"
                @if (is_object($fields[$field]) && $fields[$field]->temporaryUrl())
                    src="{{ $fields[$field]->temporaryUrl() }}"
                @elseif (isset($fields[$field]))
                    src="{{ asset('storage/' . $fields[$field]) }}"
                @endif
                alt="Image Preview" style="width: 100%;" />

            <span
                @if (is_object($fields[$field]) && $fields[$field]->temporaryUrl())
                    wire:click="$dispatch('openCropImageModalEvent', ['{{ $field }}', '{{ $fields[$field]->temporaryUrl() }}', '{{ $this->getId() }}'])"
                @else
                    wire:click="$dispatch('openCropImageModalEvent', ['{{ $field }}', '{{ asset('storage/' . $fields[$field]) }}', '{{ $this->getId() }}'])"
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
