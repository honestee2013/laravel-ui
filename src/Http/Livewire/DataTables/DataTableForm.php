<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use QuickerFaster\LaravelUI\Services\GUI\SweetAlertService;


use QuickerFaster\LaravelUI\Services\DataTables\DataTableFormService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableValidationService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableRelationshipService;

use QuickerFaster\LaravelUI\Traits\DataTable\DataTableImageHandlerTrait;
use QuickerFaster\LaravelUI\Traits\GUI\HasAutoGenerateFieldTrait;


class DataTableForm extends Component
{
    use WithFileUploads, DataTableImageHandlerTrait, HasAutoGenerateFieldTrait;
    
    // Services
    protected $formService;
    protected $validationService;
    protected $relationshipService;
    
    // Component properties
    public $model;
    public $modelName;
    public $moduleName;
    public $fieldDefinitions;
    public $fieldGroups;
    public $hiddenFields;
    public $readOnlyFields;
    public $columns;
    public $fields = [];
    public $multiSelectFormFields = [];
    public $singleSelectFormFields = [];
    public $selectedItemId;
    public $isEditMode = false;
    public $uploads = [];
    public $config;
    public $modalId;
    public $messages = [];
    public $selectedRows = [];
    
    protected $listeners = [
        'openEditModalEvent' => 'openEditModal',
        'openAddModalEvent' => 'openAddModal',
        'openDetailModalEvent' => 'openDetailModal',
        'deleteSelectedEvent' => 'deleteSelected',
        'confirmDeleteEvent' => 'confirmDelete',
        'resetFormFieldsEvent' => 'resetFields',
        'submitDatatableFormEvent' => 'saveRecord',
        'refreshFieldsEvent' => 'refreshFields',
        'updateModelFieldEvent' => 'updateModelField',

        'openCropImageModalEvent' => 'openCropImageModal'

    ];
    
    public function boot(
        DataTableFormService $formService,
        DataTableValidationService $validationService,
        DataTableRelationshipService $relationshipService
    ) {
        $this->formService = $formService;
        $this->validationService = $validationService;
        $this->relationshipService = $relationshipService;
    }
    
    public function mount()
    {
        $this->dispatch("addModalFormComponentStackEvent", [
            'modalId' => $this->modalId, 
            'componentId' => $this->getId()
        ]);
        
        // Initialize fields with default values
        foreach ($this->fieldDefinitions as $field => $definition) {
            $this->fields[$field] = $definition['default'] ?? null;
        }

    }


    
    
    public function saveRecord($modalId)
    {
        try {
            ///$this->authorizeAction();
            
            // Validate inputs
            $rules = $this->validationService->getDynamicValidationRules(
                $this->fieldDefinitions,
                $this->isEditMode,
                $this->selectedItemId,
                $this->hiddenFields
            );
            
            $this->validate($rules, $this->messages);
            
            // Process record
            $record = $this->isEditMode 
                ? $this->model::findOrFail($this->selectedItemId)
                : new $this->model();
                
            // Handle file uploads
            $this->fields = $this->formService->handleUploadedFiles($record, $this->fields);

            // Prepare data for save
            $data = $this->prepareDataForSave($record);

            // Save record
            DB::transaction(function () use ($record, $data, $modalId) {
                if ($this->isEditMode) {
                    $oldRecord = $record->toArray();
                    $record->update($data);
                    $this->dispatchEvent('updated', $oldRecord, $record->toArray());
                } else {
                    $record = $this->model::create($data);
                    $this->selectedItemId = $record->id;
                    $this->dispatchEvent('created', [], $record->toArray());
                }
                
                // Handle relationships
                $this->relationshipService->handleRelationships(
                    $record,
                    $this->fieldDefinitions,
                    $this->multiSelectFormFields,
                    $this->singleSelectFormFields,
                    $this->fields
                );
                
                $this->dispatchSuccessEvent($modalId);
            });
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    protected function prepareDataForSave($record)
    {
        // Filter allowed fields
        $formType = $this->isEditMode ? 'onEditForm' : 'onNewForm';
        $allowedFields = array_diff(
            $this->columns, // remove hidden fields from allowed fields
            $this->hiddenFields[$formType] ?? []
        );
                     
        $data = array_filter(
            $this->fields,
            fn($key) => in_array($key, $allowedFields),
            ARRAY_FILTER_USE_KEY
        );

        // Hash passwords
        $data = $this->formService->hashPasswordFields(
            $data, 
            $this->isEditMode, 
            $this->isEditMode ? $record : null
        );
        
        // Add audit trail fields
        $action = $this->isEditMode ? 'updated' : 'created';
        $data = $this->formService->addAuditTrailFields($data, $action, $this->config);

        $data = $this->addSelectedSingleFieldsToData($data);
      
        return $data;
    }


    protected function addSelectedSingleFieldsToData($data)
    {
        foreach ($this->singleSelectFormFields as $field => $value) {
            $data[$field] = $value;
        }
        return $data;
    }


    


    
    protected function authorizeAction()
    {
        $action = $this->isEditMode ? 'update' : 'create';
        
        if (!auth()->user()->can($action, $this->model)) {
            abort(403, 'Unauthorized action.');
        }
    }
    
    protected function dispatchSuccessEvent($modalId)
    {
        /*$this->dispatch('notify', [ // browser event
            'type' => 'success',
            'message' => 'Record saved successfully.'
        ]);*/

        // Display saving success message
        SweetAlertService::showSuccess($this, "Success!", "Record saved successfully.");

        
        // Close the modal, reset fields and refresh the table after saving
        $this->dispatch('closeModalEvent', ["modalId" => $modalId]);  // To show modal
        $this->dispatch('refreshFieldsEvent');  // To update  modal form field dropdown options data
        if (!$this->isEditMode)
            $this->resetFields(); // Next new form should be blank

        ///$this->dispatch('refreshDataTable');
        $this->dispatch("recordSavedEvent"); // Table refresh
        $this->dispatch('$refresh');  // To update  modal modal field dropdown options data
    }
    
    protected function dispatchEvent($eventName, $oldData, $newData)
    {
        $eventClass = "App\\Events\\DataTable\\{$this->modelName}{$eventName}";
        
        if (class_exists($eventClass)) {
            event(new $eventClass($oldData, $newData, auth()->id()));
        }
    }
    
    protected function handleError(\Exception $e)
    {
        Log::error('DataTableForm Error: ' . $e->getMessage());
        
        /*$this->dispatch('notify', [ // browser event
            'type' => 'error',
            'message' => 'An error occurred while saving the record.'
        ]);*/
        SweetAlertService::showError($this, "Error!", "An error occurred while saving the record.");

        if (app()->environment('local')) {
            throw $e;
        }
    }
    
    public function openEditModal($id, $model, $modalId = 'addEditModal')
    {
        if ($this->model !== $model) return;
        
        ///$this->authorize('update', $model::findOrFail($id));
        
        $this->selectedItemId = $id;
        $this->isEditMode = true;
        
        $record = $model::findOrFail($id);
        
        // Populate fields
        foreach ($this->fieldDefinitions as $field => $definition) {
            if (!str_contains($field, 'password')) {
                $this->fields[$field] = $record->$field;
            }
        }
        
        // Populate relationship fields
        $this->populateRelationshipFields($record);
        // Populate single select fields
        $this->populateSingleSelectFields($record);
        
        $this->dispatch('open-modal-event', ['modalId' => $modalId]); // browser event
    }


    public function populateSingleSelectFields($record)
    {
        // iterate through the fieldDefinitions
        // if field has options and no realtionship defined, then it's a single select
        foreach ($this->fieldDefinitions as $field => $definition) {
            if (isset($definition['options']) && !isset($definition['relationship'])) {
                $this->singleSelectFormFields[$field] = $record->$field;

            }
        }
    }




   ///////////////////// SHOW DETAIL MODAL //////////////////
    public function openDetailModal($id, $model)
    {

        // Check if the user has permission to perform the action
        /*if (!AccessControlPermissionService::checkPermission( 'edit', $this->modelName)) {
            SweetAlertService::showError($this, "Error!", AccessControlPermissionService::MSG_PERMISSION_DENIED);
            return;
        }*/


        // Load the selected item details from the database
        $this->selectedItem = $model::findOrFail($id);
        // Emit event to trigger the modal
        $this->dispatch('changeSelectedItemEvent', $id);
        //$this->dispatch('open-show-item-detail-modal');
        $this->dispatch('open-modal-event', ["modalId" => "detail", "modalClass" => "childModal"]);
    }



    
    protected function populateRelationshipFields($record)
    {
        foreach ($this->fieldDefinitions as $fieldName => $definition) {
            if (isset($definition['relationship'])) {
                $relationshipType = $definition['relationship']['type'] ?? null;
                $dynamicProperty = $definition['relationship']['dynamic_property'] ?? null;
                
                if ($relationshipType && $dynamicProperty && $record->$dynamicProperty) {
                    if (in_array($relationshipType, ['hasMany', 'belongsToMany', 'morphMany', 'morphToMany'])) {
                        $this->multiSelectFormFields[$fieldName] = $record->$dynamicProperty->pluck('id')->toArray();
                    } else if (in_array($relationshipType, ['belongsTo', 'morphTo'])) {
                        $this->singleSelectFormFields[$fieldName] = $record->$fieldName;
                    }
                }
            }
        }
    }
    
    public function openAddModal()
    {
        /////$this->authorize('create', $this->model);
        
        $this->resetFields();
        $this->isEditMode = false;
        
        $this->dispatch('changeFormModeEvent', ['mode' => 'new']);
        $this->dispatch('open-modal-event', ['modalId' => 'addEditModal']); // browser event

    }
    
    public function resetFields()
    {
        $this->fields = [];
        $this->multiSelectFormFields = [];
        $this->singleSelectFormFields = [];
        $this->selectedItemId = null;
        $this->isEditMode = false;
        
        // Reset to default values
        foreach ($this->fieldDefinitions as $field => $definition) {
            $this->fields[$field] = $definition['default'] ?? null;
        }
    }
    
    public function confirmDelete($ids)
    {
        ///$this->authorize('delete', $this->model);
        
        $this->selectedRows = is_array($ids) ? $ids : [$ids];
        
        $this->dispatch('confirm-delete', [ // browser event
            'message' => 'Are you sure you want to delete the selected records?'
        ]);
    }
    
    public function deleteSelected()
    {
        ///$this->authorize('delete', $this->model);
        
        DB::transaction(function () {
            foreach ($this->selectedRows as $id) {
                $record = $this->model::findOrFail($id);
                $record->delete();
                
                $this->dispatchEvent('deleted', $record->toArray(), []);
            }
        });
        
        /*$this->dispatch('notify', [ // browser event
            'type' => 'success',
            'message' => 'Records deleted successfully.'
        ]);*/
        SweetAlertService::showSuccess($this, "Success!", "Records deleted successfully.");


        
        
        $this->dispatch('refreshDataTable');
        $this->selectedRows = [];
    }
    
    public function updateModelField($modelIds, $fieldName, $fieldValue, $actionName = null)
    {
        ///$this->authorize('update', $this->model);
        
        $modelIds = is_array($modelIds) ? $modelIds : [$modelIds];
        
        DB::transaction(function () use ($modelIds, $fieldName, $fieldValue) {
            $this->model::whereIn('id', $modelIds)->update([$fieldName => $fieldValue]);
            
            foreach ($modelIds as $id) {
                $record = $this->model::findOrFail($id);
                $this->dispatchEvent('updated', $record->toArray(), array_merge($record->toArray(), [$fieldName => $fieldValue]));
            }
        });
        
        /*$this->dispatch('notify', [ // browser event
            'type' => 'success',
            'message' => 'Field updated successfully.'
        ]);*/
        SweetAlertService::showSuccess($this, "Success!", "Records updated successfully.");

        
        $this->dispatch('refreshDataTable');
    }





    
    public function refreshFields()
    {
        // Refresh options for select fields
        foreach ($this->fieldDefinitions as $fieldName => $definition) {
            if (isset($definition['relationship']['model'])) {
                $modelClass = $definition['relationship']['model'];
                $displayField = $definition['relationship']['display_field'] ?? 'name';
                
                if (class_exists($modelClass)) {
                    $options = $modelClass::pluck($displayField, 'id')->toArray();
                    $this->fieldDefinitions[$fieldName]['options'] = $options;
                }
            }
        }
        
        $this->dispatch('fieldsRefreshed');
    }










    // In your DataTableForm Livewire component

/**
 * Check if a field should be displayed based on edit mode and hidden fields
 */
public function shouldDisplayField($field, $hiddenFields, $isEditMode)
{
    if ($isEditMode) {
        return !in_array($field, $hiddenFields['onEditForm'] ?? []);
    } else {
        return !in_array($field, $hiddenFields['onNewForm'] ?? []);
    }
}

/**
 * Check if a group is empty (all fields are hidden)
 */
public function isGroupEmpty($groupType, $fields, $hiddenFields, $isEditMode)
{
    if (empty($fields)) {
        return true;
    }
    
    if ($isEditMode) {
        $hidden = $hiddenFields['onEditForm'] ?? [];
    } else {
        $hidden = $hiddenFields['onNewForm'] ?? [];
    }
    
    return empty(array_diff($fields, $hidden));
}

/**
 * Get the field type from field definition
 */
public function getFieldType($fieldDefinition)
{
    if (is_array($fieldDefinition)) {
        return $fieldDefinition['field_type'] ?? 'text';
    }
    
    return $fieldDefinition;
}

/**
 * Get field options from field definition
 */
public function getFieldOptions($fieldDefinition)
{
    if (!is_array($fieldDefinition)) {
        return [];
    }
    
    if (isset($fieldDefinition['options'])) {
        if (isset($fieldDefinition['options']['model'], $fieldDefinition['options']['column'])) {
            ///return DataTableOption::getOptionList($fieldDefinition['options']);
            return DataTableOption::getOptionList($fieldDefinition['options']);

        } else {
            return $fieldDefinition['options'];
        }
    }
    
    return [];
}












    
    public function render()
    {
         
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath =  "qf::components.livewire.$UIFramework";
        return view("$viewPath.data-tables.data-table-form")
            ->layout("$viewPath.layouts.app"); // 👈 important
    }
}