    {{-- ------------ CONTAINER BEGINS ------------ --}}
    <div class="card  p-4">

        {{-- ------------ FORM MANAGER HEADER TEXT ------------ --}}
        <div class="card-header pb-0">
            <div class="d-flex flex-row justify-content-between">
                {{--<div>
                    @php
                        if (!$pageTitle) // Check if 'pageTitle' is available in the DataTableManager
                            $pageTitle = $this->getConfigFileField($moduleName, $modelName, 'pageTitle');
                        if (!$pageTitle) { // Check if 'pageTitle' is available in the config file
                            // Generate the 'pageTitle'from the moduleName
                            $pageTitle = Str::snake($modelName); // Convert to snake case
                            $pageTitle = ucwords(str_replace('_', ' ', $pageTitle)); // Convert to capitalised words
                            $pageTitle = Str::plural(ucfirst($pageTitle))." Record";
                        }
                    @endphp

                    <h5 class="mb-4">{{ $pageTitle }} </h5>

                </div>
                @if (is_array($controls) && in_array('addButton', $controls))
                    <button wire:click="$dispatch('openAddModalEvent')"
                        class="btn bg-gradient-primary btn-icon-only rounded-circle" type="button">
                        <i class="fa-solid fa-plus   text-white"></i>
                    </button>
                @endif--}}

                <h5 class="mb-4"> Header Goes here </h5>

            </div>
        </div>

    </div>
    {{-- ------------ FORM MANAGER HEADER TEXT ------------ --}}













</div>
    {{-- ------------ FORM MANAGER CONTAINER ------------ --}}
