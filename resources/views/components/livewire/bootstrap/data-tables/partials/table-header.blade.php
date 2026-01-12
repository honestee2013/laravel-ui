@php
    // Ensure $data is defined
    use Illuminate\Support\Str;
    $pageTitle = $pageTitle ?? '';
    $subPageTitle = $subPageTitle ?? '';
    $modelName = $modelName ?? '';
    $config = $config ?? [];
    $controls = $controls ?? [];

    $record = $prevRecord = $nextRecord = null;
    if ($selectedItemId) {
        $record = $this->model::find($selectedItemId);
        // Get adjacent records for navigation
        $prevRecord = $this->model
            ::where('id', '<', $selectedItemId)
            ->orderBy('id', 'desc')
            ->first(['id']);
        $nextRecord = $this->model
            ::where('id', '>', $selectedItemId)
            ->orderBy('id', 'asc')
            ->first(['id']);
    }

@endphp


{{-- ------------DATA TABLE MANAGER HEADER TEXT ------------ --}}
<div class="pb-0">
    <div class="d-flex flex-row justify-content-between">
        <div>
            @php
                if (!$pageTitle) {
                    // If 'pageTitle' is not available in the DataTableManager check the config file
                    $pageTitle = $config['pageTitle'] ?? '';
                }

                if (!$pageTitle) {
                    // If 'pageTitle' is not available in the config file, generate it from the modelName
                    $pageTitle = Str::snake($modelName); // Convert to snake case
                    $pageTitle = ucwords(str_replace('_', ' ', $pageTitle)); // Convert to capitalised words
                    $pageTitle = Str::plural(ucfirst($pageTitle)); // . ' Record';
                }
            @endphp

            @if (isset($subPageTitle))
                <h5 class="mb-1">{{ $pageTitle }} </h5>
                <p class="mb-4 ms-2 text-gradient text-info">{{ $subPageTitle }} </p>
            @endif


        </div>
        <div>


            @if ($viewType != 'detail')






@php
    $addActions = $controls['addButton'] ?? [];
    $primaryAction = collect($addActions)->firstWhere('primary', true) ?? ($addActions[0] ?? null);
@endphp

@if (!empty($addActions))
    {{-- @if (count($addActions) === 1 && $primaryAction['type'] === 'quick_add')--}}
    @if (!is_array($controls['addButton']) && $controls['addButton']) {{-- addButton == true --}}
        {{-- Single quick-add button --}}
        <button wire:click="$dispatch('openAddModalEvent')" class="btn bg-gradient-primary btn-sm">
            <i class="{{ $primaryAction['icon'] ?? 'fa-solid fa-plus' }} text-white"></i>
            {{ $primaryAction['label'] ?? 'Add ' . \Str::singular($pageTitle) }}
        </button>
    @else








    
        {{-- Dropdown with multiple options --}}
        <div class="dropdown">
            
            <div class="btn-group" role="group">
                {{-- Primary action --}}
                @if ($primaryAction)
                    @if ($primaryAction['type'] === 'quick_add')
                        <button wire:click="$dispatch('openAddModalEvent')" class="btn bg-gradient-primary btn-sm">
                            <i class="{{ $primaryAction['icon'] ?? 'fa-solid fa-plus' }} text-white"></i>
                            {{ $primaryAction['label'] }}
                        </button>
                    @elseif ($primaryAction['type'] === 'wizard')
                        <a href="{{ $primaryAction['url']?? '/' }}" class="btn bg-gradient-primary btn-sm" wire:navigate>
                            <i class="{{ $primaryAction['icon'] ?? 'fa-solid fa-plus' }} text-white"></i>
                            {{ $primaryAction['label'] }}
                        </a>
                    @endif
                @endif

                {{-- Dropdown toggle --}}
                <button type="button" class="btn bg-gradient-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>

                {{-- Dropdown menu --}}
                <ul class="dropdown-menu me-sm-n4 dropdown-menu-end p-3 pt-4">
                    @foreach ($addActions as $action)
                        @if (empty($action['primary']))
                            <li class="mb-2">
                                @if ($action['type'] === 'wizard')
                                    <a class="dropdown-item border-radius-md" href="{{ $action['url']?? '/' }}" wire:navigate>
                                        <i class="{{ $action['icon'] ?? 'fas fa-edit' }} me-2"></i>
                                        {{ $action['label'] }}
                                    </a>
                                @elseif ($action['type'] === 'quick_add')
                                    <button class="dropdown-item border-radius-md" wire:click="$dispatch('openAddModalEvent')">
                                        <i class="{{ $action['icon'] ?? 'fas fa-edit' }} me-2"></i>
                                        {{ $action['label'] }}
                                    </button>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endif







                {{-- Replace your current detail header section --}}
            @else
                <div class="d-flex justify-content-between align-items-center mb-2">
                    {{-- Back to List --}}
                    {{-- <a href="" class="btn btn-sm btn-outline-secondary">
            ← Back to {{ Str::plural($modelName) }}
        </a> --}}

                    {{-- Previous/Next Navigation --}}
                    <div class="btn-group" role="group">
                        @if ($prevRecord)
                            <a href="/{{ strtolower($moduleName) }}/{{ Str::plural(strtolower($modelName)) }}/{{ $prevRecord->id }}"
                                class="btn btn-sm btn-outline-secondary" wire:navigate>
                                ← Previous
                            </a>
                        @else
                            <button class="btn btn-sm btn-outline-secondary disabled">
                                ← Previous
                            </button>
                        @endif

                        @if ($nextRecord)
                            <a href="/{{ strtolower($moduleName) }}/{{ Str::plural(strtolower($modelName)) }}/{{ $nextRecord->id }}"
                                class="btn btn-sm btn-outline-secondary" wire:navigate>
                                Next →
                            </a>
                        @else
                            <button class="btn btn-sm btn-outline-secondary disabled">
                                Next →
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 👇 VIEW SWITCHER --}}
            {{-- Switcher Buttons should be hidden on the detail views --}}
            @if (!empty($config['switchViews']) && $viewType != 'detail')
                <div class="btn-group " role="group" aria-label="View options">
                    @php
                        $currentView = $viewType ?? ($config['views']['list'] ?? 'table');
                    @endphp


                    <button type="button"
                        class="btn btn-sm btn-outline-primary {{ $currentView === 'table' ? 'bg-gradient-primary' : '' }}"
                        wire:click="$set('viewType', 'table')" data-bs-toggle="tooltip"
                        data-bs-original-title="Table View">
                        <i class="fas fa-table"></i>
                    </button>


                    @if (isset($config['switchViews']['list']))
                        <button type="button"
                            class="btn btn-sm btn-outline-primary {{ $currentView === 'list' ? 'bg-gradient-primary' : '' }}"
                            wire:click="$set('viewType', 'list')" data-bs-toggle="tooltip"
                            data-bs-original-title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    @endif

                    @if (isset($config['switchViews']['card']))
                        <button type="button"
                            class="btn btn-sm btn-outline-primary {{ $currentView === 'card' ? 'bg-gradient-primary' : '' }}"
                            wire:click="$set('viewType', 'card')" data-bs-toggle="tooltip"
                            data-bs-original-title="Card View">
                            <i class="fas fa-th-large"></i>
                        </button>
                    @endif

                </div>
            @endif








        </div>

    </div>

    @isset($record)
        @include('qf::components.livewire.bootstrap.data-tables.partials.data-profile-header', [
            'record' => $record,
            'config' => $config,
        ])
    @endisset

</div>
