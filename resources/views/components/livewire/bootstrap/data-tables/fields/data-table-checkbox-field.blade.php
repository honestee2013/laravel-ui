{{-- resources/views/components/data-table-checkbox-field.blade.php --}}
@props([
    'field',
    'options',
    'display',
    'reactivity' => 'defer',
    'multiSelectFormFields',
    'readOnlyFields',
    'fields',
    'binding', 
    'type',
])

@php
    $isMultiSelect = $multiSelectFormFields && in_array($field, array_keys($multiSelectFormFields));
    $isSingleSelect = !$isMultiSelect && isset($fields) && in_array($field, array_keys($fields));   
    $inlineStyle = isset($display) && $display == 'inline' ? "display:inline-flex;" : "";





@endphp

@if(isset($display) && $display == 'inline')<div>@endif

@foreach ($options as $key => $value)
    <div class="form-check" style="{{ $inlineStyle }}">
        @if($isMultiSelect)
            <input wire:key="multi-check-{{ $key }}" class="form-check-input" type="checkbox"
                id="{{ $key }}" wire:model.{{ $reactivity }}="multiSelectFormFields.{{ $field }}"
                value="{{ $key }}" name="{{ $field }}"
                {{-- Review posibility to remove the following line --}}
                @if(in_array($field, $readOnlyFields)) disabled @endif
            > 

        @elseif($isSingleSelect)
            <input wire:key="single-check-{{ $key }}" class="form-check-input" type="checkbox"
                id="{{ $key }}" wire:model.{{ $reactivity }}="singleSelectFormFields.{{ $field }}"
                value="{{ $key }}"
                name="{{ $field }}">    
        @else
            <input wire:key="check-{{ $key }}" class="form-check-input" type="checkbox"
                id="{{ $key }}" wire:model.{{ $reactivity }}="{{ $binding }}"
                value="{{ $key }}"
                name="{{ $field }}">
        @endif

        <label class="custom-control-label" for="{{ $key }}"
            style="{{ $inlineStyle ? 'margin: 0.25em 2em 1em 0.5em' : '' }}">
            {{ $value }}
        </label>
    </div>
@endforeach

@if(isset($display) && $display == 'inline')</div>@endif
