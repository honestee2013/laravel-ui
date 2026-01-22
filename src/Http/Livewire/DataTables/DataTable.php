<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;

use Livewire\Component;
use Livewire\WithPagination;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableService;

use Illuminate\Support\Facades\Storage;
use QuickerFaster\LaravelUI\Traits\GUI\HasViewConfigurationTrait;

class DataTable extends Component
{
    use WithPagination, HasViewConfigurationTrait;

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
    public $pageQueryFilters = [];

    public $viewType;

    public $config;

    public $selectedItemId;

    protected $listeners = [
        "perPageEvent" => "changePerPage",
        "searchEvent" => "changeSearch",
        "showHideColumnsEvent" => "showHideColumns",
        'applyFilterEvent' => 'applyFilters',
        "recordDeletedEvent" => "resetSelection",
        'recordSavedEvent' => '$refresh',
        'refreshDataTable' => '$refresh',
        'updatedViewTypeEvent' => 'updatedViewType'
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
                $this->pageQueryFilters,
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

    public function openRoute($routeName, $params = [])
    {
        return redirect()->route($routeName, $params);
    }

    public function openUrl($url, $params = [])
    {
        $url = $url . (empty($params) ? '' : '?' . http_build_query($params));

        return redirect(strtolower($this->moduleName)."/".$url);
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
    
    // Authorize: can user Admin this?
    ///$this->authorize('view', $doc);


    $user_number = $doc->employee->employee_number ?? null;
    $docName = $doc->name ?? '';
    $docType = $doc->type ?? '';

    $filename = ($user_number ? $user_number . '_' : '') . ($docName ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $docName) : 'document') . ($docType ? '_' . $docType : '');
    $filename .= '.' . pathinfo($doc->$column, PATHINFO_EXTENSION); 
    
    return Storage::disk('public')->download($doc->$column, $filename);
}


public function updatedViewType($viewType) {
    $this->viewType = $viewType;
    $this->dispatch('$refresh');
}



// Decide where to house the following later
//////////////////////// START

// Check if group should be displayed in detail view
public function isGroupEmptyForDetail($groupType, $fields, $hiddenFields)
{
    return empty(array_diff($fields, $hiddenFields['onQuery'] ?? []));
}

// Check if field should be displayed
public function shouldDisplayFieldInDetail($field, $hiddenFields)
{
    return !in_array($field, $hiddenFields['onQuery'] ?? []);
}

// Get field label (from YAML or auto-generated)
public function getFieldLabel($field)
{
    return $this->fieldDefinitions[$field]['label'] ?? 
           ucwords(str_replace('_', ' ', $field));
}

// Check if field has a relation
public function hasRelation($field)
{
    return isset($this->fieldDefinitions[$field]["relationship"]) || isset($this->config["relations"]);  
}

// Get relation value (e.g., department.name)
public function getRelationValue($relationData)
{
    $relationship = $relationData["relationship"]?? null;
    $displayField = $relationData["displayField"]?? "id";
    if (!$relationship)
            return "N/A";

    return $relationship->{$displayField};
    
 
}

// Format field value (dates, booleans, etc.)
/*public function formatFieldValue($record, $field)
{
    
    
    $value = $record?->{$field};
    if (is_null($value)) return 'N/A';
    

    $fieldType = $this->fieldDefinitions[$field]["field_type"];


    // Handle dates
    if ($fieldType === 'datepicker') {
        return $value ? $value->format('M d, Y') : 'N/A';
    }
    
    // Handle selects
    /*if (isset($this->fieldDefinitions[$field]['options'])) {
        $options = $this->fieldDefinitions[$field]['options'];
        if (is_array($options) && isset($options[$value])) {
            return $options[$value];
        }
    }* /
    
    return $value;
}*/




// Check if a field is an employee relation
public function isEmployeeRelation($relationValue)
{
    
    $foreignTable = $relationValue->getTable();// $this->fieldDefinitions[$field]['foreign']['table'] ?? '';
    return in_array($foreignTable, ['employees', 'users']); // Adjust table names as needed
}

// Get employee tooltip HTML for a relation
public function getEmployeeTooltipHtml($employee)
{
    if (!$employee) return '';
    
    $fullName = e($employee->first_name . ' ' . $employee->last_name);
    $jobTitle = isset($employee->job_title) ? e($employee->job_title) : (isset($employee->employeePosition?->jobTitle?->title) ? e($employee->employeePosition->jobTitle->title) : 'N/A');
    $department = isset($employee->department?->name) ? e($employee->department->name) : 'N/A';
    $status = isset($employee->status) ? e($employee->status) : 'Active';
    
    $statusColor = match(strtolower($status)) {
        'active' => 'success',
        'terminated', 'inactive' => 'danger',
        default => 'warning'
    };
    
    // Get photo URL
    $photoUrl = null;
    if (isset($employee->photo) && $employee->photo) {
        $photoUrl = asset('storage/' . $employee->photo);
    } elseif (isset($employee->employeeProfile?->photo) && $employee->employeeProfile?->photo) {
        $photoUrl = asset('storage/' . $employee->employeeProfile->photo);
    } else {
        $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=4e73df&color=fff&size=80';
    }
    
    return "<div class='text-center p-2' style='min-width: 200px;'>
        <img src='{$photoUrl}' loading='lazy' class='rounded mb-2' style='width: 80px; height: 80px; object-fit: cover;'>
        <div class='fw-bold mb-1'>{$fullName}</div>
        <div class='text-muted small mb-1'>{$jobTitle}</div>
        <div class='text-muted small mb-2'>{$department}</div>
        <span class='badge bg-{$statusColor}'>{$status}</span>
    </div>";
}



public function getRelationData($record, $field)
{

    foreach ($this->config["relations"] as $relationName => $data) {
        if (isset($data["foreignKey"]) && $data["foreignKey"] == $field) {
            $displayField = $data["displayField"]?? "id"; 
            return ["relationship" => $record?->{$relationName}, "displayField" => $displayField];
        }
    }

}

// Get the related model object (not just display value)
public function getRelationValueObject($record, $field)
{

    foreach ($this->config["relations"] as $relationName => $data) {
        if (isset($data["foreignKey"]) && $data["foreignKey"] == $field) {
            return $record->{$relationName} ?? null;
        }
    }
            
    /*if (!isset($this->fieldDefinitions[$field]['foreign'])) {
        return null;
    }

    $relationName = $field; // Assuming relation method matches field name
    return $this->model->{$relationName} ?? null;*/
}

// Get the display value for relations (what's shown in the detail view)
public function getRelationDisplayValue($relationData)
{
    if (isset($relationData["relationship"]) && isset($relationData["displayField"]))
        return $relationData["relationship"]->{$relationData["displayField"]};
    return "N/A";
}



//////////////////////////////////// END



    public function render()
    {
        // Filter if record is selected
        if ($this->selectedItemId)
            $this->queryFilters[] = ['id', '=', $this->selectedItemId];

        // Remove hidden fields in query
        $hiddenOnQuery = $this->hiddenFields['onQuery'] ?? [];

        if ($this->selectedItemId) {
            $this->viewType = "detail";
        }

        $query = $this->dataTableService->buildQuery(
            $this->model,
            $this->columns,
            $this->fieldDefinitions,
            $this->search,
            $this->queryFilters,
            $this->pageQueryFilters,
            $this->sortField,
            $this->sortDirection,
            $hiddenOnQuery,
        );
        
        $data = $query->paginate($this->perPage);

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        $viewPath =  "qf::components.livewire.$UIFramework";
        $theView = "$viewPath.data-tables.data-{$this->viewType}"; // table, list, cards

        return view($theView, ['data' => $data])
            ;//->layout("$viewPath.layouts.app"); // 👈 important    
            
    }
}