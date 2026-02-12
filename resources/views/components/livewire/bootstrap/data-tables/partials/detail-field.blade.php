{{-- resources/views/components/livewire/bootstrap/data-tables/partials/enhanced-detail-field.blade.php --}}
@props([
    'column',
    'fieldDefinitions',
    'selectedItem',
    'multiSelectFormFields',
    'config' => []
])

@php
    use QuickerFaster\LaravelUI\Services\Formatting\FieldFormattingService;
    
    $isPasswordField = $column == 'password';
    $isRelationshipField = isset($fieldDefinitions[$column]) && isset($fieldDefinitions[$column]['relationship']);
    $isMultiSelectField = $column && isset($multiSelectFormFields) && in_array($column, array_keys($multiSelectFormFields));
    $isImageField = in_array($column, DataTableConfig::getSupportedImageColumnNames());
    $isDocumentField = in_array($column, DataTableConfig::getSupportedDocumentColumnNames());
    $isStatusField = in_array($column, ['status', 'is_approved', 'is_active', 'is_published', 'needs_review']);
    $isBooleanField = isset($selectedItem->$column) && is_bool($selectedItem->$column);
    
    // Get badge color for status/boolean fields
    $columnValue = is_bool($selectedItem->$column) ? ($selectedItem->$column ? 'true' : 'false') : strtolower($selectedItem->$column);

    // Get color for badge/status
    $badgeColor = 'secondary';
if ($isStatusField || $isBooleanField) {

        $badgeColor = match ($columnValue) {
            'planned_leave', 'leave', 'auto' => 'primary',
            'sick_leave', 'holiday', 'adjusted' => 'info',
            'active', 'approved', 'published', 'success', 'excused', 'present', 'true' => 'success',
            'pending', 'warning', 'needs_review', 'late' => 'warning',
            'inactive', 'rejected', 'cancelled', 'danger', 'unplanned_absent', 'absent', 'false' => 'danger',
            default => 'secondary',
        };
    }












    $label = $fieldDefinitions[$column]['label']?? 'Unknown';

@endphp

<div class="field-content">
    @if ($isPasswordField)
        <div class="password-field">
            <div class="d-flex align-items-center">
                <div class="text-muted font-monospace bg-light rounded px-3 py-1">
                    ••••••••••••••••
                </div>
                <button type="button" 
                        class="btn btn-sm btn-link ms-2"
                        onclick="togglePasswordView(this)"
                        data-bs-toggle="tooltip"
                        data-bs-title="Show password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        
    @elseif($isRelationshipField)
        @if (isset($fieldDefinitions[$column]['relationship']['type']) && 
             ($fieldDefinitions[$column]['relationship']['type'] == 'hasMany' || 
              $fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany' || 
              $fieldDefinitions[$column]['relationship']['type'] == 'morphToMany'))
            
            @php
                $relatedItems = $selectedItem->{$fieldDefinitions[$column]['relationship']['dynamic_property']};
                $displayField = $fieldDefinitions[$column]['relationship']['display_field'];
                
                // Handle nested display fields
                if (str_contains($displayField, '.')) {
                    $parts = explode('.', $displayField);
                    $field = end($parts);
                } else {
                    $field = $displayField;
                }
                
                $values = $relatedItems ? $relatedItems->pluck($field)->filter()->toArray() : [];
            @endphp
            
            @if(count($values) > 0)
                <div class="relationship-multi-values">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach(array_slice($values, 0, 5) as $value)
                            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                                {{ $value }}
                            </span>
                        @endforeach
                    </div>
                    @if(count($values) > 5)
                        <div class="collapse" id="moreValues-{{ Str::slug($column) }}">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach(array_slice($values, 5) as $value)
                                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                                        {{ $value }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <button class="btn btn-sm btn-link p-0 text-primary" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#moreValues-{{ Str::slug($column) }}"
                                aria-expanded="false">
                            <i class="fas fa-chevron-down me-1"></i>
                            Show {{ count($values) - 5 }} more
                        </button>
                    @endif
                </div>
            @else
                <span class="text-muted fst-italic">No related items</span>
            @endif
            
        @else
            @php
                $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'] ?? $column;
                $displayField = $fieldDefinitions[$column]['relationship']['display_field'] ?? 'name';
                
                // Handle nested display fields
                if (str_contains($displayField, '.')) {
                    $parts = explode('.', $displayField);
                    $field = end($parts);
                } else {
                    $field = $displayField;
                }
                
                $relatedItem = optional($selectedItem->{$dynamic_property})->$field;
                $relatedRecord = $selectedItem->{$dynamic_property};
            @endphp
            
            @if($relatedItem)
                {{-- - --@if($relatedRecord && $this->isEmployeeRelation($relatedRecord))
                    <div class="employee-relationship">
                        <a href="{{ route('hr.employees.show', $relatedRecord->id) }}"
                           class="text-decoration-none d-flex align-items-center gap-2"
                           target="_blank">
                            @php
                                $avatarUrl = $this->getAvatarUrl($relatedRecord);
                            @endphp
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" 
                                     alt="{{ $relatedItem }}"
                                     class="rounded-circle"
                                     style="width: 24px; height: 24px; object-fit: cover;">
                            @endif
                            <span class="fw-medium text-primary">{{ $relatedItem }}</span>
                            <i class="fas fa-external-link-alt text-muted" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                @else
                    <span class="fw-medium">{{ $relatedItem }}</span>
                @endif --}}
            @else
                <span class="text-muted fst-italic">Not set</span>
            @endif
        @endif
        
    @elseif($isMultiSelectField)
        <div class="multi-select-values">
            @php
                $values = str_replace(['[', ']', '"'], '', $selectedItem->$column);
                $valueArray = $values ? array_map('trim', explode(',', $values)) : [];
            @endphp
            
            @if(count($valueArray) > 0)
                <div class="d-flex flex-wrap gap-2">
                    @foreach($valueArray as $value)
                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                            {{ $value }}
                        </span>
                    @endforeach
                </div>
            @else
                <span class="text-muted fst-italic">No values selected</span>
            @endif
        </div>
        
    @elseif($isImageField && $selectedItem->$column)
        <div class="image-field">
            <img src="{{ asset('storage/' . $selectedItem->$column) }}" 
                 alt="{{ $label }}"
                 class="rounded shadow-sm"
                 style="max-width: 200px; max-height: 150px; object-fit: contain; cursor: pointer;"
                 onclick="openImageModal('{{ asset('storage/' . $selectedItem->$column) }}', '{{ $label }}')"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZmFmYWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBOb3QgQXZhaWxhYmxlPC90ZXh0Pjwvc3ZnPg=='">
        </div>
        
    @elseif($isDocumentField && $selectedItem->$column)
        <div class="document-field">
            @php
                $filePath = $selectedItem->$column;
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $fileName = basename($filePath);
                $icon = "";
                
                switch($extension) {
                    case 'pdf':
                        $icon = "fa-file-pdf text-danger";
                        $colorClass = "text-danger";
                        break;
                    case 'doc':
                    case 'docx':
                        $icon = "fa-file-word text-primary";
                        $colorClass = "text-primary";
                        break;
                    case 'xls':
                    case 'xlsx':
                        $icon = "fa-file-excel text-success";
                        $colorClass = "text-success";
                        break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'webp':
                        $icon = "fa-file-image text-info";
                        $colorClass = "text-info";
                        break;
                    default:
                        $icon = "fa-file text-secondary";
                        $colorClass = "text-secondary";
                }
            @endphp
            
            <div class="d-flex align-items-center gap-2">
                <i class="fas {{ $icon }} fa-2x"></i>
                <div>
                    <div class="fw-medium">{{ $fileName }}</div>
                    <small class="text-muted">{{ strtoupper($extension) }} • {{ round(filesize(storage_path('app/public/' . $filePath)) / 1024, 1) }}KB</small>
                </div>
                <a href="{{ route('file.download', ['path' => $filePath]) }}"
                   class="btn btn-sm btn-outline-{{ str_replace('text-', '', $colorClass) }} ms-auto"
                   data-bs-toggle="tooltip"
                   data-bs-title="Download">
                    <i class="fas fa-download"></i>
                </a>
            </div>
        </div>
        
    @elseif($isStatusField || $isBooleanField)
        <div class="status-field">
            <span class="badge bg-{{ $badgeColor }}-light text-{{ $badgeColor }} rounded-pill px-3 py-2">
                
                @if($isBooleanField)
                    <i class="fas fa-{{ $selectedItem->$column ? 'check-circle' : 'times-circle' }} me-2"></i>
                @endif
                {{ is_bool($selectedItem->$column) ? ($selectedItem->$column ? 'Yes' : 'No') : ucfirst($selectedItem->$column) }}
            </span>
        </div>
        
    @elseif(filter_var($selectedItem->$column, FILTER_VALIDATE_EMAIL))
        <div class="email-field">
            <a href="mailto:{{ $selectedItem->$column }}" 
               class="text-decoration-none d-flex align-items-center gap-2">
                <i class="fas fa-envelope text-primary"></i>
                <span class="text-primary">{{ $selectedItem->$column }}</span>
            </a>
        </div>
        
    @elseif(filter_var($selectedItem->$column, FILTER_VALIDATE_URL))
        <div class="url-field">
            <a href="{{ $selectedItem->$column }}" 
               target="_blank"
               class="text-decoration-none d-flex align-items-center gap-2">
                <i class="fas fa-external-link-alt text-info"></i>
                <span class="text-info text-truncate" style="max-width: 300px;">
                    {{ parse_url($selectedItem->$column, PHP_URL_HOST) }}
                </span>
            </a>
        </div>
        
    @elseif(is_numeric($selectedItem->$column) && !in_array($column, ['id', 'phone', 'zip_code', 'postal_code']))
        <div class="numeric-field">
            <span class="fw-medium font-monospace">
                {{ number_format($selectedItem->$column, 2) }}
            </span>
        </div>
        
    @elseif(strtotime($selectedItem->$column) !== false && strlen($selectedItem->$column) > 5)
        <div class="date-field">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar text-secondary"></i>
                <div>
                    <div class="fw-medium">
                        {{ date('F j, Y', strtotime($selectedItem->$column)) }}
                    </div>
                    @if(strlen($selectedItem->$column) > 10)
                        <small class="text-muted">
                            {{ date('g:i A', strtotime($selectedItem->$column)) }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
        
    @else
        <div class="text-field">
            @if($selectedItem->$column === null || $selectedItem->$column === '')
                <span class="text-muted fst-italic">Empty</span>
            @else
                @php
                    $dataFormatter = app(FieldFormattingService::class);
                    $value = $dataFormatter->format($column, $selectedItem->$column, $fieldDefinitions, $selectedItem);
                @endphp
                @if(isset($fieldDefinitions[$column]["field_type"]) && $fieldDefinitions[$column]["field_type"] == "textarea")
                    <div class="text-content" style="/*white-space: pre-wrap;*/ max-height: 200px; overflow-y: auto;">
                        {{ $value }}
                    </div>
                @else
                    <span class="fw-medium">{{ $value }}</span>
                @endif
            @endif
        </div>
    @endif
</div>

<style>
    .field-content {
        min-height: 24px;
    }
    
    .relationship-multi-values .badge {
        transition: all 0.2s ease;
    }
    
    .relationship-multi-values .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .employee-relationship img {
        transition: transform 0.2s ease;
    }
    
    .employee-relationship:hover img {
        transform: scale(1.1);
    }
    
    .image-field img {
        transition: transform 0.3s ease;
    }
    
    .image-field img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .document-field {
        padding: 0.75rem;
        background: rgba(248, 249, 252, 0.5);
        border-radius: 0.5rem;
        border: 1px solid #e3e6f0;
        transition: all 0.2s ease;
    }
    
    .document-field:hover {
        background: white;
        border-color: #4e73df;
    }
    
    .badge.bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .badge.bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .badge.bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    .badge.bg-secondary-light { background-color: rgba(133, 135, 150, 0.1); }
    .badge.bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    
    .badge.bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    
    .text-content {
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .text-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .text-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .text-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
</style>

<script>
    function togglePasswordView(button) {
        const field = button.closest('.password-field');
        const dots = field.querySelector('.font-monospace');
        const icon = button.querySelector('i');
        
        if (dots.textContent.includes('•')) {
            // Show actual password (you would need to fetch it from server)
            dots.textContent = '********';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            button.setAttribute('data-bs-title', 'Hide password');
        } else {
            dots.textContent = '••••••••••••••••';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
            button.setAttribute('data-bs-title', 'Show password');
        }
        
        // Update tooltip
        const tooltip = bootstrap.Tooltip.getInstance(button);
        if (tooltip) {
            tooltip.hide();
            tooltip.dispose();
            new bootstrap.Tooltip(button);
        }
    }
</script>