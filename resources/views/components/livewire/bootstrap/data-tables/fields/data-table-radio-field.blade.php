{{-- resources/views/components/data-table-radio-field.blade.php --}}
@props([
    'field',
    'options',
    'display',
    'reactivity' => 'defer',
    'singleSelectFormFields',
    'readOnlyFields',
    'binding', 
    'type',
    'fields',

])

@php
    $isSingleSelect = $singleSelectFormFields && in_array($field, array_keys($singleSelectFormFields));
    $inlineStyle = isset($display) && $display == 'inline' ? "display:inline-flex;" : "";
@endphp

@if(isset($display) && $display == 'inline')<div>@endif

@foreach ($options as $key => $value)
    {{--@if($isSingleSelect)--}}
        <div class="form-check" style="{{ $inlineStyle }}">
            <input wire:key="radio-{{ $key }}" class="form-check-input" type="radio"
                id="{{ $key }}" wire:model.{{ $reactivity }}="singleSelectFormFields.{{ $field }}"
                value="{{ $key }}"
                @if(in_array($field, $readOnlyFields)) disabled @endif>

            <label class="custom-control-label" for="{{ $key }}"
                style="{{ $inlineStyle ? 'margin: 0.25em 2em 1em 0.5em' : '' }}">
                {{ $value }}
            </label>
        </div>
    {{--@endif--}}
@endforeach

@if(isset($display) && $display == 'inline')</div>@endif
