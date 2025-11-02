{{-- resources/views/components/data-table-field.blade.php --}}
@props([
    'field',
    'fieldDefinitions',
    'isEditMode',
    'multiSelectFormFields',
    'singleSelectFormFields',
    'readOnlyFields',
    'fieldDefinitions',
    'model',
    'modelName',
    'reactivity' => 'defer',
    'autoGenerate' => false
])

@php

    $type = $fieldDefinitions[$field]['field_type'] ?? 'text';
    if (is_array($type)) {
        $type = $type['field_type'] ?? 'text';
    }

    $options = [];
    if (isset($fieldDefinitions[$field]['options'])) {
        if (isset($fieldDefinitions[$field]['options']['model'], $fieldDefinitions[$field]['options']['column'])) {
            $options = DataTableOption::getOptionList($fieldDefinitions[$field]['options']);
        } else {
            $options = $fieldDefinitions[$field]['options'];
        }
    }

    $display = $fieldDefinitions[$field]['display'] ?? null;
    $selected = $fieldDefinitions[$field]['selected'] ?? null;
    $label = $fieldDefinitions[$field]['label'] ?? ucwords(str_replace('_', ' ', $field));
    $wraperCssClasses = $fieldDefinitions[$field]['wraperCssClasses'] ?? 'col-12';
    $hasInlineAdd = isset($fieldDefinitions[$field]['relationship']['inlineAdd']) &&
                    $fieldDefinitions[$field]['relationship']['inlineAdd'];
                    
@endphp

<div class="{{ $wraperCssClasses }}">
    <div class="form-group">
        <label class="mt-2 mb-1" for="{{ $field }}">{{ $label }}</label>

        @if($hasInlineAdd)
            <span role="button" class="badge rounded-pill bg-primary text-xxs"
                onclick="Livewire.dispatch('openAddRelationshipItemModalEvent',
                [{{ json_encode($fieldDefinitions[$field]['relationship']['model']) }}])">
                Add
            </span>
        @endif

        @switch($type)
            @case('textarea')
                <textarea wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}" id="{{ $field }}"
                    class="form-control" name="{{ $field }}" rows="3"
                    @if(in_array($field, $readOnlyFields)) disabled @endif>{{ $fieldDefinitions[$field] ?? '' }}</textarea>
                @break

            @case('select')
                <x-core.views::data-table-select-field
                    :field="$field"
                    :options="$options"
                    :reactivity="$reactivity"
                    :multiSelectFormFields="$multiSelectFormFields"
                    :singleSelectFormFields="$singleSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                    :label="$label"
                />
                @break

            @case('checkbox')
                <x-core.views::data-table-checkbox-field
                    :field="$field"
                    :options="$options"
                    :display="$display"
                    :reactivity="$reactivity"
                    :multiSelectFormFields="$multiSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                    :fieldDefinitions="$fieldDefinitions"
                />
                @break

            @case('radio')
                <x-core.views::data-table-radio-field
                    :field="$field"
                    :options="$options"
                    :display="$display"
                    :reactivity="$reactivity"
                    :singleSelectFormFields="$singleSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                />
                @break

            @case('file')
                @if(in_array($field, DataTableConfig::getSupportedImageColumnNames()))
                    <x-core.views::data-table-image-field
                        :field="$field"
                        :reactivity="$reactivity"
                        :readOnlyFields="$readOnlyFields"
                        :fieldDefinitions="$fieldDefinitions"
                    />
                @else
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}"
                        id="{{ $field }}" class="form-control" value="{{ $fieldDefinitions[$field] ?? '' }}"
                        name="{{ $field }}"
                        @if(in_array($field, $readOnlyFields)) disabled @endif>
                @endif
                @break

            @case('date')
            @case('time')
            @case('datetime')
            @case('datetime-local')
                <input type="{{ $type }}" wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}"
                    id="{{ $field }}" class="form-control rounded-pill {{ $type }}"
                    value="{{ $fieldDefinitions[$field] ?? '' }}" name="{{ $field }}"
                    placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                    @if(in_array($field, $readOnlyFields)) disabled @endif>
                @break

            @default
                {{-- Handle date and time types specifically --}}
                @if (str_contains($type, "date") || str_contains($type, "time"))
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}"
                        id="{{ $field }}" class="form-control rounded-pill {{ $type }}"
                        value="{{ $fieldDefinitions[$field] ?? '' }}" name="{{ $field }}"
                        placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                        @if(in_array($field, $readOnlyFields)) disabled @endif>

                        @break
                @endif

                {{-- Default to text input --}}
                <div class="input-group">
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="fieldDefinitions.{{ $field }}"
                        id="{{ $field }}" class="form-control" value="{{ $fieldDefinitions[$field] ?? '' }}"
                        name="{{ $field }}"
                        placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                        @if($autoGenerate)
                            wire:focus="generateOrderNumber('{{ addslashes($model) }}', '{{ $modelName }}', '{{ $field }}')"
                        @endif
                        @if(in_array($field, $readOnlyFields)) disabled @endif>

                    @if($autoGenerate)
                        <button class="btn btn-outline-primary mb-0" type="button" id="button-addon2"
                            wire:click="generateOrderNumber('{{ addslashes($model) }}', '{{ $modelName }}', '{{ $field }}')">
                            <i class="fas fa-sync-alt me-1"></i> Auto
                        </button>
                    @endif
                </div>



        @endswitch

        {{-- Validation Errors --}}
        @error('fieldDefinitions.' . $field)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'fieldDefinitions.'], ['', '', ''], $message) }}
            </span>
        @enderror

        @error('multiSelectFormFields.' . $field)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'multi select form fieldDefinitions.'], ['', '', ''], $message) }}
            </span>
        @enderror

        @error('singleSelectFormFields.' . $field)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'single select form fieldDefinitions.'], ['', '', ''], $message) }}
            </span>
        @enderror
    </div>
</div>
