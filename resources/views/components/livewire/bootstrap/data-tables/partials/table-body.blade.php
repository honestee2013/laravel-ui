<table id="dataTable" class="table align-items-center mb-0">
    <thead>
        <tr>
            {{-- Bulk Action Checkbox --}}
            @if(isset($controls['bulkActions']))
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                    <div class="form-check">
                        <input class="form-check-input" style="width: 1.6em; height:1.6em"
                            type="checkbox" wire:model.live.500ms="selectAll">
                    </div>
                </th>
            @endif

            {{-- Table Headers --}}
            @foreach ($columns as $column)
                @if (in_array($column, $visibleColumns))
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 px-2 round-top"
                        wire:click="sortColumn('{{ $column }}')" style="height: 0.5em;"
                        aria-sort="{{ $sortField === $column ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                        <div class="d-flex justify-content-between p-2 px-3 {{ $sortField === $column ? 'rounded-pill bg-gray-100' : '' }}">
                            <span>
                                {{ isset($fieldDefinitions[$column]['label']) ? 
                                   ucwords($fieldDefinitions[$column]['label']) : 
                                   ucwords(str_replace('_', ' ', $column)) }}
                            </span>
                            @if ($sortField === $column)
                                <span class="btn-inner--icon">
                                    <i class=fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                </span>
                            @endif
                        </div>
                    </th>
                @endif
            @endforeach

            {{-- Actions Column --}}
            @if ($simpleActions)
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                    Actions
                </th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($data as $row)
            <tr>
                {{-- Bulk Action Checkbox --}}
                @if(isset($controls['bulkActions']))
                    <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 no-print">
                        <div class="form-check">
                            <input class="form-check-input" style="width: 1.6em; height:1.6em"
                                wire:click="toggleRowSelected"
                                type="checkbox" wire:model="selectedRows" value="{{ $row->id }}">
                        </div>
                    </td>
                @endif

                {{-- Data Cells --}}
                @foreach ($columns as $column)
                    @if (in_array($column, $visibleColumns))
                        <td style="white-space: normal; word-wrap: break-word;">
                            <p class="text-xs font-weight-bold mb-0">
                                <x-qf::livewire.bootstrap.data-tables.partials.data-table-cell
                                    :row="$row"
                                    :column="$column"
                                    :fieldDefinitions="$fieldDefinitions"
                                    :multiSelectFormFields="$multiSelectFormFields"
                                />
                            </p>
                        </td>
                    @endif
                @endforeach

                {{-- Actions Cell --}}
                <td class="no-print">
                    <x-qf::livewire.bootstrap.data-tables.partials.data-table-actions
                        :row="$row"
                        :model="$model"
                        :modelName="$modelName"
                        :simpleActions="$simpleActions"
                        :moreActions="$moreActions"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) + (isset($controls['bulkActions']) ? 1 : 0) + ($simpleActions ? 1 : 0) }}" 
                    class="text-center py-4">
                    No records found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

