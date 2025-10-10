@props([
    'record' => null,
    'config' => [],
])

@php

    // Guard clause
    if (!$record || !$config) {
        // Fallback for dev safety
        $title = 'Record';
        $subtitle = '';
        $content = '';
        $detailType = 'profile';
        $icon = null;
        $avatarUrl = null;
        $relatedLinks = [];

        
    } else {
        $detailConfig = $config['switchViews']['detail'] ?? [];
        $detailType = $detailConfig['detailType'] ?? 'profile';

        // Helper to get field value safely
        $getFieldValue = function ($fieldKey) use ($record) {
            $value =  $this->formatFieldValue($record, $fieldKey);
            if ($value != "N/A")
              return $value;
            else
              return null;
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

        // Get photo field if switchViews.detail.imageField is not provided
        $imageFields = $detailConfig['imageField'] ?? [];
        $avatarUrl = null;
        foreach ($imageFields as $imgField) {
            if ($record->{$imgField}) {
                $avatarUrl = $record->{$imgField};
                break;
            }
        }

        //////////////////
        if (!$avatarUrl) // Fall back
          $avatarUrl = $record?->employeeProfile?->photo; 


        $icon = $detailConfig['icon'] ?? ($detailType === 'document' ? 'fa-file-alt' : null);

        // Related links (customize per your routing)
        if ($detailType === 'profile') {
            $relatedLinks = [
                ['url' => url('/hr/employees'), 'label' => 'All Employees'],
                ['url' => url("/hr/employee/{$record->id}"), 'label' => 'View Full Profile'],
            ];
        } else {
            $relatedLinks = [
                ['url' => url('/hr/documents'), 'label' => 'All Documents'],
                ['url' => url('/hr/employees'), 'label' => 'Back to Employees'],
            ];
        }
    }

    // Generate unique ID for collapse (to avoid JS conflicts)
    $collapseId = 'extraDetails_' . ($record?->id ?? uniqid());
@endphp

<style>
    .profile-header {
        background: linear-gradient(180deg, #842966, #e78bc8);
        color: white;
        border-radius: 0.75rem;
        padding: 1.75rem;
        margin-bottom: 0rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .profile-media {
        width: 96px;
        height: 96px;
        border: 4px solid white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        background: white;
        color: #6c757d;
    }

    .profile-media.document {
        border-radius: 0.5rem;
        color: #4e73df;
    }

    .profile-header h1 {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .profile-header .subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 0.25rem;
    }

    .profile-header .content {
        opacity: 0.85;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 1.1rem;
        }

        .profile-media {
            width: 68px;
            height: 68px;
            border-width: 3px;
            font-size: 1.8rem;
        }

        .profile-header h1 {
            font-size: 1.35rem;
        }

        .profile-header .subtitle {
            font-size: 1rem;
        }

        .profile-header .content,
        .related-links {
            display: none;
        }

        /* Show first part of content on mobile (e.g., ID) */
        .profile-header .content {
            display: block !important;
            font-size: 0.875rem;
        }
    }
</style>

<div class="container">



  
    <div class="profile-header d-flex align-items-start">
        <!-- Avatar or Icon -->
        <div class="profile-media {{ $detailType === 'document' ? 'document' : '' }} me-4">
            @if ($detailType === 'profile' && $avatarUrl)
                <img src="{{ asset('storage/' .$avatarUrl) }}" alt="{{ $title }}" class="w-100 h-100 rounded-circle"
                    style="object-fit: cover;">
            @elseif($detailType === 'document' && $icon)
                <i class="fas {{ $icon }}"></i>
            @else
                <i class="fas fa-user"></i>
            @endif
        </div>

        <div class="flex-grow-1">
            <h1 class="text-white">{{ $title }}</h1>
            @if ($subtitle)
                <div class="subtitle">{{ $subtitle }}</div>
            @endif
            @if ($content)
                <div class="content">{{ $content }}</div>
            @endif

            <!-- Related Links -->
            <div class="related-links mt-2">
                <i class="fas fa-link me-1"></i>
                @foreach ($relatedLinks as $link)
                    <a href="{{ $link['url'] }}" class="{{ $loop->first ? '' : 'ms-2' }}">
                        {{ $link['label'] }}
                    </a>
                    @if (!$loop->last)
                        |
                    @endif
                @endforeach
            </div>

            <!-- More Details (only if record has extra fields) -->
            @if ($record && ($record->email || $record->phone || $record->address_street))
                <button class="btn btn-sm btn-outline-light d-flex align-items-center mt-2" type="button"
                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                    <i class="fas fa-chevron-down me-1"></i>
                    <span class="toggle-text">More Details</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Extra Details Collapse -->
    @if ($record && ($record->email || $record->phone || $record->address_street))
        <div class="collapse" id="{{ $collapseId }}">
            <div class="card">
                <div class="card-body">
                    @if ($record->email)
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i> {{ $record->email }}</p>
                    @endif
                    @if ($record->phone)
                        <p class="mb-1"><i class="fas fa-phone me-2"></i> {{ $record->phone }}</p>
                    @endif
                    @if ($record->address_street)
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {{ $record->address_street }}, {{ $record->address_city }}
                        </p>
                    @endif
                    @if (isset($record->status))
                        <p class="mb-0"><i class="fas fa-user-check me-2"></i> Status: {{ ucfirst($record->status) }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const collapseEl = document.getElementById('{{ $collapseId }}');
                if (!collapseEl) return;

                const toggleBtn = collapseEl.closest('.profile-header').querySelector('[data-bs-toggle="collapse"]');
                const toggleText = toggleBtn?.querySelector('.toggle-text');
                const icon = toggleBtn?.querySelector('i');

                collapseEl.addEventListener('shown.bs.collapse', () => {
                    if (toggleText) toggleText.textContent = 'Less Details';
                    if (icon && icon.classList.contains('fa-chevron-down')) {
                        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                    }
                });

                collapseEl.addEventListener('hidden.bs.collapse', () => {
                    if (toggleText) toggleText.textContent = 'More Details';
                    if (icon && icon.classList.contains('fa-chevron-up')) {
                        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            });
        </script>
    @endif
</div>
