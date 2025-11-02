@include('core.views::data-tables.modals.modal-header', [
    'modalId' => 'detail',
    'isEditMode' => $isEditMode,
])

<form role="form text-left" class="p-4 modal-form">

    <!-- Display more details of the selected item -->
    @if ($selectedItem)
        @foreach ($columns as $column)
            @if (!in_array($column, $hiddenFields['onDetail']))
                @if (isset($fieldDefinitions[$column]['label']))
                    <strong class="pe-3">{{ ucwords($fieldDefinitions[$column]['label']) }}</strong>
                @else
                    <strong class="pe-3">{{ ucwords(str_replace('_', ' ', $column)) }}</strong>
                @endif

                @if (in_array($column, DataTableConfig::getSupportedImageColumnNames()))
                    <div class="d-flex justify-content-center">
                        <img class="rounded rounded-3 mb-5" style="width: 10em"
                            src="{{ asset('storage/' . $selectedItem->$column) }}" alt="">
                    </div>
                @elseif ($column == 'password')
                    <p class="mb-4">***********************</p>
                @elseif (isset($fieldDefinitions[$column]) && isset($fieldDefinitions[$column]['relationship']))
                    <p class="mb-4">

                        <!---- Has Many Relationship ---->
                        @if (isset($fieldDefinitions[$column]['relationship']['type']) &&
                                $fieldDefinitions[$column]['relationship']['type'] == 'hasMany')
                            {{ implode(
                                ', ',
                                $selectedItem->{$fieldDefinitions[$column]['relationship']['dynamic_property']}->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray(),
                            ) }}
                            <!---- Belongs To Many Relationship ---->
                        @elseif (isset($fieldDefinitions[$column]['relationship']['type']) &&
                                $fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany')
                            {{ implode(
                                ', ',
                                $selectedItem->{$fieldDefinitions[$column]['relationship']['dynamic_property']}->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray(),
                            ) }}
                        @else
                            <!---- Has One Relationship ---->
                            @php
                                $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
                                $displayField = $fieldDefinitions[$column]['relationship']['display_field'];
                            @endphp
                            {{ optional($selectedItem->{$dynamic_property})->$displayField }}
                        @endif



                    </p>
                @elseif ($column && $multiSelectFormFields && in_array($column, array_keys($multiSelectFormFields)))
                    <p class="mb-4">
                        {{ str_replace(',', ', ', str_replace(['[', ']', '"'], '', $selectedItem->$column)) }}
                    </p>
                @else
                    <p class="mb-4">
                        {{ $selectedItem->$column }}
                    </p>
                @endif
            @endif
        @endforeach
    @endif

</form>

{{----- NOTE THE IFFERENCE WITH core.views::data-tables.modals.modal-header -----}}
@include('core.views::data-tables.partials.form-footer', [
    'modalId' => 'detail',
])
