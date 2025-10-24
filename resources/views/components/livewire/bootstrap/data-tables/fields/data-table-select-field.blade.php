{{-- resources/views/components/data-table-select-field.blade.php --}}
@props([
    'field',
    'options',
    'reactivity' => 'defer',
    'multiSelectFormFields',
    'singleSelectFormFields',
    'readOnlyFields',
    'label',
    'binding', 

])

@php
    $isMultiSelect = $multiSelectFormFields && in_array($field, array_keys($multiSelectFormFields));
    $isSingleSelect = $singleSelectFormFields && in_array($field, array_keys($singleSelectFormFields));
@endphp

<select
    @if($isMultiSelect)
        wire:key="multi-select-{{ $field }}" multiple
        wire:model.{{ $reactivity }}="multiSelectFormFields.{{ $field }}"
    @elseif($isSingleSelect)
        wire:key="single-select-{{ $field }}"
        wire:model.{{ $reactivity }}="singleSelectFormFields.{{ $field }}"
    @else
        wire:key="select-{{ $field }}"
        wire:model.{{ $reactivity }}="{{ $binding }}"
    @endif
    name="{{ $field }}" id="{{ $field }}" class="form-control"
    @if(in_array($field, $readOnlyFields)) disabled @endif
>
    <option style="display:none" value="">
        Select {{ $label }}...
    </option>

    @foreach ($options as $key => $value)
        <option value="{{ $key }}">{{ $value }}</option>
    @endforeach
</select>

