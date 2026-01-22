{{-- resources/views/components/livewire/bootstrap/data-tables/partials/table-body.blade.php --}}

    <div class="table-responsive" style="min-height: 25em">
        <table class="table table-hover align-items-center mb-0 text-sm">
            <thead class="table-light">
                <tr>
                    {{-- Bulk Action Checkbox --}}
                    @if(isset($controls['bulkActions']))
                        <th class="ps-4" style="width: 50px;">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       wire:model.live.500ms="selectAll"
                                       id="selectAll">
                                <label class="form-check-label" for="selectAll"></label>
                            </div>
                        </th>
                    @endif

                    {{-- Table Headers --}}
                    @foreach ($columns as $column)
                        @if (in_array($column, $visibleColumns))
                            <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 px-3"
                                wire:click="sortColumn('{{ $column }}')"
                                style="cursor: pointer; position: relative;"
                                aria-sort="{{ $sortField === $column ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="header-label">
                                        {{ isset($fieldDefinitions[$column]['label']) ? 
                                           ucwords($fieldDefinitions[$column]['label']) : 
                                           ucwords(str_replace('_', ' ', $column)) }}
                                    </span>
                                    @if ($sortField === $column)
                                        <span class="sort-indicator">
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-primary"></i>
                                        </span>
                                    @else
                                        <span class="sort-indicator text-muted">
                                            <i class="fas fa-sort"></i>
                                        </span>
                                    @endif
                                </div>
                            </th>
                        @endif
                    @endforeach

                    {{-- Actions Column --}}
                    @if ($simpleActions)
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-2 text-end" style="width: 100px;">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr class="{{ $index % 2 === 0 ? 'table-row-even' : 'table-row-odd' }} {{ in_array($row->id, $selectedRows) ? 'table-row-selected' : '' }}">
                        {{-- Bulk Action Checkbox --}}
                        @if(isset($controls['bulkActions']))
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           wire:click="toggleRowSelected"
                                           wire:model="selectedRows" 
                                           value="{{ $row->id }}"
                                           id="row_{{ $row->id }}">
                                    <label class="form-check-label" for="row_{{ $row->id }}"></label>
                                </div>
                            </td>
                        @endif

                        {{-- Data Cells --}}
                        @foreach ($columns as $column)
                            @if (in_array($column, $visibleColumns))
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center">
                                        {{-- Avatar for name columns --}}
                                        @if(in_array($column, ['name', 'first_name', 'employee.first_name', 'employee.name']))
                                            @php
                                                $avatarUrl = $this->getAvatarUrl($row, $column);
                                            @endphp
                                            @if($avatarUrl)
                                                <div class="avatar avatar-sm me-3">
                                                    <img src="{{ $avatarUrl }}" 
                                                         alt="{{ $row->{$column} }}" 
                                                         class="rounded-circle"
                                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($row->{$column}) }}&background=4e73df&color=fff&size=32'">
                                                </div>
                                            @endif
                                        @endif
                                        
                                        <div class="cell-content">
                                            <x-qf::livewire.bootstrap.data-tables.partials.data-table-cell
                                                :row="$row"
                                                :column="$column"
                                                :fieldDefinitions="$fieldDefinitions"
                                                :multiSelectFormFields="$multiSelectFormFields"
                                                :index="$index"
                                            />
                                        </div>
                                    </div>
                                </td>
                            @endif
                        @endforeach

                        {{-- Actions Cell --}}
                        <td class="text-end pe-4">
                            <x-qf::livewire.bootstrap.data-tables.partials.data-table-actions
                                :row="$row"
                                :model="$model"
                                :modelName="$modelName"
                                :simpleActions="$simpleActions"
                                :moreActions="$moreActions"
                                :index="$index"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + (isset($controls['bulkActions']) ? 1 : 0) + ($simpleActions ? 1 : 0) }}" 
                            class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted mb-2">No records found</h5>
                                @if($search)
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                    <button class="btn btn-sm btn-outline-primary mt-2"
                                            wire:click="$set('search', '')">
                                        Clear Search
                                    </button>
                                @else
                                    <p class="text-muted">Get started by adding your first record</p>
                                    @if(in_array('create', $moreActions ?? []))
                                        <button class="btn btn-sm btn-primary mt-2"
                                                wire:click="$dispatch('openModal', {component: 'data-table-manager', arguments: {isEditMode: false}})">
                                            <i class="fas fa-plus me-1"></i> Add {{ $modelName }}
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


@push('styles')
<style>
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        border-bottom: 2px solid var(--border-color);
        background-color: var(--light-color);
        position: sticky;
        top: 0;
        z-index: 10;
        backdrop-filter: blur(10px);
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
        /*border-bottom: 1px solid var(--border-color);*/
    }
    
    .table tbody tr:hover {
        /*background-color: rgba(78, 115, 223, 0.05);*/
        /*border-buttom: 2px solid var(--primary-color);*/
        /*box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);*/
        /*transform: translateX(2px);*/
        
    }
    
    .table-row-selected {
        background-color: rgba(233, 236, 239, 0.5) !important;
        border-left: 3px solid var(--primary-color);
    }
    
    .table-row-even {
        /*background-color: rgba(233, 236, 239, 0.5);*/
        /*border-buttom: 1px solid var(--border-color);*/
        border-bottom: 1px solid rgba(233, 236, 239, 0.5);

    }
    
    .table-row-odd {
        background-color: white;

    }
    
    .header-label {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .sort-indicator {
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }
    
    th:hover .sort-indicator {
        opacity: 1;
    }
    
    .avatar {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }
    
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .cell-content {
        min-width: 0; /* Enable text truncation */
    }
    
    .empty-state {
        padding: 2rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }
        
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
        }
        
        .table tbody td {
            display: block;
            text-align: right;
            padding: 0.5rem 0;
            border: none;
            position: relative;
            padding-left: 50%;
        }
        
        .table tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: calc(50% - 2rem);
            text-align: left;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .table tbody td:last-child {
            border-bottom: none;
        }
        
        .avatar {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .cell-content {
            text-align: right;
        }
    }
</style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make table rows clickable for selection/detail view
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on checkbox, button, or link
                if (e.target.matches('input[type="checkbox"], button, a, .dropdown, .dropdown *')) {
                    return;
                }
                
                const rowId = this.getAttribute('data-row-id');
                if (rowId) {
                    Livewire.dispatch('openDetailModalEvent', [rowId, '{{ addslashes($model) }}']);
                }
            });
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>