{{-- resources/views/components/livewire/bootstrap/data-tables/data-cards.blade.php --}}
@props(['data', 'columns', 'simpleActions', 'model', 'moduleName', 'modelName', 'moreActions'])

<div class="row g-4 py-4">
  @if(!empty($config["switchViews"]) && isset($config["switchViews"]["card"]))
    @include('qf::components.livewire.bootstrap.widgets.spinner')

    @forelse($data as $record)
      @php
        $title = $subtitle = '';
        if (!empty($config['switchViews']['card']['titleFields'])) {
          foreach ($config['switchViews']['card']['titleFields'] as $titleField) {
            $s = $record->$titleField ?? '';
            $title .= ' ' . $s;
          }
          $title = trim($title);
        }

        if (!empty($config['switchViews']['card']['subtitleFields'])) {
          foreach ($config['switchViews']['card']['subtitleFields'] as $subtitleField) {
            $s = $record->$subtitleField ?? '';
            $subtitle .= ' ' . $s;
          }
          $subtitle = trim($subtitle);
        }

        // Get photo field if switchViews.card.imageField is not provided
        $photoField = null;
        foreach (['photo', 'profile_picture', 'avatar', 'image'] as $field) {
          if (isset($record->$field) && $record->$field) {
            $photoField = $field;
            break;
          }
        }

        /////////////////
        if (!$photoField) // Fall back
          $photoField = $record?->employeeProfile?->photo; 
        
        // Get employee details for tooltip (without photo)
        $jobTitle = isset($record->job_title) ? e($record->job_title) : (isset($record->employeePosition?->jobTitle?->title) ? e($record->employeePosition->jobTitle->title) : 'N/A');
        $department = isset($record->department?->name) ? e($record->department->name) : 'N/A';
        $status = isset($record->status) ? e($record->status) : 'Active';
        
        // Status badge color
        $statusColor = match(strtolower($status)) {
          'active' => 'success',
          'terminated', 'inactive' => 'danger',
          default => 'warning'
        };
        
        // Tooltip HTML (without photo - cards already show it)
        $tooltipHtml = "<div class='p-2' style='min-width: 180px;'>
            <div class='fw-bold mb-1'>". e($title) ."</div>
            <div class='text-muted small mb-1'>{$jobTitle}</div>
            <div class='text-muted small mb-2'>{$department}</div>
            <span class='badge bg-{$statusColor}'>{$status}</span>
        </div>";
      @endphp

      <div class="col-md-4 mt-4">
        <div class="card card-profile mt-md-0 mt-5" style="max-height: 600px; min-height: 600px">
          <a href="{{ Str::lower(Str::plural($modelName)) }}/{{ $record->id }}" class="text-decoration-none">
            @if($photoField)
              <div class="p-0">
                <img 
                  src="{{ asset('storage/' . $photoField) }}" 
                  class="card-img-top"
                  style="height: 300px; object-fit: cover;"
                  alt="{{ $title }}"
                  onerror="this.src='{{ asset('images/default-avatar.png') }}'"
                >
              </div>
            @else
              <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                <i class="fas fa-user fa-3x text-secondary"></i>
              </div>
            @endif
          </a>
          
          <div class="card-body blur justify-content-center text-center mx-4 mb-4 border-radius-md">
            {{-- Enhanced name with tooltip --}}
            <h4 class="mb-0">
              <a href="{{ Str::lower(Str::plural($modelName)) }}/{{ $record->id }}" 
                 class="text-reset text-decoration-none employee-name"
                 data-bs-toggle="tooltip"
                 data-bs-html="true"
                 data-bs-title="{{ $tooltipHtml }}"
                 data-bs-placement="top">
                {{ $title }}
              </a>
            </h4>
            
            <p>{{ $subtitle }}</p>
            
            <div class="row justify-content-center text-center">
              <div class="col-12 mx-auto">
                @if (!empty($config['switchViews']['card']['contentFields']))
                  @foreach ($config['switchViews']['card']['contentFields'] as $contentField)
                    <h5 class="text-info mb-0">{{ $this->formatFieldValue($record, $contentField) }}</h5>
                    <small>{{ Str::title(str_replace('_', ' ', $contentField)) }}</small>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="text-center py-5">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <h5>No records found</h5>
          <p class="text-muted">Get started by adding your first record.</p>
          @if(in_array('create', $controls ?? []))
            <button 
              wire:click="$dispatch('openModal', {component: 'qf::data-tables.data-table-manager', arguments: {isEditMode: false}})" 
              class="btn btn-primary"
            >
              + Add {{ $modelName }}
            </button>
          @endif
        </div>
      </div>
    @endforelse

    @include('qf::components.livewire.bootstrap.data-tables.partials.table-footer', ["data" => $data])
  @endif


{{-- Enhanced Tooltip Initialization for Cards --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth >= 768) {
      Livewire.hook('message.processed', (message, component) => {
        initializeEnhancedCardTooltips();
      });
      initializeEnhancedCardTooltips();
    }
  });

  function initializeEnhancedCardTooltips() {
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

{{-- Reuse the same tooltip styling from list/datatable --}}
<style>
  .employee-tooltip .tooltip-inner {
    background: white !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    border-radius: 0.5rem;
    padding: 0 !important;
    min-width: 180px;
  }
  
  .employee-tooltip {
    z-index: 1080 !important;
  }
</style>



</div>