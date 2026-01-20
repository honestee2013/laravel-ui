{{-- resources/views/components/livewire/bootstrap/data-tables/enhanced-data-list.blade.php --}}
@props([
    'data' => [],
    'config' => [],
    'modelName' => '',
    'moduleName' => '',
    'simpleActions' => ['edit', 'delete'],
    'viewType' => 'list',
    'emptyState' => [
        'icon' => 'fas fa-inbox',
        'title' => 'No records found',
        'description' => 'Get started by adding your first record.',
        'action' => null,
    ]
])

@php
    $viewConfig = $config['switchViews'][$viewType] ?? $config['switchViews']['list'] ?? [];
    $titleFields = $viewConfig['titleFields'] ?? [];
    $subtitleFields = $viewConfig['subtitleFields'] ?? [];
    $contentFields = $viewConfig['contentFields'] ?? [];
    $badgeField = $viewConfig['badgeField'] ?? null;
    $badgeColors = $viewConfig['badgeColors'] ?? [];
    $imageField = $viewConfig['imageField'] ?? null;
    $statusField = $viewConfig['statusField'] ?? 'status';
    
    // Modern color palette
    $statusColors = [
        'active' => 'bg-success-subtle text-success',
        'approved' => 'bg-success-subtle text-success',
        'confirmed' => 'bg-success-subtle text-success',
        'success' => 'bg-success-subtle text-success',
        'pending' => 'bg-warning-subtle text-warning',
        'needs_review' => 'bg-danger-subtle text-danger',
        'cancelled' => 'bg-danger-subtle text-danger',
        'rejected' => 'bg-danger-subtle text-danger',
        'draft' => 'bg-secondary-subtle text-secondary',
        'inactive' => 'bg-secondary-subtle text-secondary',
    ];
@endphp

<div class="space-y-3">
    @forelse($data as $record)
        @php
            // Extract title
            $title = '';
            foreach ($titleFields as $field) {
                $value = data_get($record, $field);
                if ($value) {
                    $title .= ($title ? ' ' : '') . $value;
                }
            }
            $title = trim($title);
            
            // Extract subtitle
            $subtitle = '';
            foreach ($subtitleFields as $field) {
                //dd($this->formatFieldValue($record, $field), $field);
                //$value = data_get($record, $field);
                //if ($value) {
                    $subtitle .= ($subtitle ? ' • ' : '') . $this->formatFieldValue($record, $field);
                //}
            }

            // Get badge value and color
            $badgeValue = $badgeField ? data_get($record, $badgeField) : null;
            $badgeClass = '';
            if ($badgeValue !== null) {
                $lookupKey = is_bool($badgeValue) ? ($badgeValue ? 'true' : 'false') : (string) $badgeValue;
                $color = $badgeColors[$lookupKey] ?? 
                        $statusColors[strtolower($lookupKey)] ?? 
                        'bg-secondary-subtle text-secondary';
                $badgeClass = 'bg-' . $color . '-subtle text-' . $color;
            }
            
            // Get photo/avatar
            $avatarUrl = $this->getAvatarUrl($record, $imageField);
        @endphp

        <div class="card card-hover border-1 p-2  mt-4 mb-4 bg-gray-100 ">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    {{-- Avatar --}}
                    @if($avatarUrl)
                        <div class="avatar avatar-lg me-3">
                            <img src="{{ $avatarUrl }}" 
                                 class="rounded-circle" 
                                 alt="{{ $title }}"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($title) }}&background=4e73df&color=fff'">
                        </div>
                    @endif

                    <div class="flex-grow-1">
                        {{-- Title and Badge --}}
                        <div class="d-flex align-items-center mb-1">
                            <h6 class="mb-0 me-2">
                                <a href="{{ url($moduleName . '/' . Str::plural($modelName) . '/' . $record->id) }}" 
                                   class="text-reset text-decoration-none">
                                    {{ $title }}
                                </a>
                            </h6>
                            @if($badgeClass && $badgeValue !== null)
                             : <span class="text-sm mx-2">{{ ucfirst(string: str_replace("_", " ", $badgeField)) }}? </span>
                                <span class="badge {{ $badgeClass }} rounded-pill">
                                    {{ is_bool($badgeValue) ? ($badgeValue ? 'Yes' : 'No') : ucfirst($badgeValue) }}
                                </span>
                            @endif
                        </div>
                        
                        {{-- Subtitle --}}
                        @if($subtitle)
                            <p class="text-muted small mb-2">{{ $subtitle }}</p>
                        @endif
                        
                        {{-- Content Fields --}}
                        @if(!empty($contentFields))
                            <div class="d-flex flex-wrap gap-3 mt-2">
                                @foreach($contentFields as $field)
                                    @php
                                        $label = str_replace('_', ' ', ucfirst($field));
                                        $value = $this->formatFieldValue($record, $field);
                                    @endphp
                                    @if($value)
                                        <div class="d-flex align-items-center">
                                            <span class="text-muted small me-1">{{ $label }}:</span>
                                            <span class="small fw-semibold">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    @if(!empty($simpleActions))
                        <div class="dropdown">
                            <button class="btn btn-link text-muted" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end px-2 py-3">
                                <li class="mb-2">
                                    <a class="dropdown-item border-radius-md" 
                                       href="{{ strtolower( url($moduleName . '/' . Str::plural($modelName) . '/' . $record->id) ) }}">
                                        <i class="fas fa-eye me-2"></i> View Details
                                    </a>
                                </li>
                                @if(in_array('edit', $simpleActions))
                                  <li class="mb-2">
                                    <a class="dropdown-item border-radius-md" 
                                           href="#" 
                                           wire:click="editRecord({{ $record->id }}, '{{ addslashes($model) }}')">
                                            <i class="fas fa-edit me-2"></i> Edit
                                        </a>
                                    </li>
                                @endif
                                @if(in_array('delete', $simpleActions))
                                    <li><hr class="dropdown-divider"></li>
                                  <li class="mb-2">
                                        <a class="dropdown-item border-radius-md text-danger" 
                                           href="#" 
                                           wire:click="deleteRecord({{ $record->id }})">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <div class="text-center py-8">
            <div class="mb-4">
                <i class="{{ $emptyState['icon'] ?? 'fas fa-inbox' }} fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted mb-2">{{ $emptyState['title'] }}</h5>
            <p class="text-muted mb-4">{{ $emptyState['description'] }}</p>
            @if($emptyState['action'] ?? false)
                <button class="btn btn-primary" wire:click="{{ $emptyState['action'] }}">
                    <i class="fas fa-plus me-2"></i> Add New Record
                </button>
            @endif
        </div>
    @endforelse







<style>
    .card-hover:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
    
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
    .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
    
    .avatar {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

</div>
