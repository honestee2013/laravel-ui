
@props(['row', 'column', 'fieldDefinitions', 'multiSelectFormFields'])

@php
    use QuickerFaster\LaravelUI\Services\DataTables\DataTableService;
    use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;


    $dataTableService = app(DataTableService::class);
    $value = $dataTableService->formatCellValue($row, $column, $fieldDefinitions);
   
@endphp

@if (isset($fieldDefinitions[$column]['relationship']))
    @if ($fieldDefinitions[$column]['relationship']['type'] == 'hasMany')
        {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
    @elseif ($fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany')
        {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
    @else
        @php
            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
            $displayField = explode(".", $fieldDefinitions[$column]['relationship']['display_field']);
            $displayField = count($displayField) > 1 ? $displayField[1] : $displayField[0];
        @endphp
        {{ optional($row->{$dynamic_property})->$displayField }}
    @endif
@elseif ($column && $multiSelectFormFields && in_array($column, array_keys($multiSelectFormFields)))
    {{ str_replace(',', ', ', str_replace(['[', ']', '"'], '', $row->$column)) }}
@elseif (in_array($column, DataTableConfig::getSupportedImageColumnNames()))
    @if ($row->$column)
        <img class="rounded-circle m-0" style="width: 4em; height: 4em;" 
             src="{{ asset('storage/' . $row->$column) }}" alt="">
    @else
        <i class="fas fa-file-image m-0 ms-2" style="font-size: 4em; color:lightgray;"></i>
    @endif
@elseif (in_array($column, DataTableConfig::getSupportedDocumentColumnNames()))
    @if ($row->$column)
        @php
            $filePath = $row->$column;
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileName = 'Download';// basename($filePath);
            $icon = "";

        @endphp

        @switch($extension)
            @case('pdf')
                @php $icon = "fa-file-pdf text-danger" @endphp
                @break
            @case('doc')
            @case('docx')
                @php $icon = "fa-file-word text-primary" @endphp
                @break
            @case('xls')
            @case('xlsx')
                @php $icon = "fa-file-excel text-success" @endphp
                @break
            @case('jpg')
            @case('jpeg')
            @case('png')
            @case('gif')
            @case('webp')
                @php $icon = "fa-file-image text-info" @endphp
                @break
            @case('txt')
                @php $icon = "fa-file-alt text-secondary" @endphp
                @break
            @default
                @php $icon = "fa-file" @endphp
        @endswitch
            <span wire:click="downloadDocument('{{$row->id}}', '{{$column}}')" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-original-title="Download" >
                <i class="fas {{$icon}} ms-2" style="font-size: 2.5em; margin-bottom: 0.1em;" ></i>
                <span class="text-info text-decoration-underline">{{ $fileName }}</span>
            </span>
    @else
        <i class="fas fa-file me-2 text-muted" style="font-size: 2.5em;"></i>
        <span class="text-muted">No file</span>
    @endif
@else
    {{ $value }}
@endif     