@props([
    'record' => null,
    'config' => [],
])

@php
    // Guard clause with improved fallbacks
    if (!$record || !$config) {
        $title = 'Record';
        $subtitle = '';
        $content = '';
        $detailType = 'profile';
        $icon = null;
        $avatarUrl = null;
        $relatedLinks = [];
        $badgeInfo = [];
        $stats = [];
    } else {
        $detailConfig = $config['switchViews']['detail'] ?? [];
        $detailType = $detailConfig['detailType'] ?? 'profile';
        $layout = $detailConfig['layout'] ?? 'tab';

        // Helper to get field value safely
        $getFieldValue = function ($fieldKey) use ($record) {
            $value = $this->formatFieldValue($record, $fieldKey);
            return ($value != "N/A" && !empty($value)) ? $value : null;
        };

        // Build title, subtitle, content from config
        $titleFields = $detailConfig['titleFields'] ?? [];
        $subtitleFields = $detailConfig['subtitleFields'] ?? [];
        $contentFields = $detailConfig['contentFields'] ?? [];

        $titleParts = array_filter(array_map($getFieldValue, $titleFields));
        $subtitleParts = array_filter(array_map($getFieldValue, $subtitleFields));
        $contentParts = array_filter(array_map($getFieldValue, $contentFields));

        $title = implode(' ', $titleParts) ?: 'Unnamed Record';
        $subtitle = implode(' • ', $subtitleParts);
        $content = implode(' • ', $contentParts);

        // Get image/avatar with improved fallback logic
        $avatarUrl = null;
        $imageField = $detailConfig['imageField'] ?? null;
        
        if ($imageField && is_string($imageField) && $record->{$imageField}) {
            $avatarUrl = $record->{$imageField};
        } elseif ($imageField && is_array($imageField)) {
            foreach ($imageField as $imgField) {
                if ($record->{$imgField}) {
                    $avatarUrl = $record->{$imgField};
                    break;
                }
            }
        }
        
        if (!$avatarUrl) {
            $avatarUrl = $record?->employeeProfile?->photo ?? 
                        $record?->photo ?? 
                        $record?->avatar ?? 
                        $record?->image ?? 
                        $record?->profile_picture;
        }

        // Format avatar URL
        if ($avatarUrl && !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
            $avatarUrl = asset('storage/' . $avatarUrl);
        } elseif (!$avatarUrl && $detailType === 'profile') {
            // Generate initials avatar
            $initials = implode('', array_map(fn($word) => strtoupper(substr($word, 0, 1)), explode(' ', $title)));
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=4e73df&color=fff&size=200&bold=true';
        }

        $icon = $detailConfig['icon'] ?? ($detailType === 'document' ? 'fa-file-alt' : null);

        // Collect status badges from config
        $badgeInfo = [];
        $badgeFields = ['status', 'is_approved', 'needs_review', 'is_active', 'is_published', 'is_default'];
        
        foreach ($badgeFields as $field) {
            
            if (isset($record->$field) && $record->$field !== null) {
                $badgeInfo[$field] = [
                    'value' => $record->$field,
                    'label' => $this->fieldDefinitions[$field]['label'],
                    'color' => $this->getBadgeColor($record->$field, $field)
                ];
            }
        }

        // Generate stats for profile type (e.g., employee stats)
        $stats = [];
        if ($detailType === 'profile' && method_exists($record, 'employeeProfile')) {
            $stats = [
                ['label' => 'Tenure', 'value' => $this->formatFieldValue($record, 'hire_date') ?? '—'],
                ['label' => 'Department', 'value' => $record->department?->name]?? '-', // $this->formatFieldValue($record, 'department.name') ?? '—'],
                ['label' => 'Position', 'value' => $record->employeePosition?->jobTitle?->title], // $this->formatFieldValue($record, 'employeePosition.jobTitle.title') ?? '—'],
            ];
        }

        
        // Related links with better context
        $modelName = class_basename($record);
        $moduleName = strtolower($modelName) === 'employee' ? 'hr' : 'attendance';
        
        if ($detailType === 'profile') {
            $relatedLinks = [
                ['url' => url("/$moduleName/" . Str::plural($modelName)), 'label' => 'All ' . Str::plural($modelName), 'icon' => 'fas fa-list'],
                ['url' => url("/$moduleName/" . Str::plural($modelName) . "/{$record->id}/edit"), 'label' => 'Edit Profile', 'icon' => 'fas fa-edit'],
                ['url' => url("/$moduleName/" . Str::plural($modelName) . "/{$record->id}"), 'label' => 'Full Details', 'icon' => 'fas fa-eye'],
            ];
        } else {
            $relatedLinks = [
                ['url' => url("/$moduleName/documents"), 'label' => 'All Documents', 'icon' => 'fas fa-folder'],
                ['url' => url("/$moduleName/" . Str::plural($modelName)), 'label' => 'Back to List', 'icon' => 'fas fa-arrow-left'],
            ];
        }

        // Extra details fields
        $extraDetails = [];
        $extraFields = ['email', 'phone', 'address_street', 'address_city', 'address_country'];
        foreach ($extraFields as $field) {
            if (isset($record->$field) && $record->$field) {
                $extraDetails[$field] = $record->$field;
            }
        }
    }

    // Generate unique ID for collapse
    $collapseId = 'extraDetails_' . ($record?->id ?? uniqid());
    
    // Determine theme colors based on type
    $theme = [
        'profile' => [
            'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'solid' => '#4e73df',
            'light' => 'rgba(78, 115, 223, 0.1)',
        ],
        'document' => [
            'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'solid' => '#e74a3b',
            'light' => 'rgba(231, 74, 59, 0.1)',
        ],
    ][$detailType] ?? [
        'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'solid' => '#36b9cc',
        'light' => 'rgba(54, 185, 204, 0.1)',
    ];
@endphp

<div class="profile-header-wrapper">
    <div class="profile-header" style="--theme-color: {{ $theme['solid'] }}; --theme-gradient: {{ $theme['gradient'] }};">
        <div class="container">
            <div class="row align-items-center">
                <!-- Avatar/Document Icon -->
                <div class="col-auto">
                    <div class="profile-media-wrapper">
                        @if ($detailType === 'profile' && $avatarUrl)
                            <div class="profile-media profile-avatar">
                                <img src="{{ $avatarUrl }}" 
                                     alt="{{ $title }}" 
                                     class="w-100 h-100"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($title) }}&background=4e73df&color=fff&size=200'">
                                @if(isset($badgeInfo['status']))
                                    <div class="status-indicator status-{{ $badgeInfo['status']['color'] }}"></div>
                                @endif
                            </div>
                        @elseif($detailType === 'document' && $icon)
                            <div class="profile-media document-icon">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                        @else
                            <div class="profile-media default-icon">
                                <i class="fas {{ $detailType === 'profile' ? 'fa-user' : 'fa-file' }}"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col">
                    <div class="profile-content">
                        <!-- Title Row -->
                        <div class="d-flex align-items-center flex-wrap mb-2">
                            <h1 class="profile-title mb-0 me-3">{{ $title }}</h1>
                            
                            <!-- Quick Actions -->
                            {{--  <div class="quick-actions d-flex gap-2">
                                @if($record)
                                    <button wire:click="editRecord({{ $record->id }})"
                                            class="btn btn-sm btn-light"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="window.print()"
                                            class="btn btn-sm btn-light"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Print">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <a href="#" 
                                       class="btn btn-sm btn-light"
                                       data-bs-toggle="tooltip"
                                       data-bs-title="Share">
                                        <i class="fas fa-share-alt"></i>
                                    </a>
                                @endif
                            </div>--}}
                        </div>

                        <!-- Subtitle -->
                        @if ($subtitle)
                            <div class="profile-subtitle mb-3">
                                <i class="fas fa-info-circle me-1"></i> {{ $subtitle }}
                            </div>
                        @endif

                        <!-- Stats & Badges -->
                        <div class="profile-meta d-flex flex-wrap align-items-center gap-3">
                            <!-- Badges -->
                            @if(!empty($badgeInfo))
                                <div class="badges-container">
                                    @foreach($badgeInfo as $badge)
                                        <span class="badge badge-pill bg-{{ $badge['color'] }}-light text-{{ $badge['color'] }}">
                                            @if(is_bool($badge['value']))
                                                <i class="fas fa-{{ $badge['value'] ? 'check' : 'times' }} me-1"></i>
                                            @endif
                                            {{ $badge['label'] }}: {{ is_bool($badge['value']) ? ($badge['value'] ? 'Yes' : 'No') : ucfirst($badge['value']) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Stats -->
                            @if(!empty($stats))
                                <div class="stats-container">
                                    @foreach($stats as $stat)
                                        @if($stat['value'] && $stat['value'] !== '—')
                                            <div class="stat-item">
                                                <span class="stat-label">{{ $stat['label'] }}:</span>
                                                <span class="stat-value">{{ $stat['value'] }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Content & Quick Links -->
                        <div class="profile-footer d-flex justify-content-between align-items-center mt-4">
                            @if ($content)
                                <div class="profile-content-text">
                                    <i class="fas fa-file-alt me-1"></i> {{ $content }}
                                </div>
                            @endif

                            <!-- Quick Links -->
                            <div class="profile-links">

                                {{--  <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-light dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h me-1"></i> Quick Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach($relatedLinks as $link)
                                            <li>
                                                <a class="dropdown-item" href="{{ $link['url'] }}">
                                                    <i class="{{ $link['icon'] ?? 'fas fa-link' }} me-2"></i>
                                                    {{ $link['label'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                        @if(!empty($extraDetails))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="#" 
                                                   data-bs-toggle="collapse" 
                                                   data-bs-target="#{{ $collapseId }}">
                                                    <i class="fas fa-address-card me-2"></i> Contact Info
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>--}}
                            </div>


                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extra Details Collapse (Contact Info) -->
    @if(!empty($extraDetails))
        <div class="container">
            <div class="collapse" id="{{ $collapseId }}">
                <div class="card card-shadow mt-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-address-book me-2"></i> Contact Information
                        </h6>
                        <div class="row">
                            @if(isset($extraDetails['email']))
                                <div class="col-md-6 mb-3">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <div>
                                            <div class="text-muted small">Email</div>
                                            <a href="mailto:{{ $extraDetails['email'] }}" class="text-decoration-none">
                                                {{ $extraDetails['email'] }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(isset($extraDetails['phone']))
                                <div class="col-md-6 mb-3">
                                    <div class="contact-item">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <div>
                                            <div class="text-muted small">Phone</div>
                                            <a href="tel:{{ $extraDetails['phone'] }}" class="text-decoration-none">
                                                {{ $extraDetails['phone'] }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(isset($extraDetails['address_street']))
                                <div class="col-12 mb-3">
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <div>
                                            <div class="text-muted small">Address</div>
                                            <div>
                                                {{ $extraDetails['address_street'] }}
                                                @if(isset($extraDetails['address_city']))
                                                    , {{ $extraDetails['address_city'] }}
                                                @endif
                                                @if(isset($extraDetails['address_country']))
                                                    , {{ $extraDetails['address_country'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .profile-header-wrapper {
        margin-bottom: 2rem;
    }

    .profile-header {
        background: var(--theme-gradient);
        color: white;
        border-radius: 0.75rem;
        padding: 2rem 0;
        margin-bottom: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .profile-media-wrapper {
        position: relative;
    }

    .profile-media {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .profile-media:hover {
        transform: scale(1.05);
    }

    .profile-avatar img {
        object-fit: cover;
    }

    .profile-media.document-icon,
    .profile-media.default-icon {
        font-size: 3rem;
        color: var(--theme-color);
        border-radius: 0.75rem;
    }

    .status-indicator {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .status-success { background-color: #1cc88a; }
    .status-warning { background-color: #f6c23e; }
    .status-danger { background-color: #e74a3b; }
    .status-secondary { background-color: #858796; }

    .profile-content {
        color: white;
    }

    .profile-title {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .profile-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 400;
    }

    .profile-content-text {
        font-size: 0.95rem;
        opacity: 0.85;
        max-width: 500px;
    }

    .quick-actions .btn-light {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        transition: all 0.2s ease;
    }

    .quick-actions .btn-light:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }

    .badges-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .badge.bg-success-light { background-color: rgba(28, 200, 138, 0.2); }
    .badge.bg-warning-light { background-color: rgba(246, 194, 62, 0.2); }
    .badge.bg-danger-light { background-color: rgba(231, 74, 59, 0.2); }
    .badge.bg-secondary-light { background-color: rgba(133, 135, 150, 0.2); }
    .badge.bg-primary-light { background-color: rgba(78, 115, 223, 0.2); }

    .stats-container {
        display: flex;
        gap: 1.5rem;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .profile-links .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .profile-links .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .card-shadow {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .contact-item {
        display: flex;
        align-items: flex-start;
        padding: 0.5rem 0;
    }

    .contact-item i {
        margin-top: 0.25rem;
        font-size: 1.25rem;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem 0;
            border-radius: 0;
            margin-bottom: 1rem;
        }

        .profile-media {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }

        .profile-media.document-icon,
        .profile-media.default-icon {
            font-size: 2rem;
        }

        .profile-title {
            font-size: 1.4rem;
        }

        .profile-subtitle {
            font-size: 0.9rem;
        }

        .stats-container {
            flex-direction: column;
            gap: 0.75rem;
        }

        .profile-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .profile-links {
            width: 100%;
        }

        .profile-links .dropdown {
            width: 100%;
        }

        .profile-links .btn {
            width: 100%;
        }

        .quick-actions {
            order: -1;
            margin-bottom: 1rem;
            width: 100%;
            justify-content: flex-end;
        }
    }

    /* Animation for collapse */
    .collapse.show {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Enhanced collapse toggle with animation
        const collapseEl = document.getElementById('{{ $collapseId }}');
        if (collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', function () {
                this.style.maxHeight = '0';
                this.style.transition = 'max-height 0.3s ease';
                setTimeout(() => {
                    this.style.maxHeight = this.scrollHeight + 'px';
                }, 10);
            });

            collapseEl.addEventListener('hide.bs.collapse', function () {
                this.style.maxHeight = '0';
            });
        }

        // Add click outside to close dropdown
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                const openDropdowns = document.querySelectorAll('.dropdown.show');
                openDropdowns.forEach(dropdown => {
                    bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'))?.hide();
                });
            }
        });
    });
</script>