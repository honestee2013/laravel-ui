<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;

use Livewire\Component;
use Livewire\WithPagination;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableService;

use Illuminate\Support\Facades\Storage;

class DataTable extends Component
{
    use WithPagination;

    // Component properties
    public $model;
    public $controls;
    public $columns;
    public $fieldDefinitions;
    public $multiSelectFormFields;
    public $simpleActions;
    public $moreActions;
    public $hiddenFields;
    public $modelName;
    public $moduleName;
    public $visibleColumns;
    public $selectedColumns;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $selectedRows = [];
    public $selectAll = false;
    public $search = '';
    public $queryFilters = [];

    protected $listeners = [
        "perPageEvent" => "changePerPage",
        "searchEvent" => "changeSearch",
        "showHideColumnsEvent" => "showHideColumns",
        'applyFilterEvent' => 'applyFilters',
        "recordDeletedEvent" => "resetSelection",
        'recordSavedEvent' => '$refresh',
        'refreshDataTable' => '$refresh',
    ];

    protected $dataTableService;

    public function boot(DataTableService $dataTableService)
    {
        $this->dataTableService = $dataTableService;
    }

    public function mount()
    {
        // Initialization if needed
    }

    public function resetSelection()
    {
        $this->selectedRows = [];
        $this->selectedColumns = null;
        $this->selectAll = false;
    }

    public function sortColumn($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->dispatch('sortColumnEvent', [
            "column" => $field, 
            "direction" => $this->sortDirection
        ]);
    }

    public function changePerPage($perPage)
    {
        $this->perPage = $perPage;
        $this->dispatch("changePerPageEvent", $perPage);
    }

    public function changeSearch($search)
    {
        $this->search = $search;
        $this->dispatch("changeSearchEvent", $search);
    }

    public function showHideColumns($selectedColumns)
    {
        $this->visibleColumns = $selectedColumns;
    }

    public function applyFilters($filters)
    {
        $this->queryFilters = [];
        
        foreach ($filters as $field => $filter) {
            if (!isset($filter['operator']) || !isset($filter['value'])) {
                continue;
            }
            
            $operator = trim($filter['operator']);
            $value = trim($filter['value']);
            
            if ($operator !== '' && $value !== '') {
                $this->queryFilters[] = [$field, $operator, $value];
            }
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = $this->dataTableService->buildQuery(
                $this->model,
                $this->columns,
                $this->fieldDefinitions,
                $this->search,
                $this->queryFilters,
                $this->sortField,
                $this->sortDirection,
                $this->hiddenFields['onQuery'] ?? []
            );
            
            $this->selectedRows = $query->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
        
        $this->dispatch("toggleRowsSelectedEvent", $this->selectedRows);
    }

    public function toggleRowSelected()
    {
        $this->dispatch("toggleRowsSelectedEvent", $this->selectedRows);
    }

    public function editRecord($id, $model, $modalId = 'addEditModal')
    {
        $this->dispatch("openEditModalEvent", $id, $model, $modalId);
    }

    public function showDetail($id)
    {
        $this->dispatch("openShowItemDetailModalEvent", $id);
    }

    public function deleteRecord($id)
    {
        $this->dispatch("confirmDeleteEvent", $id);
    }

    public function openLink($routeName, $params = [])
    {
        return redirect()->route($routeName, $params);
    }

    public function downloadFile($path) {
        // Check permissions, existence, etc. as needed
        $filename = basename($path);
        return Storage::disk('public')->download("uploads/documents/{$filename}");
    }



    
public function downloadDocument($documentId, $column)
{
    $doc = $this->model::findOrFail($documentId);
   
    if (!$doc || !$doc->$column) {
        abort(404, 'Document not found.');
    }
    
    // Authorize: can user access this?
    ///$this->authorize('view', $doc);


    $user_number = $doc->employee->employee_number ?? null;
    $docName = $doc->name ?? '';
    $docType = $doc->type ?? '';

    $filename = ($user_number ? $user_number . '_' : '') . ($docName ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName) : 'document') . ($docType ? '_' . $docType : '');
    $filename .= '.' . pathinfo($doc->$column, PATHINFO_EXTENSION); 
    
    return Storage::disk('public')->download($doc->$column, $filename);
}



    public function render()
    {
        $hiddenOnQuery = $this->hiddenFields['onQuery'] ?? [];
        
        $query = $this->dataTableService->buildQuery(
            $this->model,
            $this->columns,
            $this->fieldDefinitions,
            $this->search,
            $this->queryFilters,
            $this->sortField,
            $this->sortDirection,
            $hiddenOnQuery
        );
        
        $data = $query->paginate($this->perPage);

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath =  "qf::components.livewire.$UIFramework";
        return view("$viewPath.data-tables.data-table", ['data' => $data])
            ->layout("$viewPath.layouts.app"); // 👈 important    
            
    }
}