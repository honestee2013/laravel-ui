
@include('qf::components.livewire.bootstrap.data-tables.modals.modal-header', [
    'modalId' => 'detail',
    'isEditMode' => $isEditMode,
])




<div class="p-0 modal-form">

    @if ($selectedItem)
        <div class="container-fluid py-4">
            <div class="row g-4">
                <!-- Image Section - Full Width -->
                @php
                    $imageColumns = array_filter($columns, function($column) use ($hiddenFields) {
                        return !in_array($column, $hiddenFields['onDetail']) && 
                               in_array($column, DataTableConfig::getSupportedImageColumnNames());
                    });
                @endphp
                
                @if(count($imageColumns) > 0)
                <div class="col-12">

                            <div class="row g-3 justify-content-center">
                                @foreach($imageColumns as $column)
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <div class="mb-2 fw-semibold text-muted small">
                                                @if (isset($fieldDefinitions[$column]['label']))
                                                    {{ ucwords($fieldDefinitions[$column]['label']) }}
                                                @else
                                                    {{ ucwords(str_replace('_', ' ', $column)) }}
                                                @endif
                                            </div>
                                            <div class="image-container position-relative d-inline-block">
                                                <img class="rounded shadow-sm img-thumbnail" 
                                                     style="max-height: 200px; max-width: 100%; object-fit: contain;"
                                                     src="{{ asset('storage/' . $selectedItem->$column) }}" 
                                                     alt="{{ $selectedItem->$column }}"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBVbmF2YWlsYWJsZTwvdGV4dD48L3N2Zz4='">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                        </div>
                </div>
                @endif
                
                <!-- Text and Relationship Data - Two Columns Layout -->
                <div class="col-12">
                    

                            <div class="row g-0">
                                @php
                                    $textColumns = array_filter($columns, function($column) use ($hiddenFields, $imageColumns) {
                                        return !in_array($column, $hiddenFields['onDetail']) && 
                                               !in_array($column, $imageColumns);
                                    });
                                    $columnCount = count($textColumns);
                                    $midPoint = ceil($columnCount / 2);
                                    $currentIndex = 0;
                                @endphp
                                
                                <!-- First Column -->
                                <div class="col-md-6">
                                    <div class="p-4">
                                        @foreach($textColumns as $column)
                                            @if($currentIndex < $midPoint)

                                             
                                                <x-qf::livewire.bootstrap.data-tables.partials.detail-field
                                                    :column="$column"
                                                    :fieldDefinitions="$fieldDefinitions"
                                                    :selectedItem="$selectedItem"
                                                    :multiSelectFormFields="$multiSelectFormFields"
                                                />
                                            @endif
                                            @php $currentIndex++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Second Column -->
                                <div class="col-md-6 border-start-md">
                                    <div class="p-4">
                                        @php $currentIndex = 0; @endphp
                                        @foreach($textColumns as $column)
                                            @if($currentIndex >= $midPoint)
                                                <x-qf::livewire.bootstrap.data-tables.partials.detail-field
                                                    :column="$column"
                                                    :fieldDefinitions="$fieldDefinitions"
                                                    :selectedItem="$selectedItem"
                                                    :multiSelectFormFields="$multiSelectFormFields"
                                                />
                                            @endif
                                            @php $currentIndex++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                    
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-exclamation-circle display-4 d-block mb-3"></i>
                <p class="mb-0">No item selected</p>
            </div>
        </div>
    @endif
</div>




{{----- NOTE THE IFFERENCE WITH core.views::data-tables.modals.modal-header -----}}
@include('qf::components.livewire.bootstrap.data-tables.partials.form-footer', [
    'modalId' => 'detail',
])