{{-- resources/views/components/livewire/bootstrap/data-tables/enhanced-data-detail.blade.php --}}
@props([
    'config' => [],
    'hiddenFields' => [],
])

@php
    $fieldGroups = $config['fieldGroups'] ?? [];
    $layout = $config['switchViews']['detail']['layout'] ?? 'hr';
    $isMobile = request()->header('User-Agent') && preg_match('/(mobile|android|iphone|ipad)/i', request()->header('User-Agent'));
    $record = null;
    
    if ($selectedItemId)
        $record = $model::find($selectedItemId);
    
    // Get title and subtitle from config
    $titleFields = $config['switchViews']['detail']['titleFields'] ?? [];
    $subtitleFields = $config['switchViews']['detail']['subtitleFields'] ?? [];
    
    $title = '';
    $subtitle = '';
    
    if ($record && !empty($titleFields)) {
        foreach ($titleFields as $field) {
            $s = data_get($record, $field, '');
            $title .= ' ' . $s;
        }
        $title = trim($title);
    }
    
    if ($record && !empty($subtitleFields)) {
        foreach ($subtitleFields as $field) {
            $s = data_get($record, $field, '');
            $subtitle .= ($subtitle ? ' • ' : '') . $s;
        }
        $subtitle = trim($subtitle);
    }
@endphp

<div class="detail-view">
  @if ($record)
    {{-- Modern Header Section --}}
    {{--<div class="card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div>
            <h2 class="h4 mb-2">{{ $title }}</h2>
            @if($subtitle)
              <p class="text-muted mb-0">{{ $subtitle }}</p>
            @endif
          </div>
          <div class="d-flex gap-2">
            <button wire:click="editRecord({{ $record->id }}, '{{ addslashes($model) }}')"
                    class="btn btn-outline-primary btn-sm">
              <i class="fas fa-edit me-1"></i> Edit
            </button>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-print me-1"></i> Print
            </button>
          </div>
        </div>
        
        {{-- Status Badges -- }}
        @php
            $badgeFields = ['status', 'is_approved', 'needs_review', 'is_active'];
            $badgeLabels = [
                'status' => 'Status',
                'is_approved' => 'Approval',
                'needs_review' => 'Review',
                'is_active' => 'Active'
            ];
        @endphp
        <div class="d-flex flex-wrap gap-2">
          @foreach($badgeFields as $badgeField)
            @if(isset($record->$badgeField) && $record->$badgeField !== null)
              @php
                $value = $record->$badgeField;
                $isBool = is_bool($value);
                $displayValue = $isBool ? ($value ? 'Yes' : 'No') : ucfirst($value);
                
                // Determine badge color
                $colorClass = 'bg-secondary';
                if ($isBool) {
                  $colorClass = $value ? 'bg-success' : 'bg-warning';
                } elseif (in_array(strtolower($value), ['approved', 'active', 'success'])) {
                  $colorClass = 'bg-success';
                } elseif (in_array(strtolower($value), ['pending', 'warning'])) {
                  $colorClass = 'bg-warning';
                } elseif (in_array(strtolower($value), ['rejected', 'cancelled', 'danger'])) {
                  $colorClass = 'bg-danger';
                }
              @endphp
              <span class="badge {{ $colorClass }}">
                {{ $badgeLabels[$badgeField] ?? ucfirst(str_replace('_', ' ', $badgeField)) }}: {{ $displayValue }}
              </span>
            @endif
          @endforeach
        </div>
      </div>
    </div> --}}

    {{-- TAB LAYOUT --}}
    @if($layout == 'tab')
      <div class="detail-tabs-wrapper mb-4">
        <ul class="nav nav-tabs responsive-tab-nav" id="detailTabs" role="tablist">
          @foreach($fieldGroups as $group)
            @php
              $groupTitle = $group['title'] ?? '';
              $fields = $group['fields'] ?? [];
              $isGroupEmpty = $this->isGroupEmptyForDetail('hr', $fields, $hiddenFields);
            @endphp
            @if(!$isGroupEmpty)
              <li class="nav-item" role="presentation">
                <button 
                  class="nav-link {{ $loop->first ? 'active' : '' }}" 
                  data-bs-toggle="tab" 
                  data-bs-target="#{{ Str::slug($groupTitle) }}"
                  role="tab"
                  aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                  @if(isset($group['icon']))
                    <i class="{{ $group['icon'] }} me-2"></i>
                  @endif
                  {{ $groupTitle }}
                </button>
              </li>
            @endif
          @endforeach
        </ul>
      </div>

      <div class="tab-content">
        @foreach($fieldGroups as $group)
          @php
            $groupTitle = $group['title'] ?? '';
            $groupType = $group['groupType'] ?? 'hr';
            $fields = $group['fields'] ?? [];
            $isGroupEmpty = $this->isGroupEmptyForDetail($groupType, $fields, $hiddenFields);
          @endphp
          @if(!$isGroupEmpty)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                id="{{ Str::slug($groupTitle) }}" 
                role="tabpanel">
              <div class="card border-0 shadow-sm">
                <div class="card-body">
                  <div class="row">
                    @foreach($fields as $field)
                      @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                        <div class="col-md-6 col-lg-4 mb-4">
                          <div class="field-item">
                            <div class="field-label text-muted small mb-1">
                              {{ $this->getFieldLabel($field) }}
                            </div>
                            <div class="field-value">
                              @php
                                $relationData = $this->getRelationData($record, $field);
                                $relationValue = $relationData["relationship"] ?? null;
                              @endphp
                              
                              @if($relationData)
                                @if(isset($relationValue) && $this->isEmployeeRelation($relationValue))
                                  @if($isMobile)
                                    <span>{{ $this->getRelationDisplayValue($relationData) }}</span>
                                  @else
                                    <a href="/hr/employees/{{ $relationValue->id }}" 
                                      class="text-reset text-decoration-none employee-name"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-title="{{ $this->getEmployeeTooltipHtml($relationValue) }}"
                                      data-bs-placement="top">
                                      {{ $this->getRelationDisplayValue($relationData) }}
                                    </a>
                                  @endif
                                @else
                                  {{ $this->getRelationValue($relationData) }}
                                @endif
                              @else
                                @php
                                  $value = $this->formatFieldValue($record, $field);
                                @endphp
                                @if(is_bool($value))
                                  <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $value ? 'Yes' : 'No' }}
                                  </span>
                                @elseif(filter_var($value, FILTER_VALIDATE_EMAIL))
                                  <a href="mailto:{{ $value }}" class="text-decoration-none">
                                    {{ $value }}
                                  </a>
                                @else
                                  <span class="fw-medium">{{ $value }}</span>
                                @endif
                              @endif
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>

    {{-- NON-TAB LAYOUTS (hr/accordion) --}}
    @else
      @foreach($fieldGroups as $group)
        @php
          $groupTitle = $group['title'] ?? '';
          $groupType = $group['groupType'] ?? 'hr';
          $fields = $group['fields'] ?? [];
          $isGroupEmpty = $this->isGroupEmptyForDetail($groupType, $fields, $hiddenFields);
        @endphp

        @if(!$isGroupEmpty)
          @if($groupType == 'hr')
            <div class="card mb-4 border-0 shadow-sm">
              <div class="card-header bg-light">
                <h6 class="mb-0">{{ $groupTitle }}</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  @foreach($fields as $field)
                    @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                      <div class="col-md-6 col-lg-4 mb-4">
                        <div class="field-item">
                          <div class="field-label text-muted small mb-1">
                            {{ $this->getFieldLabel($field) }}
                          </div>
                          <div class="field-value">
                            @if($this->hasRelation($field))
                              @php
                                $relationValue = $this->getRelationValueObject($field);
                              @endphp
                              
                              @if($relationValue && $this->isEmployeeRelation($field))
                                @if($isMobile)
                                  <span>{{ $this->getRelationDisplayValue($field) }}</span>
                                @else
                                  <a href="{{ route('hr.employees.show', $relationValue->id) }}" 
                                    class="text-reset text-decoration-none employee-name"
                                    data-bs-toggle="tooltip"
                                    data-bs-html="true"
                                    data-bs-title="{{ $this->getEmployeeTooltipHtml($relationValue) }}"
                                    data-bs-placement="top">
                                    {{ $this->getRelationDisplayValue($field) }}
                                  </a>
                                @endif
                              @else
                                {{ $this->getRelationValue($field) }}
                              @endif
                            @else
                              @php
                                $value = $this->formatFieldValue($field);
                              @endphp
                              @if(is_bool($value))
                                <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                  {{ $value ? 'Yes' : 'No' }}
                                </span>
                              @else
                                <span class="fw-medium">{{ $value }}</span>
                              @endif
                            @endif
                          </div>
                        </div>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
            </div>

          @elseif($groupType == 'accordion')
            <div class="card mb-4 border-0 shadow-sm">
              <div class="card-header bg-light cursor-pointer" 
                  data-bs-toggle="collapse" 
                  data-bs-target="#detail-group-{{ Str::slug($groupTitle) }}"
                  aria-expanded="false">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">{{ $groupTitle }}</h6>
                  <i class="fas fa-chevron-down transition-rotate"></i>
                </div>
              </div>
              <div class="collapse show" id="detail-group-{{ Str::slug($groupTitle) }}">
                <div class="card-body">
                  <div class="row">
                    @foreach($fields as $field)
                      @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                        <div class="col-md-6 col-lg-4 mb-4">
                          <div class="field-item">
                            <div class="field-label text-muted small mb-1">
                              {{ $this->getFieldLabel($field) }}
                            </div>
                            <div class="field-value">
                              @if($this->hasRelation($field))
                                @php
                                  $relationValue = $this->getRelationValueObject($field);
                                @endphp
                                
                                @if($relationValue && $this->isEmployeeRelation($field))
                                  @if($isMobile)
                                    <span>{{ $this->getRelationDisplayValue($field) }}</span>
                                  @else
                                    <a href="{{ route('hr.employees.show', $relationValue->id) }}" 
                                      class="text-reset text-decoration-none employee-name"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-title="{{ $this->getEmployeeTooltipHtml($relationValue) }}"
                                      data-bs-placement="top">
                                      {{ $this->getRelationDisplayValue($field) }}
                                    </a>
                                  @endif
                                @else
                                  {{ $this->getRelationValue($field) }}
                                @endif
                              @else
                                @php
                                  $value = $this->formatFieldValue($field);
                                @endphp
                                @if(is_bool($value))
                                  <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $value ? 'Yes' : 'No' }}
                                  </span>
                                @else
                                  <span class="fw-medium">{{ $value }}</span>
                                @endif
                              @endif
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endif
      @endforeach
    @endif
    
    {{-- Activity Timeline (Optional Enhancement) --}}
    @if(method_exists($this, 'getActivityTimeline') && $this->getActivityTimeline($record))
      <div class="card mt-4 border-0 shadow-sm">
        <div class="card-header bg-light">
          <h6 class="mb-0"><i class="fas fa-history me-2"></i> Activity Timeline</h6>
        </div>
        <div class="card-body">
          @php
            $activities = $this->getActivityTimeline($record);
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
  @else
    <div class="text-center py-8">
      <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
      <h5 class="text-muted">Record not found</h5>
      <p class="text-muted">The record you're looking for doesn't exist or has been deleted.</p>
      <button wire:click="$dispatch('closeModal')" class="btn btn-primary">
        <i class="fas fa-arrow-left me-2"></i> Go Back
      </button>
    </div>
  @endif
</div>

@push('styles')
<style>
  .detail-view {
    --primary-color: #4e73df;
    --success-color: #1cc88a;
    --warning-color: #f6c23e;
    --danger-color: #e74a3b;
    --secondary-color: #858796;
  }
  
  .detail-tabs-wrapper {
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #e3e6f0;
  }
  
  .detail-tabs-wrapper .nav-tabs {
    border-bottom: none;
  }
  
  .detail-tabs-wrapper .nav-link {
    color: var(--secondary-color);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
  }
  
  .detail-tabs-wrapper .nav-link:hover {
    color: var(--primary-color);
    border-bottom-color: #dee2e6;
  }
  
  .detail-tabs-wrapper .nav-link.active {
    color: var(--primary-color);
    background: transparent;
    border-bottom: 3px solid var(--primary-color);
  }
  
  .field-item {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s;
  }
  
  .field-item:hover {
    background-color: rgba(78, 115, 223, 0.05);
  }
  
  .field-label {
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
  }
  
  .field-value {
    font-size: 0.95rem;
    min-height: 1.5rem;
    word-break: break-word;
  }
  
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
    background: #e3e6f0;
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
  
  .transition-rotate {
    transition: transform 0.3s ease;
  }
  
  .collapsed .transition-rotate {
    transform: rotate(180deg);
  }
  
  /* Responsive Design */
  @media (max-width: 767.98px) {
    .detail-tabs-wrapper {
      position: sticky;
      top: 0;
      background: white;
      z-index: 1020;
      padding: 0.5rem 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
    
    .field-item {
      margin-bottom: 1rem;
    }
    
    .card-body .row > div {
      width: 100%;
      flex: 0 0 100%;
      max-width: 100%;
    }
  }
  
  @media (min-width: 768px) and (max-width: 991.98px) {
    .col-md-6 {
      width: 50%;
      flex: 0 0 50%;
      max-width: 50%;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Only initialize tooltips on desktop
    if (window.innerWidth >= 768) {
      Livewire.hook('message.processed', (message, component) => {
        initializeDetailTooltips();
      });
      initializeDetailTooltips();
    }
    
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
    
    // Initialize accordion transitions
    const accordionHeaders = document.querySelectorAll('[data-bs-toggle="collapse"]');
    accordionHeaders.forEach(header => {
      header.addEventListener('click', function() {
        const chevron = this.querySelector('.transition-rotate');
        if (chevron) {
          const isExpanded = this.getAttribute('aria-expanded') === 'true';
          chevron.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
        }
      });
    });
  });
  
  function initializeDetailTooltips() {
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
        container: 'body',
        customClass: 'employee-tooltip'
      });
    });
  }
</script>
@endpush