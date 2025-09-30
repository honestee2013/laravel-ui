{{-- Export, Import & Print Data --}}
<div class="row">

    <!-- Files -->
    @if (isset($controls['files']))

        <div class="dropdown col-12 w-100 col-sm-auto w-sm-auto " wire:ignore>
            <a href="#" class="btn bg-gradient-primary dropdown-toggle mb-2 bt-sm pt-2  rounded-pill w-100 px-5"
                data-bs-toggle="dropdown" id="navbarDropdownMenuLink2" style=" height: 3em; ">
                <span class="btn-inner--icon"><i class="fa-solid fa-file text-sm me-1 text-white"></i></span>
                <span class="btn-inner--text">File</span>
            </a>

            <ul class="dropdown-menu p-3 pt-4" aria-labelledby="navbarDropdownMenuLink2">
                <!-- Export Section -->
                @if (isset($controls['files']['export']))
                    <pan class="m-2 text-uppercase text-xs fw-bolder">Export</pan>
                    <hr class="m-2 p-0 bg-gray-500" />
                    @if (in_array('xls', $controls['files']['export']))
                        <li class="mb-2">
                            <a wire:click="export('xlsx')" class="dropdown-item  border-radius-md" href="#">
                                <span class="btn-inner--icon me-1"><i
                                        class="fas fa-file-excel text-sm me-1 text-success"></i></span>
                                <span class="btn-inner--text">XLS</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('csv', $controls['files']['export']))
                        <li class="mb-2">
                            <a wire:click="export('csv')" class="dropdown-item  border-radius-md" href="#">
                                <span class="btn-inner--icon me-1"><i
                                        class="fa-solid fa-file-csv me-1 text-info"></i></span>
                                <span class="btn-inner--text">CVS</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('pdf', $controls['files']['export']))
                        <li class="mb-2">
                            <a wire:click="export('pdf')" class="dropdown-item  border-radius-md" href="#">
                                <span class="btn-inner--icon me-1"><i
                                        class="fas fa-file-pdf text-sm me-1 text-danger"></i></span>
                                <span class="btn-inner--text">PDF</span>
                            </a>
                        </li>
                    @endif

                    {{--
                    @if (in_array('pdf', $controls['files']['export']))
                        <li>
                            <a wire:click="export('print')" target="_blank" class="dropdown-item  border-radius-md" href="#">
                                <span class="btn-inner--icon me-1"><i
                                        class="fas fa-file-pdf text-sm me-1 text-danger"></i></span>
                                <span class="btn-inner--text">Print</span>
                            </a>
                        </li>
                    @endif
                    --}}



                @endif
                <!-- Print Option -->
                @if (in_array('print', $controls['files']))
                    <hr class="m-2 p-0 bg-gray-500" />
                    <li class="mb-2">
                        <a wire:click="printTable()" class="dropdown-item  border-radius-md" href="#">
                            <span class="btn-inner--icon me-1"><i class="fa-solid fa-print text-sm me-1"></i></span>
                            <span class="btn-inner--text">Print</span>
                        </a>
                    </li>
                @endif

                <!-- Import Section -->
                @if (isset($controls['files']['import']))
                    <div class="mt-4"></div>
                    <pan class="m-2 text-uppercase text-xs fw-bolder">Import</pan>
                    <hr class="m-2 p-0 bg-gray-500" />
                    <li class="mb-2">
                        <form wire:submit.prevent="import">
                            <input type="file" wire:model="file" class="p-4 pb-4 mb-1" />

                            @error('file')
                                <span class="text-danger"> {{ str_replace('fields.', ' ', $message) }} </span>
                            @enderror

                            <span x-show="$wire.file">
                                <hr class="m-0 p-0 mt-2 bg-gray-500" />
                                <button type="submit" class="btn btn-sm btn-secondary w-100 mt-4"
                                    style="border-radius: 2em">Import</button>
                            </span>
                        </form>
                    </li>
                @endif

            </ul>
        </div>
    @endif

    <!-- Bulk Action -->
    @if (isset($controls['bulkActions']))

        <div class="input-group col-12 w-100 col-sm-auto w-sm-auto" x-show="$wire.selectedRows.length">
            <select wire:model="bulkAction" class="form-select p-1 ps-3 pe-sm-5 px-sm-4 m-0"
                style = "height: 2.6em;
                                border-top-left-radius: 1.3em;
                                border-bottom-left-radius: 1.3em;

                            ">
                <option value="" style="display: none"> Action... </option>
                @if (array_key_exists('updateModelFields', $controls['bulkActions']) && is_array($controls['bulkActions']['updateModelFields']))

                        @foreach ($controls['bulkActions']["updateModelFields"] as $command => $options)
                            @if (isset($options['fieldName']) && isset($options['fieldValue']))
                                <option value="{{$options['fieldName']}}:{{$options['fieldValue']}}">
                                        {{ ucfirst($command) }}
                                </option>
                            @endif
                        @endforeach
                        <hr />
                @endif
                @if (in_array('xls', $controls['bulkActions']['export']))
                    <option value="exportXLSX">Export XLS</option>
                @endif
                @if (in_array('csv', $controls['bulkActions']['export']))
                    <option value="exportCSV">Export CSV</option>
                @endif
                @if (in_array('pdf', $controls['bulkActions']['export']))
                    <option value="exportPDF">Export PDF</option>
                @endif
                @if (in_array('delete', $controls['bulkActions']))
                    <hr class="horizontal" />
                    <option value="delete">Delete</option>
                @endif
            </select>
            <button wire:click="applyBulkAction" class="btn btn-sm btn-outline-primary p-0"
                style="height: 3em;
                                width: 4.7em;
                                    border-top-right-radius: 1.3em;
                                    border-bottom-right-radius: 1.3em;
                                "
                type="button" id="button-addon2">OK
            </button>
        </div>
    @endif



    <!-- Rows Per Page, Search & Hide Columns Section -->
    <!-- Rows Per Page -->
    @if (isset($controls['perPage']))

        <div class="input-group col-12 w-100 col-sm-auto w-sm-auto ">
            <label class="input-group-text" for="recordsPerPage"
                style = "
                            height: 2.6em;
                            padding: 0em 1em;
                            border-top-left-radius: 1.3em;
                            border-bottom-left-radius: 1.3em;">Rows</label>
            <select id="recordsPerPage" wire:model.live.500ms="perPage" class="form-select form-control ps-sm-2 pe-sm-5"
                style="
                                height: 2.6em;
                                border-top-right-radius: 1.3em;
                                border-bottom-right-radius: 1.3em;">
                @foreach ($controls['perPage'] as $page)
                    <option value="{{ $page }}">{{ $page }}</option>
                @endforeach
            </select>
        </div>
    @endif


    <!-- Search Box -->
    @if (isset($controls['search']) && $controls['search'])

        <div class="input-group mb-2 col-12 w-100 col-sm-auto w-sm-auto">
            <span class="input-group-text"
                style="
                            height: 2.6em;
                            border-top-left-radius: 1.3em;
                            border-bottom-left-radius: 1.3em;"><i
                    class="fas fa-search"></i></span>
            <input wire:model.live.500ms="search" class="form-control" type="search" id="search_box"
                placeholder="Search..."
                style="
                            height: 2.6em;
                            border-top-right-radius: 1.3em;
                            border-bottom-right-radius: 1.3em;">
        </div>
    @endif




    <!-- Filter Columns -->
    @if (isset($controls['filterColumns']) && $controls['filterColumns'])

        <div class="dropdown col-12 w-100 col-sm-auto w-sm-auto" >
            <a href="#" class="btn bg-gradient-primary dropdown-toggle bt-sm pt-2 me-2 w-100"
                data-bs-toggle="dropdown" id="filterDropdownMenuLink" style="border-radius: 3em; height: 3em">
                <span class="btn-inner--icon"><i class="fa-solid fa-filter text-sm me-1 text-white"></i></span>
                <span class="btn-inner--text">Filter</span>
            </a>

            <ul class="dropdown-menu  me-sm-n4 dropdown-menu-end p-3 pt-4 " aria-labelledby="filterDropdownMenuLink" style="min-width: 300px;">
                <span class="m-2 text-uppercase text-xs fw-bolder">Filter Records</span>
                <hr class="m-2 p-0 bg-gray-500" />

                {{--------- List filter inputs per column ---------}}
                @foreach ($columns as $column)
                    @if (!in_array($column, $hiddenFields['onTable']))
                        <div class="dropdown-item mb-2 border-radius-md">
                            <label class="form-label mb-1 fw-bold">
                                {{ $fieldDefinitions[$column]['label'] ?? ucwords(str_replace('_', ' ', str_replace('_id', '', $column))) }}
                            </label>
                            <div class="input-group input-group-sm">
                                <select class="form-select me-1 " wire:model.defer="filters.{{ $column }}.operator" style="width: 4.5em;">
                                    <option value="like">Contains</option>
                                    <option value="=">=</option>
                                    <option value="!=">!=</option>
                                    <option value=">">></option>
                                    <option value="<"><</option>
                                </select>
                                <input type="text" class="form-control" wire:model.defer="filters.{{ $column }}.value" placeholder="Value">
                            </div>
                        </div>
                    @endif
                @endforeach

                <hr class="m-2 p-0 bg-gray-500" />
                <button class="btn btn-sm btn-secondary w-100 mt-2" wire:click="applyFilters"
                    style="border-radius: 2em">Apply Filter</button>
            </ul>
        </div>
    @endif




    <!-- Show / Hide Columns -->
    @if (isset($controls['showHideColumns']) && $controls['showHideColumns'])

        <div class="dropdown col-12 w-100 col-sm-auto w-sm-auto">
            <a href="#" class="btn bg-gradient-primary dropdown-toggle bt-sm pt-2 me-2 w-100"
                data-bs-toggle="dropdown" id="navbarDropdownMenuLink2" style="border-radius: 3em; height: 3em">
                <span class="btn-inner--icon"><i class="fa-solid fa-list text-sm me-1 text-white"></i></span>
                <span class="btn-inner--text">Columns</span>
            </a>

            <ul class="dropdown-menu  me-sm-n4 dropdown-menu-end p-3 pt-4" aria-labelledby="navbarDropdownMenuLink2">
                <span class="m-2 text-uppercase text-xs fw-bolder">Show/Hide</span>
                <hr class="m-2 p-0 bg-gray-500" />
                {{--------- List columns with checkbox ---------}}
                @foreach ($columns as $column)
                    @if (!in_array($column, $hiddenFields['onTable']))
                        <div class="dropdown-item  border-radius-md">
                            <div class="form-check">
                                <input class="form-check-input " type="checkbox" wire:model.defer="selectedColumns"
                                    value="{{ $column }}" class="form-check-input"
                                    style="width: 1.2em; height:1.2em"
                                    @if (in_array($column, $visibleColumns)) checked @endif>

                                @if (isset($fieldDefinitions[$column]['label']))
                                    <span class="ms-1">
                                        {{ $fieldDefinitions[$column]['label'] }}
                                    </span>
                                @else
                                    <span class="ms-1">{{ ucwords(str_replace('_', ' ', str_replace('_id', '', $column))) }}</span>

                                @endif

                                </input>
                            </div>
                        </div>
                    @endif
                @endforeach
                <hr class="m-2 p-0 bg-gray-500" />
                <button class="btn btn-sm btn-secondary w-100 mt-2" wire:click="showHideSelectedColumns"
                    style="border-radius: 2em">OK</button>
            </ul>
        </div>
    @endif





</div>


