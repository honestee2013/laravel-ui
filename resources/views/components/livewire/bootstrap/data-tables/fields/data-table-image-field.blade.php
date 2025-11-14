{{-- resources/views/components/data-table-image-field.blade.php --}}
@props([
    'field',
    'reactivity' => 'live', // Change from 'defer' to 'live' for immediate updates
    'readOnlyFields',
    'fields',
    'fieldDefinitions',
    'binding',
])

@php
    // Debug current field state
    $fileObject = $fields[$field] ?? null;
    $hasFile = !empty($fileObject);
    $isUploadedFile = $hasFile && is_object($fileObject);
    $hasTemporaryUrl = false;
    $temporaryUrl = null;
    
    if ($isUploadedFile) {
        try {
            if (method_exists($fileObject, 'temporaryUrl')) {
                $temporaryUrl = $fileObject->temporaryUrl();
                $hasTemporaryUrl = true;
            }
        } catch (Exception $e) {
            \Log::debug('Temporary URL error', [
                'field' => $field,
                'error' => $e->getMessage(),
                'file_class' => get_class($fileObject)
            ]);
        }
    }
@endphp

<div class="row border rounded-3 m-1 p-3" wire:key="file-field-{{ $field }}-{{ $hasFile ? 'has-file' : 'no-file' }}">
    <div class="col-9">
        <input 
            type="file" 
            wire:model.{{ $reactivity }}="{{ $binding }}" 
            accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, $fieldDefinitions[$field]['fileTypes'] ?? ['jpg', 'jpeg', 'png', 'webp'])) }}"
            id="{{ $field }}" 
            class="form-control rounded-pill"
            @if(in_array($field, $readOnlyFields)) disabled @endif
        />

        {{-- Debug info --}}
        <div class="text-xs text-muted mt-1">
            @if ($hasFile)
                Field has value: {{ $isUploadedFile ? 'UploadedFile object' : 'Other' }}
                @if ($isUploadedFile)
                    - {{ $fileObject->getClientOriginalName() }}
                @endif
            @else
                No file selected
            @endif
        </div>

        @error($binding)
            <span class="text-danger text-xs">{{ $message }}</span>
        @enderror

        @if ($hasFile && $isUploadedFile)
            <span class="text-xs">
                @if ($hasTemporaryUrl)
                    This is the <strong>Selected Image</strong>
                @else
                    File selected: {{ $fileObject->getClientOriginalName() }} (preview not available)
                @endif
            </span>
        @elseif($hasFile && is_string($fileObject))
            <span class="text-xs">This is the <strong>Current Image</strong></span>
        @endif
    </div>

    <div class="col-2 rounded border border-red p-1 ms-3 image-container" id="image-container-{{ $field }}">
        @if ($hasTemporaryUrl)
            <img id="image-preview-{{ $field }}"
                src="{{ $temporaryUrl }}"
                alt="Image Preview" style="width: 100%;" />

            <span
                wire:click="$dispatch('openCropImageModalEvent', ['{{ $field }}', '{{ $temporaryUrl }}', '{{ $this->getId() }}'])"
                class="mx-2" style="" data-bs-toggle="tooltip" data-bs-original-title="Crop">
                <span style="cursor: pointer;">
                    <i class="fas fa-edit text-primary"></i>
                    <span class="text-xs">Crop</span>
                </span>
            </span>
        @elseif ($hasFile && $isUploadedFile)
            <div class="text-center">
                <i class="fas fa-file-image text-muted" style="font-size: 2rem;"></i>
                <p class="text-xs mt-1">{{ $fileObject->getClientOriginalName() }}</p>
                <p class="text-xs text-muted">Preview not available</p>
            </div>
        @elseif ($hasFile && is_string($fileObject))
            <img id="image-preview-{{ $field }}"
                src="{{ asset('storage/' . $fileObject) }}"
                alt="Image Preview" style="width: 100%;" />
        @endif
    </div>
</div>