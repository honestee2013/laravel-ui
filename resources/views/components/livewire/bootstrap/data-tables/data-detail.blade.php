{{-- resources/views/components/livewire/bootstrap/data-tables/data-detail.blade.php --}}
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


@endphp

<div class="detail-view">
  @if ($record)

      {{-- TAB LAYOUT --}}
      @if($layout == 'tab')
        <div class="detail-tabs-wrapper">
          <ul class="nav nav-tabs responsive-tab-nav" id="profileTabs" role="tablist">
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
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    id="tab-{{ Str::slug($groupTitle) }}-tab">
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
                  role="tabpanel" 
                  aria-labelledby="tab-{{ Str::slug($groupTitle) }}-tab">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      @foreach($fields as $field)
                        @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                          <div class="col-md-6 mb-3">
                            <dt class="text-sm text-secondary"><strong class="text-dark fw-semibold">{{ $this->getFieldLabel($field) }}</strong></dt>
                            <dd class="mb-0">
                              @php
                                $relationData = $this->getRelationData($record, $field);
                                $relationValue = $relationData["relationship"]?? null;
                              @endphp
                              
                              @if($relationData )
                                @if(isset($relationValue) && $this->isEmployeeRelation($relationValue))
                                  @if($isMobile)
                                    {{-- Mobile: plain text --}}
                                    <span>{{ $this->getRelationDisplayValue($relationData) }}</span>
                                  @else

                                    {{-- Desktop: with tooltip --}}
                                    <a href="/hr/{{ $relationValue->id }}" 
                                      class="text-reset text-decoration-none employee-name"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-title="{{ $this->getEmployeeTooltipHtml($relationValue) }}"
                                      data-bs-placement="top">
                                      {{ $this->getRelationDisplayValue($relationData) }}
                                    </a>
                                  @endif
                                @else
                                  {{-- Regular relation --}}
                                  {{ $this->getRelationValue($relationData) }}
                                @endif
                              @else
                                <span class="text-break"> {{ $this->formatFieldValue($record, $field) }} </span>
                              @endif
                            </dd>
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
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <h6 class="mb-0">{{ $groupTitle }}</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    @foreach($fields as $field)
                      @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                        <div class="col-md-6 mb-3">
                          <dt class="text-sm text-secondary">{{ $this->getFieldLabel($field) }}</dt>
                          <dd class="mb-0">
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
                              {{ $this->formatFieldValue($field) }}
                            @endif
                          </dd>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>

            @elseif($groupType == 'accordion')
              <div class="card mb-4">
                <div class="card-header bg-light cursor-pointer" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#detail-group-{{ Str::slug($groupTitle) }}"
                    aria-expanded="false">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $groupTitle }}</h6>
                    <i class="fas fa-chevron-down chevron-rotate"></i>
                  </div>
                </div>
                <div class="collapse" id="detail-group-{{ Str::slug($groupTitle) }}">
                  <div class="card-body">
                    <div class="row">
                      @foreach($fields as $field)
                        @if($this->shouldDisplayFieldInDetail($field, $hiddenFields))
                          <div class="col-md-6 mb-3">
                            <dt class="text-sm text-secondary">{{ $this->getFieldLabel($field) }}</dt>
                            <dd class="mb-0">
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
                                {{ $this->formatFieldValue($field) }}
                              @endif
                            </dd>
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
  @endif
</div>

{{-- Push styles to main layout --}}
@push('styles')
<style>
  .employee-tooltip .tooltip-inner {
    background: white !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    border-radius: 0.5rem;
    padding: 0 !important;
    min-width: 200px;
  }
  
  .employee-tooltip {
    z-index: 1080 !important;
  }

  .detail-tabs-wrapper {
    margin-bottom: 1.25rem;
  }

  .responsive-tab-nav {
    /* Desktop styles inherited from Bootstrap */
  }

  @media (max-width: 767.98px) {
    :root {
      --navbar-height: 56px;
      --profile-header-height: 60px;
    }
    
    .detail-tabs-wrapper {
      position: sticky;
      top: calc(var(--navbar-height) + var(--profile-header-height));
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
      touch-action: manipulation;
      border: 1px solid var(--bs-primary, #0d6efd);
      background: transparent;
      color: var(--bs-primary, #0d6efd);
    }

    .responsive-tab-nav .nav-link.active {
      background-color: var(--bs-primary, #0d6efd);
      color: white;
      border-color: var(--bs-primary, #0d6efd);
    }

    /* Chevron rotation */
    [aria-expanded="true"] .chevron-rotate {
      transform: rotate(180deg);
      transition: transform 0.2s ease;
    }

    [aria-expanded="false"] .chevron-rotate {
      transform: rotate(0deg);
      transition: transform 0.2s ease;
    }
  }
</style>
@endpush

{{-- Push scripts to main layout --}}
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