{{-- resources/views/components/data-table-select-field.blade.php --}}
@props([
    'field',
    'options',
    'reactivity' => 'defer',
    // 'multiSelectFormFields',
    // 'singleSelectFormFields',
    'readOnlyFields',
    'label',
    'binding', 
    'multiSelect' => false,

])

@php
    // $isMultiSelect = $multiSelectFormFields && in_array($field, array_keys($multiSelectFormFields));
    // $isSingleSelect = $singleSelectFormFields && in_array($field, array_keys($singleSelectFormFields));
@endphp

<select

    
    wire:model.{{ $reactivity }}="{{ $binding }}"
    wire:key="select-{{ $field }}" 
    id="{{ $field }}" 
    class="form-control"

    @if($multiSelect) multiple @endif
    @if(in_array($field, $readOnlyFields)) disabled @endif
>
    <option style="display:none" value="">
        Select {{ $label }}...
    </option>

    @foreach ($options as $key => $value)
        <option value="{{ $key }}">{{ $value }}</option>
    @endforeach
</select>

