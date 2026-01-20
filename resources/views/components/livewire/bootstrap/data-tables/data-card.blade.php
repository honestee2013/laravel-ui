{{-- resources/views/components/livewire/bootstrap/data-tables/enhanced-data-cards.blade.php --}}
@props([
    'data' => [],
    'config' => [],
    'modelName' => '',
    'moduleName' => '',
    'viewType' => 'card',
    'columns' => 3,
])

@php
    $viewConfig = $config['switchViews'][$viewType] ?? ($config['switchViews']['card'] ?? []);
    $titleFields = $viewConfig['titleFields'] ?? [];
    $subtitleFields = $viewConfig['subtitleFields'] ?? [];
    $contentFields = $viewConfig['contentFields'] ?? [];
    $badgeField = $viewConfig['badgeField'] ?? null;
    $badgeColors = $viewConfig['badgeColors'] ?? [];
    $ribbonField = $viewConfig['ribbonField'] ?? null;
    $ribbonText = $viewConfig['ribbonText'] ?? '';
    $ribbonColor = $viewConfig['ribbonColor'] ?? 'warning';
    $imageField = $viewConfig['imageField'] ?? null;

    $gridClass = match ($columns) {
        2 => 'col-lg-6',
        4 => 'col-lg-3',
        default => 'col-lg-4 col-md-6',
    };
@endphp

<div class="row g-4">
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
                $value = data_get($record, $field);
                if ($value) {
                    $subtitle .= ($subtitle ? ' • ' : '') . $this->formatFieldValue($record, $field);
                }
            }

            // Get badge value and color
            $badgeValue = $badgeField ? data_get($record, $badgeField) : null;
            $badgeClass = '';
            if ($badgeValue !== null) {
                $lookupKey = is_bool($badgeValue) ? ($badgeValue ? 'true' : 'false') : (string) $badgeValue;
                $color = $badgeColors[$lookupKey] ?? ($this->getBadgeColor($lookupKey, $badgeField) ?? 'secondary');
                $badgeClass = 'bg-' . $color . '-subtle text-' . $color;
            }

            // Check for ribbon
            $showRibbon = $ribbonField && data_get($record, $ribbonField);

            // Get image
            $imageUrl = $this->getImageUrl($record, $imageField);
        @endphp

        <div class="{{ $gridClass }}">
            <div class="card card-hover h-100 border-0 shadow-sm overflow-hidden">
                {{-- Ribbon --}}
                @if ($showRibbon)
                    <div class="ribbon ribbon-{{ $ribbonColor }}">{{ $ribbonText }}</div>
                @endif

                {{-- Card Image --}}
                @if ($imageUrl)
                    <div class="card-img-top position-relative" style="height: 180px; overflow: hidden;">
                        <img src="{{ $imageUrl }}" class="w-100 h-100 object-fit-cover" alt="{{ $title }}"
                            onerror="this.style.display='none'; this.parentElement.classList.add('bg-light')">
                    </div>
                @endif

                <div class="card-body p-4">
                    {{-- Title and Badge --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">
                            <a href="{{ url($moduleName . '/' . Str::plural($modelName) . '/' . $record->id) }}"
                                class="text-reset text-decoration-none">
                                {{ $title }}
                            </a>
                        </h6>
                        @if ($badgeClass && $badgeValue !== null)
                            <div class="m-0 p-0">
                                <span class="text-sm m-0 p-0">{{ ucfirst(string: str_replace('_', ' ', $badgeField)) }}?
                                </span>
                                <span class="badge {{ $badgeClass }} rounded-pill ">
                                    {{-- is_bool($badgeValue) ? ($badgeValue ? '✓' : '✗') : $badgeValue --}}
                                    {{ is_bool($badgeValue) ? ($badgeValue ? 'YES' : 'NO') : $badgeValue }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Subtitle --}}
                    @if ($subtitle)
                        <p class="text-muted small mb-3">{{ $subtitle }}</p>
                    @endif

                    {{-- Content Fields --}}
                    @if (!empty($contentFields))
                        <div class="mt-3 pt-3 border-top">
                            @foreach ($contentFields as $field)
                                @php
                                    $label = str_replace('_', ' ', ucfirst($field));
                                    $value = $this->formatFieldValue($record, $field);
                                @endphp
                                @if ($value)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">{{ $label }}</span>
                                        <span class="small fw-semibold">{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Card Footer with Actions --}}
                <div class="card-footer bg-transparent border-top-0 pt-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ strtolower(url($moduleName . '/' . Str::plural($modelName) . '/' . $record->id)) }}"
                            class="btn btn-sm btn-outline-primary">
                            View Details
                        </a>
                        <div class="dropup">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown"
                                data-bs-display="static">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end px-2 py-3">
                                <li class="mb-2">
                                    <a class="dropdown-item border-radius-md" href="#"
                                        wire:click="editRecord({{ $record->id }}, '{{ addslashes($model) }}')">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a class="dropdown-item text-danger border-radius-md" href="#"
                                        wire:click="deleteRecord({{ $record->id }})">
                                        <i class="fas fa-trash me-2"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-8">
                <div class="mb-4">
                    <i class="fas fa-cards fa-3x text-muted"></i>
                </div>
                <h5 class="text-muted mb-2">No records found</h5>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
            </div>
        </div>
    @endforelse
















<style>
    .card-hover:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(13, 202, 240, 0.1);
    }

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






    .ribbon {
        position: absolute;
        top: 10px;
        right: -30px;
        padding: 5px 30px;
        transform: rotate(45deg);
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        z-index: 1;
    }

    .ribbon-warning {
        background-color: #ffc107;
    }

    .ribbon-success {
        background-color: #198754;
    }

    .ribbon-danger {
        background-color: #dc3545;
    }

    .ribbon-info {
        background-color: #0dcaf0;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .object-fit-cover {
        object-fit: cover;
    }
</style>

</div>


