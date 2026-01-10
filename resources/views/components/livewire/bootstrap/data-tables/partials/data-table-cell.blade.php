{{-- resources/views/components/livewire/bootstrap/data-tables/partials/data-table-cell.blade.php --}}
@props(['row', 'column', 'fieldDefinitions', 'multiSelectFormFields'])

@php
    use QuickerFaster\LaravelUI\Services\DataTables\DataTableService;
    use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;
    use QuickerFaster\LaravelUI\Services\Formatting\FieldFormattingService;

    $dataFormatter = app(FieldFormattingService::class);
    $value = $dataFormatter->format($column, $row->$column, $fieldDefinitions, $row);
    
    // Cell formatter to be implemented later
    // $dataTableService = app(DataTableService::class);
    // $value = $dataTableService->formatCellValue($row, $column, $fieldDefinitions);
   
    // Check if this is a name column (adjust based on your fields)
    $isNameColumn = in_array($column, ['first_name', 'last_name', 'name']);
    
    // Get photo URL
    $photoUrl = null;
    if ($isNameColumn) {
        if (isset($row->photo) && $row->photo) {
            $photoUrl = asset('storage/' . $row->photo);
        } elseif (isset($row->employeeProfile?->photo) && $row->employeeProfile?->photo) {
            $photoUrl = asset('storage/' . $row->employeeProfile->photo);
        } elseif (isset($row->user?->photo) && $row->user?->photo) {
            $photoUrl = asset('storage/' . $row->user->photo);
        } else {
            // Fallback avatar
            $fullName = $row->first_name . ' ' . $row->last_name;
            $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=4e73df&color=fff&size=80';
        }
    }
    
    // Get employee details for tooltip
    $tooltipHtml = '';
    if ($isNameColumn && $photoUrl) {
        $fullName = e($row->first_name . ' ' . $row->last_name);
        $jobTitle = isset($row->job_title) ? e($row->job_title) : (isset($row->employeePosition?->jobTitle?->title) ? e($row->employeePosition->jobTitle->title) : 'N/A');
        $department = isset($row->department?->name) ? e($row->department->name) : 'N/A';
        $status = isset($row->status) ? e($row->status) : 'Active';

        // $email = isset($row->email) ? e($row->email) : 'N/A';
        // $hireDate = isset($row->hire_date) ? e($row->hire_date->format('M Y')) : 'N/A';

        // If you store job title in employee_positions table
        // $jobTitle = $row->employeePositions?->first()?->jobTitle?->title ?? 'N/A';

        // If department is stored directly on employee
        // $department = $row->department?->name ?? 'N/A';
        
        // Status badge color
        $statusColor = match(strtolower($status)) {
            'active' => 'success',
            'terminated', 'inactive' => 'danger',
            default => 'warning'
        };
        
        $tooltipHtml = "<div class='text-center p-2' style='min-width: 200px;'>
            <img src='{$photoUrl}' class='rounded mb-2' style='width: 80px; height: 80px; object-fit: cover;'>
            <div class='fw-bold mb-1'>{$fullName}</div>
            <div class='text-muted small mb-1'>{$jobTitle}</div>
            <div class='text-muted small mb-2'>{$department}</div>

            <span class='badge bg-{$statusColor}'>{$status}</span>
        </div>";
        // <div class='text-muted small'>{$email}</div>
        // <div class='text-muted small'>Hired: {$hireDate}</div>
    }
@endphp


@if (isset($fieldDefinitions[$column]['relationship']))
    @if ($fieldDefinitions[$column]['relationship']['type'] == 'hasMany')
        {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
    @elseif ($fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany')
        {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
    @elseif ($fieldDefinitions[$column]['relationship']['type'] == 'morphToMany')
        @php
            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
            $displayField = explode(".", $fieldDefinitions[$column]['relationship']['display_field']);
            $displayField = count($displayField) > 1 ? $displayField[1] : $displayField[0];
        @endphp
        {{ $row->$dynamic_property->implode($displayField, ', ') }} 
    @else
        @php
            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
            $displayField = explode(".", $fieldDefinitions[$column]['relationship']['display_field']);
            $displayField = count($displayField) > 1 ? $displayField[1] : $displayField[0];
        @endphp
        {{-- optional($row->{$dynamic_property})->$displayField --}}
        Unknown
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
    {{-- Enhanced name column with tooltip --}}
    @if ($isNameColumn && $tooltipHtml)
        <span class="d-none d-md-inline position-relative">
            <span class="text-reset text-decoration-none employee-name"
                  data-bs-toggle="tooltip"
                  data-bs-html="true"
                  data-bs-title="{{ $tooltipHtml }}"
                  data-bs-placement="top">
                {{ $value }}
            </span>
        </span>
        <span class="d-md-none">{{ $value }}</span>
    @else
        {{ $value }}
    @endif
@endif  




{{-- Initialize Bootstrap tooltips for datatables --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth >= 768) {
      Livewire.hook('message.processed', (message, component) => {
        initializeEnhancedTooltips();
      });
      initializeEnhancedTooltips();
    }
  });

  function initializeEnhancedTooltips() {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('.employee-name[data-bs-toggle="tooltip"]')
    );
    
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
      if (existingTooltip) {
        existingTooltip.dispose();
      }
      
      new bootstrap.Tooltip(tooltipTriggerEl, {
        html: true,
        trigger: 'hover',
        placement: 'top',
        delay: { show: 250, hide: 150 },
        container: 'body', // Prevents clipping in table cells
        customClass: 'employee-tooltip' // For custom styling
      });
    });
  }
</script>

<style>
  /* Enhanced tooltip styling */
  .employee-tooltip .tooltip-inner {
    background: white !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    border-radius: 0.5rem;
    padding: 0 !important;
    min-width: 200px;
  }
  
  /* Ensure tooltip appears above everything */
  .employee-tooltip {
    z-index: 1080 !important;
  }
</style>