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
use App\Modules\System\Events\DataTableFormEvent;
use Illuminate\Support\Facades\Validator;


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
    public $multiSelectFormFields = []; // To be removed later
    public $singleSelectFormFields = []; // To be removed later
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
        $this->initializeFieldsWithDefaults();

    
    }


    // Initialize fields with default values 
    function initializeFieldsWithDefaults()
    {
        foreach ($this->fieldDefinitions as $field => $definition) {
            // Make the multiselect field array by default
            if (isset($this->fieldDefinitions[$field]['multiSelect'])) {
                $this->fields[$field] = [$definition['default']?? null];
            } else if (is_array($definition) && isset($definition['field_type']) ){
                // For integer, string, boolradio, boolcheckbox, date, datetime, time, etc.
                if ($definition['field_type'] == 'boolradio' || $definition['field_type'] == 'boolcheckbox') {
                    $this->fields[$field] = $definition['default'] ?? false;
                } 
                // Interger field default to 0
                else if ($definition['field_type'] == 'integer' || $definition['field_type'] == 'number') {
                    $this->fields[$field] = $definition['default'] ?? 0;
                }
                else {
                    $this->fields[$field] = $definition['default'] ?? null;
                }

            }

            // Add options for boolcheckbox & boolradio 
            if (isset($definition['field_type'])) {
                if (!isset($this->fieldDefinitions[$field]["options"]) && $definition['field_type'] == 'boolradio') {
                    $this->fieldDefinitions[$field]["options"] = [1 => 'Yes', 0 => 'No'];
                } else if (!isset($this->fieldDefinitions[$field]["options"]) && $definition['field_type'] == 'boolcheckbox') {
                    $this->fieldDefinitions[$field]["options"] = [1 => 'Yes'];
                } 
            }
        }
    }




    public function saveRecord($modalId)
    {


        try {

            // $this->authorizeAction();

            // Validate inputs
            $rules = $this->validationService->getDynamicValidationRules(
                $this->fields,
                $this->fieldDefinitions,
                $this->isEditMode,
                $this->model,
                $this->selectedItemId,
                $this->hiddenFields
            );


            $validator = Validator::make($this->fields, $rules, $this->messages);

            if ($validator->fails()) {
                
                // Clear previous errors
                $this->resetErrorBag();
                // Map errors back without the fields prefix
                $errors = new \Illuminate\Support\MessageBag();
                foreach ($validator->errors()->messages() as $key => $messages) {
                    //$cleanKey = str_replace('fields.', '', $key);
                    foreach ($messages as $message) {
                        $errors->add($key, $message);
                    }
                }

                $this->setErrorBag($errors);
                return;

            }


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
                    $this->dispatchAllEvents('updated', $oldRecord, $record->toArray());
                } else {
                    $record = $this->model::create($data);
                    $this->selectedItemId = $record->id;
                    $this->dispatchAllEvents('created', [], $record->toArray());
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

        // Remove hidden in query
        $allowedFields = array_diff(
            $allowedFields,
            $this->hiddenFields['onQuery'] ?? []
        );

        $data = array_filter(
            $this->fields,
            fn($key) => in_array($key, $allowedFields),
            ARRAY_FILTER_USE_KEY
        );


        // Loop and convert the multiSelect fields to comma separated string
        foreach ($this->fieldDefinitions as $field => $definition) {
            if (isset($definition['multiSelect']) && isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = implode(',', $data[$field]);
            }
        }


        // Hash passwords
        $data = $this->formService->hashPasswordFields(
            $data,
            $this->isEditMode,
            $this->isEditMode ? $record : null
        );

        // Add audit trail fields
        $action = $this->isEditMode ? 'updated' : 'created';
        $data = $this->formService->addAuditTrailFields($data, $action, $this->config);

        // $data = $this->addSelectedSingleFieldsToData($data);

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


    /*protected function dispatchEvent($eventName, $oldData, $newData)
    {
        $eventClass = "App\\Events\\DataTable\\{$this->modelName}{$eventName}";

        if (class_exists($eventClass)) {
            event(new $eventClass($oldData, $newData, auth()->id()));
        }
    }*/



    private function dispatchAllEvents($eventName, $oldRecord, $newData)
    {

        if (!isset($this->config["dispatchEvents"]) || !$this->config["dispatchEvents"])
            return;

        // AVAILABLE FOR IMPLEMENTATION EVENTS:
        // DataTableFormEvent, DataTableFormBeforeCreateEvent,  DataTableFormAfterCreateEvent,
        // DataTableFormBeforeUpdateEvent,  DataTableFormAfterUpdateEvent,
        // {AnyModelName}Event, {AnyModelName}BeforeCreateEvent,  {AnyModelName}AfterCreateEvent,
        // {AnyModelName}BeforeUpdateEvent,  {AnyModelName}AfterUpdateEvent,

        // Sending DtatTableForm Generic event
        DataTableFormEvent::dispatch($oldRecord, $newData, $eventName, $this->model);

        // Sending DtatTableForm Specific event eg. DataTableForm{BeforeUpdate}Event
        $dataTableFormEvent = "DataTableForm{$eventName}Event";
        if (class_exists($dataTableFormEvent))
            $dataTableFormEvent::dispatch($oldRecord, $newData, $eventName, $this->model);


        // Specific Model releted eg. {User}BeforeUpdateEvent
        $specificEvent = $this->getSpecificEventFullName($eventName);
        $event = $this->getEventFullName();
        if (class_exists($specificEvent))
            $specificEvent::dispatch($oldRecord, $newData);

        // Generic Model releted eg. {User}Event
        if (class_exists($event))
            $event::dispatch($oldRecord, $newData, $eventName);
    }


    private function getSpecificEventFullName($eventName)
    {
        return "\\App\\Modules\\{$this->moduleName}\\Events\\{$eventName}" . $this->modelName . "Event";
    }

    private function getEventFullName()
    {
        return "\\App\\Modules\\{$this->moduleName}\\Events\\" . $this->modelName . "Event";
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
        if ($this->model !== $model)
            return;

        ///$this->authorize('update', $model::findOrFail($id));

        $this->selectedItemId = $id;

        $record = $model::findOrFail($id);

        // Populate fields
        foreach ($this->fieldDefinitions as $field => $definition) {
            // Multiselect fields should be converted to array from comma separated string
            if (isset($this->fieldDefinitions[$field]['multiSelect'])) {
                $this->fields[$field] = $record->$field ? explode(',', $record->$field) : [];
                
            } else if (!str_contains($field, 'password')) {
                $this->fields[$field] = $record->$field;
            } /*else if (str_contains($field, 'password')) {
                $this->fields[$field] = $record->$field;
                $this->fields["password_confirmation"] = $record->$field;
            }*/
        }

        // Populate relationship fields
        $this->populateRelationshipFields($record);
        // Populate single select fields
        // $this->populateSingleSelectFields($record);

        $this->isEditMode = true;
        $this->dispatch('changeFormModeEvent', ['mode' => 'edit']);
        $this->dispatch('open-modal-event', ['isEditMode' => $this->isEditMode, 'editModalTitle' => '', 'modalId' => $modalId]); // browser event
    }


    public function populateSingleSelectFields($record)
    {
        // iterate through the fieldDefinitions
        // if field has options and no realtionship defined, then it's a single select
        foreach ($this->fieldDefinitions as $field => $definition) {
            if (isset($definition['options']) && !isset($definition['relationship'])) {
                $this->singleSelectFormFields[$field] = $record->$field;

            } else {
                // For boolradio and boolcheckbox fields
                if (in_array($definition['field_type'], ['boolradio', 'boolcheckbox'])) {
                    $this->singleSelectFormFields[$field] = $record->$field;
                }
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

        // Re-initialize fields with default values
        $this->initializeFieldsWithDefaults();


        


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

                $this->dispatchAllEvents('deleted', $record->toArray(), []);
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

        DB::transaction(function () use ($modelIds, $fieldName, $fieldValue, $actionName) {
            $this->model::whereIn('id', $modelIds)->update([$fieldName => $fieldValue]);

            foreach ($modelIds as $id) {
                $record = $this->model::findOrFail($id);
                $this->dispatchAllEvents('updated', $record->toArray(), array_merge($record->toArray(), [$fieldName => $fieldValue, "actionName" => $actionName]));
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












    // Also add a method to check the current state
    public function checkFileState()
    {
        foreach ($this->fields as $key => $value) {
            if (is_object($value)) {
                \Log::debug("Field {$key} state", [
                    'class' => get_class($value),
                    'methods' => get_class_methods($value),
                    'path' => method_exists($value, 'getPath') ? $value->getPath() : 'N/A',
                    'temp_url_method' => method_exists($value, 'temporaryUrl'),
                ]);
            }
        }
    }













    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath = "qf::components.livewire.$UIFramework";
        return view("$viewPath.data-tables.data-table-form")
        ;//->layout("$viewPath.layouts.app"); // 👈 important
    }
}