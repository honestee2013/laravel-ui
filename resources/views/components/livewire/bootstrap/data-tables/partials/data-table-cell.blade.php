{{-- resources/views/components/livewire/bootstrap/data-tables/partials/enhanced-data-table-cell.blade.php --}}
@props(['row', 'column', 'fieldDefinitions', 'multiSelectFormFields', 'index'])

@php
    use QuickerFaster\LaravelUI\Services\DataTables\DataTableService;
    use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;
    use QuickerFaster\LaravelUI\Services\Formatting\FieldFormattingService;

    $dataFormatter = app(FieldFormattingService::class);
    $value = $dataFormatter->format($column, $row->$column, $fieldDefinitions, $row);

    // Check if this is a name column
    $isNameColumn = in_array($column, ['first_name', 'last_name', 'name', 'employee.first_name', 'employee.last_name']);

    // Get badge configuration from field definitions
    $isBadgeField = isset($fieldDefinitions[$column]['badge']) && $fieldDefinitions[$column]['badge'];
    $badgeColors = $fieldDefinitions[$column]['badgeColors'] ?? [];

    // Get status/boolean fields
    $isStatusField = in_array($column, ['status', 'is_approved', 'is_active', 'is_published', 'needs_review']);
    $isBooleanField = is_bool($row->$column);


    $columnValue = is_bool($row->$column) ? ($row->$column ? 'true' : 'false') : strtolower($row->$column);

    // Get color for badge/status
    $badgeColor = 'secondary';
    if ($isBadgeField && isset($badgeColors[$columnValue])) {
        $badgeColor = $badgeColors[$columnValue];
    } elseif ($isStatusField || $isBooleanField) {

        $badgeColor = match ($columnValue) {
            'planned_leave', 'leave', 'auto' => 'primary',
            'sick_leave', 'holiday', 'adjusted' => 'info',
            'active', 'approved', 'published', 'success', 'excused', 'present', 'true' => 'success',
            'pending', 'warning', 'needs_review', 'late' => 'warning',
            'inactive', 'rejected', 'cancelled', 'danger', 'unplanned_absent', 'absent', 'false' => 'danger',
            default => 'secondary',
        };
    }

    // Get photo URL for employee tooltip
    $photoUrl = null;
    $tooltipHtml = '';

    if ($isNameColumn) {
        $photoUrl = $this->getAvatarUrl($row, $column);

        // Generate tooltip HTML for employee details
        $fullName = e($row->first_name . ' ' . $row->last_name);
        $jobTitle = isset($row->job_title)
            ? e($row->job_title)
            : (isset($row->employeePosition?->jobTitle?->title)
                ? e($row->employeePosition->jobTitle->title)
                : 'N/A');
        $department = isset($row->department?->name) ? e($row->department->name) : 'N/A';
        $status = isset($row->status) ? e($row->status) : 'Active';
        $statusColor = match (strtolower($status)) {
            'active' => 'success',
            'terminated', 'inactive' => 'danger',
            default => 'warning',
        };

        $tooltipHtml = "<div class='text-center p-2' style='min-width: 200px;'>
            <img src='{$photoUrl}' class='rounded mb-2' style='width: 80px; height: 80px; object-fit: cover;'>
            <div class='fw-bold mb-1'>{$fullName}</div>
            <div class='text-muted small mb-1'>{$jobTitle}</div>
            <div class='text-muted small mb-2'>{$department}</div>
            <span class='badge bg-{$statusColor}'>{$status}</span>
        </div>";
    }
@endphp

@if (isset($fieldDefinitions[$column]['relationship']))
    @if ($fieldDefinitions[$column]['relationship']['type'] == 'hasMany')
        <div class="relationship-values">
            {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
        </div>
    @elseif ($fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany')
        <div class="relationship-values">
            {{ implode(', ', $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray() ?? []) }}
        </div>
    @elseif ($fieldDefinitions[$column]['relationship']['type'] == 'morphToMany')
        @php
            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
            $displayField = explode('.', $fieldDefinitions[$column]['relationship']['display_field']);
            $displayField = count($displayField) > 1 ? $displayField[1] : $displayField[0];
        @endphp
        <div class="relationship-values">
            {{ $row->$dynamic_property->implode($displayField, ', ') }}
        </div>
    @else
        @php
            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
            $displayField = explode('.', $fieldDefinitions[$column]['relationship']['display_field']);
            $displayField = count($displayField) > 1 ? $displayField[1] : $displayField[0];
        @endphp

        <div class="relationship-value">
            {{ optional($row->{$dynamic_property})->$displayField }}
        </div>
    @endif
@elseif ($column && $multiSelectFormFields && in_array($column, array_keys($multiSelectFormFields)))
    <div class="multiselect-values">
        {{ str_replace(',', ', ', str_replace(['[', ']', '"'], '', $row->$column)) }}
    </div>
@elseif (in_array($column, DataTableConfig::getSupportedImageColumnNames()))
    @if ($row->$column)
        <img class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;"
            src="{{ asset('storage/' . $row->$column) }}" alt="" data-bs-toggle="tooltip"
            data-bs-title="Click to view larger"
            onclick="openImageModal('{{ asset('storage/' . $row->$column) }}', '{{ $row->name }}')">
    @else
        <div class="image-placeholder">
            <i class="fas fa-user-circle text-muted"></i>
        </div>
    @endif
@elseif (in_array($column, DataTableConfig::getSupportedDocumentColumnNames()))
    @if ($row->$column)
        @php
            $filePath = $row->$column;
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileName = 'Download';
            $icon = '';

            switch ($extension) {
                case 'pdf':
                    $icon = 'fa-file-pdf text-danger';
                    break;
                case 'doc':
                case 'docx':
                    $icon = 'fa-file-word text-primary';
                    break;
                case 'xls':
                case 'xlsx':
                    $icon = 'fa-file-excel text-success';
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'webp':
                    $icon = 'fa-file-image text-info';
                    break;
                case 'txt':
                    $icon = 'fa-file-alt text-secondary';
                    break;
                default:
                    $icon = 'fa-file';
            }
        @endphp

        <div class="document-cell" wire:click="downloadDocument('{{ $row->id }}', '{{ $column }}')">
            <i class="fas {{ $icon }} me-2" style="font-size: 1.25em;"></i>
            <span class="document-name">{{ $fileName }}</span>
        </div>
    @else
        <span class="text-muted small">No file</span>
    @endif
@else
    {{-- Enhanced display for different field types --}}
    <div class="cell-value">
        @if ($isNameColumn && $tooltipHtml)
            <span class="employee-name-tooltip" data-bs-toggle="tooltip" data-bs-html="true"
                data-bs-title="{{ $tooltipHtml }}" data-bs-placement="top">
                {{ $value }}
            </span>
@elseif ($isStatusField || $isBooleanField || $isBadgeField)
    <span class="badge badge-sm {{ ($isBooleanField && !isset($badgeColor)) ? ($row->$column ? 'bg-gradient-success' : 'bg-gradient-secondary') : 'bg-gradient-' . $badgeColor }} rounded-pill">
        @if($isBooleanField)
            <i class="fas fa-{{ $row->$column ? 'check' : 'times' }} me-1"></i>
        @endif
        
        {{ is_bool($row->$column) ? ($row->$column ? 'Yes' : 'No') : ucfirst($row->$column) }}
    </span>

        @elseif (is_numeric($value) && !in_array($column, ['id', 'phone', 'zip_code']))
            <span class="numeric-value">
                {{ number_format($value, 2) }}
            </span>
        @elseif (strtotime($value) !== false && strlen($value) > 5)
            <span class="date-value" data-bs-toggle="tooltip"
                data-bs-title="{{ date('F j, Y, g:i a', strtotime($value)) }}">
                {{ date('M j, Y', strtotime($value)) }}
            </span>
        @else
            <span
                class="text-value {{ $fieldDefinitions[$column]['field_type'] == 'textarea' ? 'text-truncate' : '' }}">
                {{ $fieldDefinitions[$column]['field_type'] == 'textarea' ? Str::words($value, 8) : $value }}
            </span>
        @endif
    </div>
@endif

<style>
    .relationship-values {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .multiselect-values {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .image-placeholder {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fc;
        border-radius: 50%;
        color: #858796;
    }

    .document-cell {
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: color 0.2s ease;
    }

    .document-cell:hover {
        color: var(--primary-color);
    }

    .document-name {
        font-size: 0.875rem;
    }

    .badge.bg-success-light {
        background-color: rgba(28, 200, 138, 0.1);
    }

    .badge.bg-warning-light {
        background-color: rgba(246, 194, 62, 0.1);
    }

    .badge.bg-danger-light {
        background-color: rgba(231, 74, 59, 0.1);
    }

    .badge.bg-secondary-light {
        background-color: rgba(133, 135, 150, 0.1);
    }

    .badge.bg-primary-light {
        background-color: rgba(78, 115, 223, 0.1);
    }

    .numeric-value {
        font-family: 'Roboto Mono', monospace;
        font-weight: 500;
    }

    .date-value {
        cursor: help;
        font-size: 0.875rem;
    }

    .text-truncate {
        max-width: 250px;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-name-tooltip {
        cursor: help;
        font-weight: 500;
        color: var(--primary-color);
    }
</style>

<script>
    function openImageModal(src, title) {
        const modalHtml = `
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${src}" class="img-fluid rounded" alt="${title}">
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        const existingModal = document.getElementById('imageModal');
        if (existingModal) existingModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }
</script>
