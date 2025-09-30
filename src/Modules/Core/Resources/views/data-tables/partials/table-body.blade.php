
<table id="dataTable" class="table align-items-center mb-0" >
    <thead>
        <tr>
            <!--- Bulk Action - Select ALL Checkbox ---->
            @if(isset($controls['bulkActions']))
                <th
                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                    <div class="form-check">
                        <input class="form-check-input" style="width: 1.6em; height:1.6em"
                            type="checkbox" wire:model.live.500ms="selectAll">
                    </div>
                </th>
            @endif

            <!--- Table Header ACS-DESC Sorting ---->
            @foreach ($columns as $column)
                @if (in_array($column, $visibleColumns))
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2 round-top "
                        wire:click="sortColumn('{{ $column }}')" style="height: 0.5em; "
                        aria-sort="{{ $sortField === $column ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                        <div
                            class="d-flex justify-content-between p-2 px-3
                        {{ $sortField === $column ? 'rounded-pill bg-gray-100' : '' }}">

                            @if(isset($fieldDefinitions[$column]['label']))
                                <span>{{ ucwords($fieldDefinitions[$column]['label']) }}</span>
                            @else
                                <span>{{ ucwords(str_replace('_', ' ', $column)) }}</span>
                            @endif
                            @if ($sortField === $column)
                                @if ($sortDirection === 'asc')
                                    <span class="btn-inner--icon"><i
                                            class="fa-solid fa-sort-up"></i></span>
                                @else
                                    <span class="btn-inner--icon"><i
                                            class="fa-solid fa-sort-down"></i></span>
                                @endif
                            @endif
                        </div>
                    </th>
                @endif
            @endforeach

            @if ($simpleActions)
                <th
                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                    Actions
                </th>
            @endif

        </tr>
    </thead>
    <tbody>


        @forelse($data as $row)
            <tr>
                <!--- Bulk Action - Single Row Selection Checkbox ---->
                @if(isset($controls['bulkActions']))
                    <td
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                        <div class="form-check">
                            <input class="form-check-input" style="width: 1.6em; height:1.6em"
                                x-on:click="$wire.toggleRowSelected()"
                                type="checkbox" wire:model="selectedRows" value="{{ $row->id }}"
                                @if (in_array($row->id, $selectedRows)) selected @endif />
                        </div>
                    </td>
                @endif

                <!---- Displaying Record That Has A RELATIONSHIP  ---->
                @foreach ($columns as $column)
                    @if (in_array($column, $visibleColumns))
                        <td  style=" white-space: normal; word-wrap: break-word; ">
                            <p class="text-xs font-weight-bold mb-0 ">
                                @if (isset($fieldDefinitions[$column]) && isset($fieldDefinitions[$column]['relationship']))
                                    <!---- Has Many Relationship ---->
                                    @if (isset($fieldDefinitions[$column]['relationship']['type']) && $fieldDefinitions[$column]['relationship']['type'] == 'hasMany')
                                        @php
                                            $dynamicProperty = $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}
                                                ?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray();
                                                $dynamicProperty = $dynamicProperty?? [];
                                        @endphp

                                        {!!
                                            $this->formartTableCellData($row, $column,
                                                implode(', ', $dynamicProperty)
                                            )
                                        !!}
                                    <!---- Belongs To Many Relationship ---->
                                    @elseif (isset($fieldDefinitions[$column]['relationship']['type']) && $fieldDefinitions[$column]['relationship']['type'] == 'belongsToMany')
                                        {!!
                                            $this->formartTableCellData($row, $column,
                                                implode(', ',
                                                    $row->{$fieldDefinitions[$column]['relationship']['dynamic_property']}
                                                    ?->pluck($fieldDefinitions[$column]['relationship']['display_field'])->toArray()
                                                )
                                            )
                                        !!}
                                    @else <!---- Belongs To Relationship ---->
                                        @php
                                            $dynamic_property = $fieldDefinitions[$column]['relationship']['dynamic_property'];
                                            $displayField = $fieldDefinitions[$column]['relationship']['display_field'];

                                            $displayField = explode(".", $displayField); // Handling a nested display field eg. user.name
                                            $displayField = count($displayField) > 1? $displayField[1]: $displayField[0]; // name should be replace with expected default display field
                                        @endphp
                                        {!!
                                            $this->formartTableCellData($row, $column,
                                                optional($row->{$dynamic_property})->$displayField
                                            )
                                        !!}
                                    @endif

                                @elseif ($column && $multiSelectFormFields && in_array($column, array_keys($multiSelectFormFields)))
                                    {!!
                                        $this->formartTableCellData($row, $column,
                                            str_replace(',', ', ', str_replace(['[', ']', '"'], '', $row->$column))
                                        )
                                    !!}
                                @elseif (in_array($column, DataTableConfig::getSupportedImageColumnNames()))
                                    @if ($row->$column)
                                        <img class="rounded-circle m-0"
                                            style="width: 4em; height: 4em;"
                                            src="{{ asset('storage/' . $row->$column) }}"
                                            alt="">
                                    @else
                                        <i class="fas fa-file-image  m-0 ms-2"
                                            style="font-size: 4em; color:lightgray;"></i>
                                    @endif
                                @else
                                    {!!
                                        $this->formartTableCellData($row, $column,
                                            $row->$column
                                        )
                                    !!}
                                @endif
                            </p>
                        </td>
                    @endif
                @endforeach

                <td class="no-print">
                    @if ($simpleActions)
                        @foreach ($simpleActions as $action)
                            @if (strtolower($action) == 'edit')

                                <span wire:click="editRecord( {{ $row->id }}, '{{ addslashes($model) }}' )"
                                    class="mx-2"
                                    style="cursor: pointer"
                                    data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </span>
                            @elseif(strtolower($action) == 'show')
                                <span wire:click="$dispatch('openDetailModalEvent', [{{ $row->id }}, '{{ addslashes($model) }}'] )"
                                    style="cursor: pointer" class="mx-2"
                                    data-bs-toggle="tooltip"  data-bs-original-title="Detail"
                                    >
                                    <i class="fas fa-eye text-info"></i>
                                </span>
                            @elseif(strtolower($action) == 'delete')
                                <span wire:click="deleteRecord({{ $row->id }} )"
                                    class="mx-2"
                                    style="cursor: pointer"
                                    data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                    <i class="fas fa-trash text-danger"></i>
                                </span>
                            @else
                                <!------- Default eg. route('users.user.edit', ['user' => 1]) --------->
                                <a href="{{ route(strtolower(Str::plural($modelName)) . '.' . strtolower(Str::singular($modelName)) . '.' . strtolower(Str::singular($action)), [strtolower($modelName) => $row->id]) }}"
                                    class="mx-2" data-bs-toggle="tooltip"
                                    style="cursor: pointer"
                                    data-bs-original-title="{{ucfirst($action)}}">
                                    <span
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        {{ ucfirst($action) }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    @endif

                    {{----------  HANDLING MORE ACTIONS (GROUPED AND UNGROUPED)  ---------}}
                    @if ($moreActions)
                        <span class="btn-group dropdown" data-bs-toggle="tooltip" data-bs-original-title="More">
                            <span class="px-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical text-secondary" style="cursor: pointer"></i>
                            </span>
                            <ul class="dropdown-menu dropdown-menu-end me-sm-n4 px-2 py-3" aria-labelledby="dropdownMenuButton">

                                @foreach ($moreActions as $key => $value)
                                    @php
                                        $isGrouped = is_string($key) && is_array($value);
                                        $actions = $isGrouped ? $value : [$value];
                                    @endphp

                                    @if($isGrouped)
                                        <span class="m-2 text-uppercase text-xs fw-bolder">{{ ucfirst($key) }}</span>
                                        <hr class="m-2 p-0 bg-gray-500" />
                                    @endif

                                    @foreach ($actions as $action)
                                        <li class="mb-2">
                                            @if(isset($action['route']))
                                               <a class="dropdown-item border-radius-md" wire:click="openLink('{{ $action['route'] }}', {{ json_encode(array_merge($action['params']?? [], ['id' => $row->id])) }})">
                                            @elseif(isset($action['updateModelField']) && isset($action['fieldName']) && isset($action['fieldValue']) && isset($action['actionName']))
                                                <a class="dropdown-item border-radius-md" onclick="Livewire.dispatch('updateModelFieldEvent',['{{$row->id}}', '{{$action['fieldName']}}', '{{$action['fieldValue']}}', '{{$action['actionName']}}', '{{$action['handleByEventHandlerOnly']}}'])">
                                            @else
                                                <a class="dropdown-item border-radius-md" href="javascript:void(0)">
                                            @endif

                                                @if(isset($action['icon']))
                                                    <i class="{{ $action['icon'] }}" style="margin-right: 1em"></i>
                                                @endif
                                                <span class="btn-inner--text">{{ $action['title'] ?? '' }}</span>
                                                </a>
                                        </li>

                                        @if(isset($action['hr']))
                                            <hr class="m-2 p-0 bg-gray-500" />
                                        @endif
                                    @endforeach
                                @endforeach

                            </ul>
                        </span>
                    @endif


                </td>

            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) }}" class="text-center py-4">No records found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
