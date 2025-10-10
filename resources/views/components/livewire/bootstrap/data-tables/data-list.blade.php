{{-- resources/views/components/livewire/bootstrap/data-tables/data-list.blade.php --}}
@props(['data', 'columns', 'simpleActions', 'model', 'moduleName', 'modelName', 'moreActions'])

<div class="py-4">

  @if (!empty($config['switchViews']) && isset($config['switchViews']['list']))
    @include('qf::components.livewire.bootstrap.widgets.spinner')

    <div class="list-group">
      @forelse($data as $record)
        @php
          $title = '';
          if (!empty($config['switchViews']['list']['titleFields'])) {
            foreach ($config['switchViews']['list']['titleFields'] as $titleField) {
              $s = $record->$titleField ?? '';
              $title .= ' ' . $s;
            }
            $title = trim($title);
          }
          
          // Get photo URL
          $photoUrl = null;
          if (isset($record->photo) && $record->photo) {
            $photoUrl = asset('storage/' . $record->photo);
          } elseif (isset($record->employeeProfile?->photo) && $record->employeeProfile?->photo) {
            $photoUrl = asset('storage/' . $record->employeeProfile->photo);
          } elseif (isset($record->user?->photo) && $record->user?->photo) {
            $photoUrl = asset('storage/' . $record->user->photo);
          } else {
            // Fallback avatar
            $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($title) . '&background=4e73df&color=fff&size=80';
          }
          
          // Get employee details for tooltip
          $fullName = e($title);
          $jobTitle = isset($record->job_title) ? e($record->job_title) : (isset($record->employeePosition?->jobTitle?->title) ? e($record->employeePosition->jobTitle->title) : 'N/A');
          $department = isset($record->department?->name) ? e($record->department->name) : 'N/A';
          $status = isset($record->status) ? e($record->status) : 'Active';


            // Show email instead of department
            // $email = isset($record->email) ? e($record->email) : 'N/A';
            // Replace department line with:
            // <div class='text-muted small mb-2'>{$email}</div>

            // $hireDate = isset($record->hire_date) ? e($record->hire_date->format('M Y')) : 'N/A';
            // Add to tooltip:
            // <div class='text-muted small'>Hired: {$hireDate}</div>

          
          // Status badge color
          $statusColor = match(strtolower($status)) {
            'active' => 'success',
            'terminated', 'inactive' => 'danger',
            default => 'warning'
          };
          
          // Enhanced tooltip HTML
          $tooltipHtml = "<div class='text-center p-2' style='min-width: 200px;'>
              <img src='{$photoUrl}' loading='lazy' class='rounded mb-2' style='width: 80px; height: 80px; object-fit: cover;'>
              <div class='fw-bold mb-1'>{$fullName}</div>
              <div class='text-muted small mb-1'>{$jobTitle}</div>
              <div class='text-muted small mb-2'>{$department}</div>
              <span class='badge bg-{$statusColor}'>{$status}</span>
          </div>";
        @endphp

        <div class="list-group-item border-0 d-flex p-4 m-2 bg-gray-100 border-radius-lg">
          <div class="d-flex flex-column">
            {{-- Title with enhanced hover tooltip --}}
            <h6 class="mb-3 text-sm">
              {{-- Desktop: Hover tooltip with enhanced details --}}
              <span class="d-none d-md-inline position-relative">
                <a href="/{{$moduleName}}/{{ Str::lower(Str::plural($modelName)) }}/{{ $record->id }}" 
                   class="text-reset text-decoration-none employee-name"
                   data-bs-toggle="tooltip"
                   data-bs-html="true"
                   data-bs-title="{{ $tooltipHtml }}"
                   data-bs-placement="right">
                  {{ $title }}
                </a>
              </span>
              
              {{-- Mobile: Just the link (no tooltip) --}}
              <span class="d-md-none">
                <a href="/{{$moduleName}}/{{ Str::lower(Str::plural($modelName)) }}/{{ $record->id }}" 
                   class="text-reset text-decoration-none">
                  {{ $title }}
                </a>
              </span>
            </h6>
            
            @if (!empty($config['switchViews']['list']['contentFields']))
              @foreach ($config['switchViews']['list']['contentFields'] as $contentField)
                <span class="mb-2 text-xs">
                  {{ Str::title(str_replace('_', ' ', $contentField)) }}:
                  <span class="text-dark font-weight-bold ms-sm-2">
                    {{ $record->$contentField ?? '' }}
                  </span>
                </span>
              @endforeach
            @endif
          </div>

          {{-- Responsive Actions --}}
          <div class="ms-auto text-end">
            @if ($simpleActions)
              {{-- Desktop: Inline Buttons --}}
              <div class="d-none d-md-flex gap-2">
                @foreach ($simpleActions as $action)
                  @if (strtolower($action) == 'edit')
                    <button type="button" 
                            wire:click="editRecord({{ $record->id }}, '{{ addslashes($model) }}')"
                            wire:click.stop
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="Edit">
                      <i class="fas fa-pencil-alt"></i>
                    </button>
                  @elseif(strtolower($action) == 'delete')
                    <button type="button"
                            wire:click="deleteRecord({{ $record->id }})"
                            wire:click.stop
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="Delete">
                      <i class="far fa-trash-alt"></i>
                    </button>
                  @endif
                @endforeach
              </div>

              {{-- Mobile: Dropdown --}}
              <div class="d-md-none">
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary" 
                          type="button" 
                          data-bs-toggle="dropdown" 
                          aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    @foreach ($simpleActions as $action)
                      @if (strtolower($action) == 'edit')
                        <li>
                          <a class="dropdown-item" 
                             href="#" 
                             wire:click="editRecord({{ $record->id }}, '{{ addslashes($model) }}')"
                             wire:click.stop>
                            <i class="fas fa-pencil-alt me-2"></i> Edit
                          </a>
                        </li>
                      @elseif(strtolower($action) == 'delete')
                        <li>
                          <a class="dropdown-item text-danger" 
                             href="#" 
                             wire:click="deleteRecord({{ $record->id }})"
                             wire:click.stop>
                            <i class="far fa-trash-alt me-2"></i> Delete
                          </a>
                        </li>
                      @endif
                    @endforeach
                  </ul>
                </div>
              </div>
            @endif
          </div>
        </div>

      @empty
        <div class="text-center py-5">
          <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
          <p class="text-muted">No records available.</p>
        </div>
      @endforelse
    </div>

    @include('qf::components.livewire.bootstrap.data-tables.partials.table-footer', ['data' => $data])
  @endif






{{-- Enhanced Tooltip Initialization --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth >= 768) {
      Livewire.hook('message.processed', (message, component) => {
        initializeEnhancedListTooltips();
      });
      initializeEnhancedListTooltips();
    }
  });

  function initializeEnhancedListTooltips() {
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
        placement: 'right',
        delay: { show: 250, hide: 150 },
        container: 'body',
        customClass: 'employee-tooltip'
      });
    });
  }
</script>

<style>
  /* Enhanced tooltip styling (same as datatable) */
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
</style>


</div>