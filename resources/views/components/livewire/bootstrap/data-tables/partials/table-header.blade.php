@php
    // Ensure $data is defined
    use Illuminate\Support\Str;
    $pageTitle = $pageTitle ?? '';
    $modelName = $modelName ?? '';
    $config = $config ?? [];
    $controls = $controls ?? [];

    $record = $prevRecord = $nextRecord = null;
    if ($selectedItemId) {
        $record = $this->model::find($selectedItemId);
        // Get adjacent records for navigation
        $prevRecord = $this->model::where('id', '<', $selectedItemId)
            ->orderBy('id', 'desc')
            ->first(['id']);
        $nextRecord = $this->model::where('id', '>', $selectedItemId)
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
                    $pageTitle = Str::plural(ucfirst($pageTitle));// . ' Record';
                }
            @endphp

            <h5 class="mb-4">{{ $pageTitle }} </h5>

        </div>
        <div>


            @if ($viewType != 'detail')
                @if (is_array($controls) && isset($controls['addButton']) && $controls['addButton'])
                    <button wire:click="$dispatch('openAddModalEvent')" class="btn bg-gradient-primary bt-sm"
                        type="button" data-bs-toggle="tooltip" data-bs-original-title="Add {{ $modelName }}" style="height: 3em">
                        <i class="fa-solid fa-plus text-white"></i> Add {{ \Str::singular($pageTitle) }}
                    </button>
                @endif
{{-- Replace your current detail header section --}}
@else
    <div class="d-flex justify-content-between align-items-center mb-2">
        {{-- Back to List --}}
        {{--<a href="" class="btn btn-sm btn-outline-secondary">
            ← Back to {{ Str::plural($modelName) }}
        </a>--}}

        {{-- Previous/Next Navigation --}}
        <div class="btn-group" role="group">
            @if($prevRecord)
                <a href="/{{strtolower($moduleName)}}/{{Str::plural(strtolower($modelName))}}/{{ $prevRecord->id }}" 
                   class="btn btn-sm btn-outline-secondary"
                   wire:navigate>
                    ← Previous
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary disabled">
                    ← Previous
                </button>
            @endif

            @if($nextRecord)
                <a href="/{{strtolower($moduleName)}}/{{Str::plural(strtolower($modelName))}}/{{ $nextRecord->id }}" 
                   class="btn btn-sm btn-outline-secondary"
                   wire:navigate>
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
                        class="btn btn-sm btn-outline-primary {{ $currentView === 'table' ? 'active' : '' }}"
                        wire:click="$set('viewType', 'table')" data-bs-toggle="tooltip" data-bs-original-title="Table View">
                        <i class="fas fa-table"></i>
                    </button>


                    @if (isset($config['switchViews']['list']))
                        <button type="button"
                            class="btn btn-sm btn-outline-primary {{ $currentView === 'list' ? 'active' : '' }}"
                            wire:click="$set('viewType', 'list')" data-bs-toggle="tooltip" data-bs-original-title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    @endif

                    @if (isset($config['switchViews']['card']))
                        <button type="button" 
                            class="btn btn-sm btn-outline-primary {{ $currentView === 'cards' ? 'active' : '' }}"
                            wire:click="$set('viewType', 'card')" data-bs-toggle="tooltip" data-bs-original-title="Card View">
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
