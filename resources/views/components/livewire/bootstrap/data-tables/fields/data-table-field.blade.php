{{-- resources/views/components/data-table-field.blade.php --}}
@props([
    'field',
    'fieldDefinitions',
    'isEditMode',
    'multiSelectFormFields',
    'singleSelectFormFields',
    'readOnlyFields',
    'fields',
    'model',
    'modelName',
    'reactivity' => 'defer',
    'autoGenerate' => false,
        
    'fieldBindingPath' => null, // e.g., 'formData.employee.name'

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
                    

    $binding = $fieldBindingPath ?? 'fields.' . $field;
    $fieldBindingPath = $fieldBindingPath?? $field;
@endphp


<div class="{{ $wraperCssClasses }}">
    <div class="form-group">
        <label class="mt-2 mb-1" for="{{ $field }}">{{ $label }}</label>

        @if($hasInlineAdd && isset($fields[$field]) &&  isset($fields[$field]['relationship']) && isset($fields[$field]['relationship']['model']))
            <span role="button" class="badge rounded-pill bg-primary text-xxs"
                onclick="Livewire.dispatch('openAddRelationshipItemModalEvent',
                [{{ json_encode($fields[$field]['relationship']['model']) }}])">
                Add
            </span>
        @endif

        @switch($type)
            @case('textarea')
                <textarea wire:model.{{ $reactivity }}="{{ $binding }}" id="{{ $field }}"
                    class="form-control" name="{{ $field }}" rows="3"
                    @if(in_array($field, $readOnlyFields)) disabled @endif>{{ $fields[$field] ?? '' }}</textarea>
                @break

            @case('select')
                <x-qf::livewire.bootstrap.data-tables.fields.data-table-select-field
                    :field="$field"
                    :options="$options"
                    :reactivity="$reactivity"
                    :multiSelectFormFields="$multiSelectFormFields"
                    :singleSelectFormFields="$singleSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                    :label="$label"
                    :binding="$binding"
                />
                @break

            @case('checkbox')
            @case('boolcheckbox')
                <x-qf::livewire.bootstrap.data-tables.fields.data-table-checkbox-field
                    :field="$field"
                    :options="$options"
                    :display="$display"
                    :reactivity="$reactivity"
                    :multiSelectFormFields="$multiSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                    :fields="$fields"
                    :binding="$binding"
                    :type="$type"
                />
                @break



            @case('radio')
            @case('boolradio')
                <x-qf::livewire.bootstrap.data-tables.fields.data-table-radio-field
                    :field="$field"
                    :options="$options"
                    :display="$display"
                    :reactivity="$reactivity"
                    :multiSelectFormFields="$multiSelectFormFields"
                    :singleSelectFormFields="$singleSelectFormFields"
                    :readOnlyFields="$readOnlyFields"
                    :fields="$fields"
                    :binding="$binding"
                    :type="$type"

                />
                @break


            @case('file')
            
                @if(in_array($field, DataTableConfig::getSupportedImageColumnNames()))
                    <x-qf::livewire.bootstrap.data-tables.fields.data-table-image-field
                        :field="$field"
                        :reactivity="$reactivity"
                        :readOnlyFields="$readOnlyFields"
                        :fields="$fields"
                        :fieldDefinitions="$fieldDefinitions"
                        :binding="$binding"

                    />
                @else
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="{{ $binding }}"
                        id="{{ $field }}" class="form-control" value="{{ $fields[$field] ?? '' }}"
                        name="{{ $field }}"
                        accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, DataTableConfig::getSupportedDocumentExtensions())) }}"
                        @if(in_array($field, $readOnlyFields)) disabled @endif>
                @endif
                @break

            @case('date')
            @case('time')
            @case('datetime')
            @case('datetime-local')
                <input type="{{ $type }}" wire:model.{{ $reactivity }}="{{ $binding }}"
                    id="{{ $field }}" class="form-control rounded-pill {{ $type }}"
                    value="{{ $fields[$field] ?? '' }}" name="{{ $field }}"
                    placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                    @if(in_array($field, $readOnlyFields)) disabled @endif>
                @break

            @default
                {{-- Handle date and time types specifically --}}
                @if (str_contains($type, "date") || str_contains($type, "time"))
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="{{ $binding }}"
                        id="{{ $field }}" class="form-control rounded-pill {{ $type }}"
                        value="{{ $fields[$field] ?? '' }}" name="{{ $field }}"
                        placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                        @if(in_array($field, $readOnlyFields)) disabled @endif>

                        @break
                @endif

                {{-- Default to text input --}}
                <div class="input-group">
                    
                    <input type="{{ $type }}" wire:model.{{ $reactivity }}="{{ $binding }}"
                        id="{{ $field }}" class="form-control" value="{{ $fields[$field] ?? '' }}"
                        name="{{ $field }}"
                        placeholder="Please provide the {{ strtolower(str_replace('_', ' ', $field)) }}..."
                        @if($autoGenerate)
                            wire:click="generateCodeForField('{{ addslashes($model) }}', '{{ $modelName }}', '{{ $field }}')"
                        @endif
                        @if(in_array($field, $readOnlyFields)) disabled @endif>

                    @if($autoGenerate)
                        <button class="btn btn-outline-primary mb-0" type="button" id="button-addon2"
                            wire:click="generateCodeForField('{{ addslashes($model) }}', '{{ $modelName }}', '{{ $field }}')"
                            >
                            <i class="fas fa-sync-alt me-1"></i> Auto
                        </button>
                    @endif
                </div>



        @endswitch
        

        {{-- Validation Errors --}}
        @error($fieldBindingPath)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'fields.'], ['', '', ''], $message) }}
            </span>
        @enderror

        @error('multiSelectFormFields.' . $field)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'multi select form fields.'], ['', '', ''], $message) }}
            </span>
        @enderror

        @error('singleSelectFormFields.' . $field)
            <span class="text-danger text-sm mb-0">
                {{ str_replace(['characters.', 'id', 'single select form fields.'], ['', '', ''], $message) }}
            </span>
        @enderror

    </div>
</div>
