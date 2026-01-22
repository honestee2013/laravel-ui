
@include('qf::components.livewire.bootstrap.data-tables.modals.modal-header', [
    'modalId' => 'detail',
    'isEditMode' => $isEditMode,
])



{{-- resources/views/components/livewire/bootstrap/data-tables/enhanced-detail-modal.blade.php --}}
<div class="p-0 modal-form">
    @if ($selectedItem)
        @php
            // Get configuration
            $detailConfig = $config['switchViews']['detail'] ?? [];
            $layout = $detailConfig['layout'] ?? 'tab';
            $titleFields = $detailConfig['titleFields'] ?? [];
            $subtitleFields = $detailConfig['subtitleFields'] ?? [];
            $tabs = $detailConfig['tabs'] ?? [];
            $simpleLayout = isset($detailConfig['fields']);
            
            // Build title and subtitle
            $title = '';
            foreach ($titleFields as $field) {
                $value = data_get($selectedItem, $field);
                if ($value) {
                    $title .= ($title ? ' ' : '') . $value;
                }
            }
            $title = trim($title) ?: 'Record Details';
            
            $subtitle = '';
            foreach ($subtitleFields as $field) {
                $value = data_get($selectedItem, $field);
                if ($value) {
                    $subtitle .= ($subtitle ? ' • ' : '') . $this->formatFieldValue($selectedItem, $field);
                }
            }
            
            // Get avatar/image
            $avatarUrl = $this->getAvatarUrl($selectedItem);
            
            // Get status badges
            $badgeInfo = [];
            $badgeFields = ['status', 'is_approved', 'needs_review', 'is_active', 'is_published'];
            foreach ($badgeFields as $field) {


                if (isset($selectedItem->$field) && $selectedItem->$field !== null) {
                    $badgeInfo[$field] = [
                        'value' => $selectedItem->$field,
                        'label' => $fieldDefinitions[$field]['label'] ?? 'Unknown',
                        'color' => $this->getBadgeColor($selectedItem->$field, $field)
                    ];
                }
            }
            
            // Get image columns
            $imageColumns = array_filter($columns, function($column) use ($hiddenFields) {
                return !in_array($column, $hiddenFields['onDetail'] ?? []) && 
                       in_array($column, DataTableConfig::getSupportedImageColumnNames());
            });
            
            // Get text columns for simple layout
            $textColumns = array_filter($columns, function($column) use ($hiddenFields, $imageColumns) {
                return !in_array($column, $hiddenFields['onDetail'] ?? []) && 
                       !in_array($column, $imageColumns);
            });
        @endphp

        {{-- Modal Header --}}
        <div class="modal-header sticky-top bg-white border-bottom-0 pb-0">
            <div class="d-flex align-items-center w-100">
                {{-- Avatar/Icon --}}
                @if($avatarUrl && count($imageColumns) == 0)
                    <div class="avatar avatar-lg me-3">
                        <img src="{{ $avatarUrl }}" 
                             class="rounded-circle"
                             alt="{{ $title }}"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($title) }}&background=4e73df&color=fff&size=80'">
                    </div>
                @endif
                
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="modal-title mb-1">{{ $title }}</h5>
                            @if($subtitle)
                                <p class="text-muted small mb-2">{{ $subtitle }}</p>
                            @endif
                            
                            {{-- Status Badges --}}
                            @if(!empty($badgeInfo))
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <ul>
                                    @foreach($badgeInfo as $badge)
                                        <li class="my-3 text-sm">
                                        {{ $badge['label'] }}? 
                                        <span class="badge  bg-{{ $badge['color'] }}-light text-{{ $badge['color'] }} rounded-pill">
                                            @if(is_bool($badge['value']))
                                                <i class="fas fa-{{ $badge['value'] ? 'check' : 'times' }} me-1"></i>
                                            @endif
                                            {{ is_bool($badge['value']) ? ($badge['value'] ? 'Yes' : 'No') : ucfirst($badge['value']) }}
                                        </span>
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Quick Actions --}}
                        <div class="quick-actions">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="window.print()"
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Print">
                                <i class="fas fa-print"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary"
                                    {{--wire:click="editRecord({{ $selectedItem->id }})"--}}
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-body pt-3">
            {{-- Tab Layout --}}
            @if($layout === 'tab' && !empty($tabs))
                <div class="detail-tabs-wrapper mb-4">
                    <ul class="nav nav-tabs responsive-tab-nav" id="detailModalTabs" role="tablist">
                        @foreach($tabs as $index => $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#modal-tab-{{ Str::slug($tab['title'] ?? 'tab' . $index) }}"
                                        type="button"
                                        role="tab">
                                    @if(isset($tab['icon']))
                                        <i class="{{ $tab['icon'] }} me-2"></i>
                                    @endif
                                    {{ $tab['title'] ?? 'Tab ' . ($index + 1) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content" id="detailModalTabsContent">
                    @foreach($tabs as $index => $tab)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                             id="modal-tab-{{ Str::slug($tab['title'] ?? 'tab' . $index) }}"
                             role="tabpanel">
                             
                            @if(isset($tab['relation']))
                                {{-- Related Records Section --}}
                                @php
                                    $relationName = $tab['relation'];
                                    $relatedRecords = $selectedItem->$relationName;
                                @endphp
                                @if($relatedRecords && $relatedRecords->count() > 0)
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ $tab['title'] ?? 'Related Records' }}</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            @livewire('data-table', [
                                                'model' => get_class($relatedRecords->first()),
                                                'parentId' => $selectedItem->id,
                                                'parentRelation' => $relationName,
                                                'config' => $this->getRelationConfig($relationName),
                                            ])
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No {{ strtolower($tab['title'] ?? 'related records') }} found.
                                    </div>
                                @endif
                            @elseif(isset($tab['fields']))
                                {{-- Fields Section --}}
                                @if(count($tab['fields']) > 0)
                                    <div class="row">
                                        @foreach($tab['fields'] as $field)
                                            @if($this->shouldDisplayFieldInDetail($field, $hiddenFields['onDetail'] ?? []))
                                                <div class="col-md-6 mb-4">
                                                    <div class="field-group">
                                                        <label class="field-label text-muted small mb-2">
                                                            {{-- $this->getFieldLabel($field) --}}
                                                            {{ $fieldDefinitions[$field]['label']?? 'Unknown' }}
                                                        </label>
                                                        <div class="field-value">
                                                            <x-qf::livewire.bootstrap.data-tables.partials.detail-field
                                                                :column="$field"
                                                                :fieldDefinitions="$fieldDefinitions"
                                                                :selectedItem="$selectedItem"
                                                                :multiSelectFormFields="$multiSelectFormFields"
                                                                :config="$config"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">No fields to display in this tab.</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
                
            {{-- Simple Layout (two columns) --}}
            @else
                {{-- Image Gallery --}}
                @if(count($imageColumns) > 0)
                    <div class="card border-0 shadow-sm mb-4"> 
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-images me-2"></i> Images
                            </h6>
                            <div class="row g-3">
                                @foreach($imageColumns as $column)
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <div class="image-container position-relative d-inline-block">
                                                <img class="rounded shadow-sm img-fluid" 
                                                     style="max-height: 200px; max-width: 100%; object-fit: contain; cursor: pointer;"
                                                     src="{{ asset('storage/' . $selectedItem->$column) }}" 
                                                     {{-- alt="{{ $this->getFieldLabel($column) }}" --}}
                                                     alt = "{{ $fieldDefinitions[$column]['label']?? 'Unknown' }}"

                                                     onclick="openImageModal('{{ asset('storage/' . $selectedItem->$column) }}', '{{ $title }}')"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZmFmYWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBOb3QgQXZhaWxhYmxlPC90ZXh0Pjwvc3ZnPg=='">
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                {{-- $this->getFieldLabel($column) --}}
                                                {{ $fieldDefinitions[$column]['label']?? 'Unknown' }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Two Column Layout --}}
                @if(count($textColumns) > 0)
                    <div class="card border-0 shadow-sm me-1">
                        <div class="card-body"> 
                            <div class="row">
                                @php
                                    $midPoint = ceil(count($textColumns) / 2);
                                    $currentIndex = 0;
                                @endphp
                                
                                {{-- First Column --}}
                                <div class="col-md-6">
                                    <div class="pe-md-4">
                                        @foreach($textColumns as $column)
                                            @if($currentIndex < $midPoint)
                                                <div class="field-group mb-4">
                                                    <label class="field-label text-muted small mb-2">
                                                        {{-- $this->getFieldLabel($column) --}}
                                                        {{ $fieldDefinitions[$column]['label']?? 'Unknown' }}
                                                    </label>
                                                    <div class="field-value">
                                                        <x-qf::livewire.bootstrap.data-tables.partials.detail-field
                                                            :column="$column"
                                                            :fieldDefinitions="$fieldDefinitions"
                                                            :selectedItem="$selectedItem"
                                                            :multiSelectFormFields="$multiSelectFormFields"
                                                        />
                                                    </div>
                                                </div>
                                            @endif
                                            @php $currentIndex++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                                
                                {{-- Second Column --}}
                                <div class="col-md-6">
                                    <div class="ps-md-4 border-start-md">
                                        @php $currentIndex = 0; @endphp
                                        @foreach($textColumns as $column)
                                            @if($currentIndex >= $midPoint)
                                                <div class="field-group mb-4">
                                                    <label class="field-label text-muted small mb-2">
                                                        {{-- $this->getFieldLabel($column) --}}
                                                        {{ $fieldDefinitions[$column]['label']?? 'Unknown' }}
                                                    </label>
                                                    <div class="field-value">
                                                        <x-qf::livewire.bootstrap.data-tables.partials.detail-field
                                                            :column="$column"
                                                            :fieldDefinitions="$fieldDefinitions"
                                                            :selectedItem="$selectedItem"
                                                            :multiSelectFormFields="$multiSelectFormFields"
                                                        />
                                                    </div>
                                                </div>
                                            @endif
                                            @php $currentIndex++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <br /> 

                @endif
            @endif

            {{-- Activity Timeline (Optional) --}}
            @if(method_exists($this, 'getActivityTimeline') && $this->getActivityTimeline($selectedItem))
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-history me-2"></i> Activity Timeline</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $activities = $this->getActivityTimeline($selectedItem);
                        @endphp
                        @if(!empty($activities))
                            <div class="timeline">
                                @foreach($activities as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-medium">{{ $activity['title'] }}</span>
                                                <small class="text-muted">{{ $activity['time'] }}</small>
                                            </div>
                                            @if(isset($activity['description']))
                                                <p class="mb-0 text-muted small">{{ $activity['description'] }}</p>
                                            @endif
                                            @if(isset($activity['user']))
                                                <small class="text-muted">By: {{ $activity['user'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No activity recorded yet.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Footer --}}
        {{--  <div class="modal-footer border-top-0 pt-0">
            <div class="d-flex justify-content-between w-100">
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i> Created: {{ $selectedItem->created_at->format('M d, Y') }}
                    @if($selectedItem->updated_at->gt($selectedItem->created_at))
                        • Updated: {{ $selectedItem->updated_at->format('M d, Y') }}
                    @endif
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if(in_array('edit', $simpleActions ?? []))
                        <button type="button" 
                                class="btn btn-primary"
                                {{--wire:click="editRecord({{ $selectedItem->id }}) -- }}
                                
                                >
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                    @endif
                </div>
            </div>
        </div>
        --}}


    @else
        {{-- Empty State --}}
        <div class="modal-body">
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-exclamation-circle fa-3x text-warning"></i>
                </div>
                <h5 class="text-muted mb-2">No Record Selected</h5>
                <p class="text-muted">Please select a record to view details.</p>
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .modal-form {
        --primary-color: #4e73df;
        --success-color: #1cc88a;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --border-color: #e3e6f0;
    }
    
    .modal-header {
        padding: 1.5rem 1.5rem 0.5rem 1.5rem;
    }
    
    .modal-title {
        font-weight: 600;
        color: #2e3a59;
        font-size: 1.25rem;
    }
    
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .quick-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .quick-actions .btn {
        padding: 0.375rem;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .detail-tabs-wrapper {
        margin: 0 -1.5rem;
        padding: 0 1.5rem;
        background: white;
        border-bottom: 1px solid var(--border-color);
    }
    
    .detail-tabs-wrapper .nav-tabs {
        border-bottom: none;
        margin-bottom: -1px;
    }
    
    .detail-tabs-wrapper .nav-link {
        border: none;
        color: #6c757d;
        padding: 0.75rem 1rem;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        margin-bottom: -1px;
        transition: all 0.2s ease;
    }
    
    .detail-tabs-wrapper .nav-link:hover {
        color: var(--primary-color);
        background: transparent;
        border-bottom-color: #dee2e6;
    }
    
    .detail-tabs-wrapper .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom: 3px solid var(--primary-color);
    }
    
    .field-group {
        padding: 0.5rem 0;
    }
    
    .field-label {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    .field-value {
        font-size: 0.95rem;
        line-height: 1.5;
        min-height: 1.5rem;
    }
    
    .badge.bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .badge.bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .badge.bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    .badge.bg-secondary-light { background-color: rgba(133, 135, 150, 0.1); }
    .badge.bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--primary-color);
    }
    
    .timeline-content {
        padding-left: 1rem;
    }
    
    .empty-state-icon {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @media (max-width: 768px) {
        .modal-header {
            padding: 1rem 1rem 0.5rem 1rem;
        }
        
        .avatar-lg {
            width: 50px;
            height: 50px;
        }
        
        .modal-title {
            font-size: 1.1rem;
        }
        
        .detail-tabs-wrapper {
            margin: 0 -1rem;
            padding: 0 1rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .responsive-tab-nav {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-bottom: none !important;
            gap: 0.5rem;
            min-height: 44px;
            align-items: center;
            scrollbar-width: none;
        }
        
        .responsive-tab-nav::-webkit-scrollbar {
            display: none;
        }
        
        .responsive-tab-nav .nav-item {
            flex: 0 0 auto;
        }
        
        .responsive-tab-nav .nav-link {
            white-space: nowrap;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border: 1px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            border-bottom: 3px solid transparent !important;
        }
        
        .responsive-tab-nav .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .field-group {
            margin-bottom: 1rem;
        }
        
        .border-start-md {
            border-left: none !important;
            padding-left: 0 !important;
        }
        
        .col-md-6 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        .ps-md-4, .pe-md-4 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        .quick-actions .btn {
            width: 32px;
            height: 32px;
        }
    }
    
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .col-md-6 {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Make tabs swipeable on mobile
        if ('ontouchstart' in window) {
            const tabContent = document.querySelector('.tab-content');
            if (tabContent) {
                let startX = 0;
                let endX = 0;
                
                tabContent.addEventListener('touchstart', (e) => {
                    startX = e.changedTouches[0].screenX;
                });
                
                tabContent.addEventListener('touchend', (e) => {
                    endX = e.changedTouches[0].screenX;
                    handleSwipe(startX, endX);
                });
                
                function handleSwipe(start, end) {
                    const threshold = 50;
                    const diff = start - end;
                    
                    if (Math.abs(diff) > threshold) {
                        const activeTab = document.querySelector('.nav-link.active');
                        const tabs = document.querySelectorAll('.nav-link');
                        const currentIndex = Array.from(tabs).indexOf(activeTab);
                        
                        if (diff > 0 && currentIndex < tabs.length - 1) {
                            // Swipe left, go to next tab
                            tabs[currentIndex + 1].click();
                        } else if (diff < 0 && currentIndex > 0) {
                            // Swipe right, go to previous tab
                            tabs[currentIndex - 1].click();
                        }
                    }
                }
            }
        }
    });
    
    function openImageModal(src, title) {
        const modalHtml = `
            <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${src}" class="img-fluid rounded" alt="${title}" 
                                 style="max-height: 70vh; object-fit: contain;">
                        </div>
                        <div class="modal-footer">
                            <a href="${src}" download class="btn btn-primary">
                                <i class="fas fa-download me-2"></i> Download
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal
        const existingModal = document.getElementById('imagePreviewModal');
        if (existingModal) existingModal.remove();
        
        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
        
        // Cleanup after modal is hidden
        document.getElementById('imagePreviewModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }
</script>
@endpush


{{----- NOTE THE IFFERENCE WITH core.views::data-tables.modals.modal-header -----}}
@include('qf::components.livewire.bootstrap.data-tables.partials.form-footer', [
    'modalId' => 'detail',
])