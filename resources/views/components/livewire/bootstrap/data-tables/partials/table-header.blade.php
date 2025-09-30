
@php
    // Ensure $data is defined
    use Illuminate\Support\Str;
    $pageTitle = $pageTitle ?? '';
    $modelName = $modelName ?? '';
    $config = $config ?? [];
    $controls = $controls ?? [];    
    
@endphp


        {{-- ------------DATA TABLE MANAGER HEADER TEXT ------------ --}}
        <div class="card-header pb-0">
            <div class="d-flex flex-row justify-content-between">
                <div>
                    @php
                        if (!$pageTitle) // If 'pageTitle' is not available in the DataTableManager check the config file
                            $pageTitle = $config['pageTitle']?? '';

                        if (!$pageTitle) { // If 'pageTitle' is not available in the config file, generate it from the modelName
                            $pageTitle = Str::snake($modelName); // Convert to snake case
                            $pageTitle = ucwords(str_replace('_', ' ', $pageTitle)); // Convert to capitalised words
                            $pageTitle = Str::plural(ucfirst($pageTitle))." Record";
                        }
                    @endphp

                    <h5 class="mb-4">{{ $pageTitle }} </h5>

                </div>
                @if (is_array($controls) && isset($controls['addButton']) && $controls['addButton'])
                    <button wire:click="$dispatch('openAddModalEvent')"
                        class="btn bg-gradient-primary btn-icon-only rounded-circle" type="button" data-bs-toggle="tooltip" data-bs-original-title="Add {{$modelName}}" >
                        <i class="fa-solid fa-plus   text-white"></i>
                    </button>
                @endif
            </div>
        </div>