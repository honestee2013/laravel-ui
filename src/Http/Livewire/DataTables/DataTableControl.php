<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Log\Models\UserActivityLog;

use QuickerFaster\LaravelUI\Services\Exports\DataExportExcel;
use QuickerFaster\LaravelUI\Services\Exports\DataExportExcelTemplate;
use QuickerFaster\LaravelUI\Services\Exports\DataTableExportCSV;
use QuickerFaster\LaravelUI\Services\Imports\DataImport;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Schema;


use QuickerFaster\LaravelUI\Services\AccessControl\AccessControlPermissionService;

use QuickerFaster\LaravelUI\Services\GUI\SweetAlertService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableValidationService;


class DataTableControl extends Component
{

    use WithFileUploads;

    ////////// DEFINE BY THE PARENT ///////////
    public $controls;
    public $columns;
    public $hiddenFields;
    public $visibleColumns;
    public $model;
    public $fieldDefinitions;
    public $multiSelectFormFields;
    public $modelName;
    public $moduleName;


    ////////// DEFINE BY THE CLASS ///////////
    public $file;
    public $bulkAction = '';
    public $selectedColumns;
    public $search = '';
    public $perPage;

    public $sortField;
    public $sortDirection = "asc";

    public $selectedRows = [];
    public array $filters = [];


    public $viewType;


    protected $listeners = [
        "toggleRowsSelectedEvent" => "toggleRowsSelected",
        "sortColumnEvent" => "updateSortParameters",
        "changeSearchEvent" => "changeSearch",
        "changePerPageEvent" => "changePerPage",
        'updatedViewTypeEvent' => 'updatedViewType',

    ];



    public function mount()
    {
        // For selecting columns to SHOW/HIDE on the data table
        $this->selectedColumns = [...$this->visibleColumns];
    }




    public function updated($field, $value)
    {
        // Event name = 'Field name' + 'Event'. eg perPageEvent
        $event = "{$field}Event";
        $this->dispatch($event, $value);

    }

    public function updatedViewType($viewType)
    {
        $this->viewType = $viewType;
        $this->dispatch('$refresh');
    }


    // Update visibleColumns with the selectedColumns
    public function showHideSelectedColumns()
    {
        // Update visibleColumns with the selectedColumns
        $this->visibleColumns = $this->selectedColumns;
        $this->dispatch("showHideColumnsEvent", $this->selectedColumns);

    }







    public function toggleRowsSelected($rowIds)
    {
        $this->selectedRows = $rowIds;
    }


    public function applyBulkAction()
    {
        if ($this->bulkAction == "delete") {
            return $this->dispatch('confirmDeleteEvent', $this->selectedRows);
        } else if (str_contains($this->bulkAction, ":")) {
            $data = explode(":", $this->bulkAction);
            //public function updateModelField($modelIds, $fieldName, $fieldValue)
            $this->dispatch('updateModelFieldEvent', $this->selectedRows, $data[0], $data[1]);
        } else { // Export
            return $this->exportSelectedRows();
        }
    }




    public function applyFilters()
    {
        $this->dispatch('applyFilterEvent', $this->filters);
    }






    private function exportSelectedRows()
    {
        // File type
        $fileType = "xlsx";
        if ($this->bulkAction == "exportCSV")
            $fileType = "csv";
        else if ($this->bulkAction == "exportPDF")
            $fileType = "pdf";

        return $this->export($fileType, "", "selected");
    }



    // Export XLS, CSV, PDF files
    public function export($fileType, $fileName = "", $rows = "table")
    {
        // Check if the user has permission to perform the action
        if (!AccessControlPermissionService::checkPermission('export', $this->modelName)) {
            SweetAlertService::showError($this, "Error!", AccessControlPermissionService::MSG_PERMISSION_DENIED);
            return;
        }


        //if (!$fileName && $this->model)
        //$fileName = class_basename($this->model);
        if (!$fileName && $this->modelName) {
            $timestamp = now()->format('Y-m-d_H-i'); // 2024-06-15_14-30
            $fileName = $this->moduleName . "_" . \Str::kebab($this->modelName) . "_export_{$timestamp}.xlsx";

        }

        if ($fileType === "xlsx") {
            return $this->exportToExcel($fileName, $rows);
        } elseif ($fileType === "csv") {
            return $this->exportToCSV($fileName, $rows);
        } elseif ($fileType === "pdf") {
            return $this->exportToPDF($fileName, $rows);
        } elseif ($fileType === "print") {
            //return $this->printPreview($rows); // 👈 Here it is integrated
        }
    }



    private function exportToExcel($fileName = "", $rows = "table")
    {

        $modelClass = '\\' . ltrim($this->model, '\\');
        $query = (new $modelClass)->newQuery();
        $hiddenFields = $this->hiddenFields["onQuery"];

        // Apply filters and search first
        $this->applySearchAndFilters($query, $hiddenFields);

        // Then restrict to selected IDs if needed
        if ($rows === "selected" && !empty($this->selectedRows)) {
            $query->whereIn('id', $this->selectedRows);
        }

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $data = $query->get();

        $excel = app(Excel::class);

        return $excel->download(
            new DataExportExcel($data, $this->fieldDefinitions, $this->visibleColumns),
            $fileName
        );
    }



    private function exportToCSV($fileName = "", $rows = "table")
    {
        $modelClass = '\\' . ltrim($this->model, '\\');
        $query = (new $modelClass)->newQuery();
        $hiddenFields = $this->hiddenFields["onQuery"];

        $this->applySearchAndFilters($query, $hiddenFields);

        if ($rows === "selected" && !empty($this->selectedRows)) {
            $query->whereIn('id', $this->selectedRows);
        }

        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $data = $query->get();
        $excel = app(Excel::class);

        return $excel->download(
            new DataTableExportCSV($data, $this->fieldDefinitions, $this->visibleColumns),
            $fileName . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }




    public function exportToPDF($fileName, $rows = "table")
    {

        $modelClass = '\\' . ltrim($this->model, '\\');
        $query = (new $modelClass)->newQuery();
        $hiddenFields = $this->hiddenFields["onQuery"];

        $this->applySearchAndFilters($query, $hiddenFields);

        if ($rows === "selected" && !empty($this->selectedRows)) {
            $query->whereIn('id', $this->selectedRows);
        }

        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $data = $query->get();
        $columns = $this->visibleColumns;
        $fieldDefs = $this->fieldDefinitions;

        $pdf = \PDF::loadView('system.views::exports.data-table-pdf', compact('data', 'columns', 'fieldDefs'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $fileName . '.pdf');
    }




    protected function applySearchAndFilters(&$query, $hiddenFields)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) use ($hiddenFields) {
                foreach ($this->fieldDefinitions as $fieldName => $definition) {
                    if (in_array($fieldName, $hiddenFields))
                        continue;

                    if (isset($definition['relationship'])) {
                        $rel = $definition['relationship'];
                        if (!isset($rel['type'], $rel['display_field'], $rel['dynamic_property']))
                            continue;

                        $displayField = explode(".", $rel['display_field']);
                        $displayField = count($displayField) > 1 ? "id" : $displayField[0];

                        $q->orWhereHas(
                            $rel['dynamic_property'],
                            fn($r) =>
                            $r->where($displayField, 'like', '%' . $this->search . '%')
                        );
                    } else {
                        $q->orWhere($fieldName, 'like', '%' . $this->search . '%');
                    }
                }
            });
        }

        foreach ($this->queryFilters ?? [] as $filter) {
            if (is_array($filter) && count($filter) === 3) {
                [$field, $operator, $value] = $filter;
                $column = "";

                if (str_contains($field, ".")) {
                    [$field, $column] = explode(".", $field);
                }

                if (isset($this->fieldDefinitions[$field]['relationship']['dynamic_property']) && $column) {
                    $dp = $this->fieldDefinitions[$field]['relationship']['dynamic_property'];
                    $query->whereHas($dp, fn($q) => $q->where($column, $operator, $value));
                } else {
                    $operator === 'in'
                        ? $query->whereIn($field, $value)
                        : $query->where($field, $operator, $value);
                }
            }
        }
    }




    public function updateSortParameters($params)
    {
        $this->sortField = $params["column"];
        $this->sortDirection = $params["direction"];
    }


    public function changePerPage($perPage)
    {
        $this->perPage = $perPage;
    }


    public function changeSearch($search)
    {
        $this->search = $search;
    }




    public function printTable()
    {
        // Check if the user has permission to perform the action
        if (!AccessControlPermissionService::checkPermission('print', $this->modelName)) {
            SweetAlertService::showError($this, "Error!", AccessControlPermissionService::MSG_PERMISSION_DENIED);
            return;
        }

        $this->dispatch('print-table-event');
    }





public function import()
{
    // 1. Permission Check
    if (!AccessControlPermissionService::checkPermission('import', $this->modelName)) {
        SweetAlertService::showError($this, "Error!", AccessControlPermissionService::MSG_PERMISSION_DENIED);
        return;
    }


    // Validate inputs
    $validationRules = (new DataTableValidationService())->getDynamicValidationRules(
        $this->fieldDefinitions,
        false, // treat as new
        null, // No any selected field
        $this->hiddenFields
    );


    // 2. Resolve Table Name and Columns
    $tableName = $this->getTableName();
    $columns = $this->getColumnsForImportAndExport($tableName);
    
    $result =  (new DataImport($this->model, $columns, $validationRules, $this->fieldDefinitions))->import($this->file->path(), $this);
    $this->dispatch('refreshDataTable');
    return $result;

}

public function downloadTemplate($withData = false)
{
    $modelClass = $this->resolveModelClass();

    if ($modelClass && class_exists($modelClass)) {
        $tableName = (new $modelClass)->getTable();
        $columns = $this->getColumnsForImportAndExport($tableName);
        
        // Use query() to avoid "all() on null" issues
        $data = $modelClass::query()->get();

        $excel = app(Excel::class);
        return $excel->download(
            new DataExportExcelTemplate($data, $this->fieldDefinitions, $columns, $withData),
            $this->modelName . "-template.xlsx"
        );
    }
    
    // Optional: Handle case where model class doesn't exist
    return null;
}

/**
 * Helper to resolve the full model class name
 */
private function resolveModelClass(): string
{
    return '\\' . ltrim($this->model, '\\');
}

/**
 * Helper to get table name from the model property
 */
private function getTableName(): ?string
{
    $class = $this->resolveModelClass();
    return class_exists($class) ? (new $class)->getTable() : null;
}

private function getColumnsForImportAndExport(?string $tableName): array
{
    if (!$tableName) {
        return [];
    }

    // 1. Get all fields defined in the component
    $allColumns = array_keys($this->fieldDefinitions);

    // 2. Identify exclusions (Hidden fields + Relationships)
    $hiddenFields = $this->hiddenFields["onNewForm"] ?? [];
    $relationshipFields = [];

    // Remove hasMany & belogsToMany
    foreach ($this->fieldDefinitions as $fieldName => $definition) {
        if (isset($definition['relationship']) && $definition['relationship']['type'] != "belongsTo") {
            $relationshipFields[] = $fieldName;
        }
    }

    // 3. Subtract exclusions from defined columns
    $toExclude = array_merge($hiddenFields, $relationshipFields);
    $definedVisible = array_diff($allColumns, $toExclude);

    // 4. Cross-reference with physical database columns
    $actualTableColumns = Schema::getColumnListing($tableName);
    
    // Only return columns that exist in BOTH the definitions and the DB
    $result =  array_values(array_intersect($definedVisible, $actualTableColumns));
  
    return $result;
}





    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath = "qf::components.livewire.$UIFramework";
        return view("$viewPath.data-tables.data-table-control", [])
        ;//->layout("$viewPath.layouts.app"); // 👈 important   
    }


}


