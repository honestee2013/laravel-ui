<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Wizards;

use Livewire\Component;
use Livewire\WithFileUploads;
use QuickerFaster\LaravelUI\Services\Wizards\WizardConfigService;
use QuickerFaster\LaravelUI\Services\Wizards\WizardFormService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableValidationService;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableRelationshipService;
use QuickerFaster\LaravelUI\Services\ValidationMessageGenerator;
use QuickerFaster\LaravelUI\Traits\GUI\HasAutoGenerateFieldTrait;
use Illuminate\Support\Facades\DB;


class WizardManager extends Component
{
    use WithFileUploads, HasAutoGenerateFieldTrait;

    public $wizardId;
    public $module;
    public $wizardConfig;
    public $currentStep = 0;
    public $formData = [];
    public $isCompleted = false;
    public $uploads = [];

    protected $wizardConfigService;
    protected $wizardFormService;
    protected $validationService;
    protected $relationshipService;
    protected $validationMessageGenerator;

    public $aliasFromModel = [];
    public $modelFromAlias = [];
    public $isConfirmPage = false;
    public $linkSourceRecordId;

public $linkUserFieldValue = null;      // employee_number (user-facing)
public $linkDatabaseFieldValue = null;  // employee_id (database foreign key)
public $linkUserFieldName = null;       // 'employee_number'
public $linkDatabaseFieldName = null;   // 'employee_id'

    





    public function boot(
        WizardConfigService $wizardConfigService,
        WizardFormService $wizardFormService,
        DataTableValidationService $validationService,
        DataTableRelationshipService $relationshipService,
        ValidationMessageGenerator $validationMessageGenerator
    ) {
        $this->wizardConfigService = $wizardConfigService;
        $this->wizardFormService = $wizardFormService;
        $this->validationService = $validationService;
        $this->relationshipService = $relationshipService;
        $this->validationMessageGenerator = $validationMessageGenerator;
    }

    public function mount($wizardId, $module = 'hr')
    {
        $this->wizardId = $wizardId;
        $this->module = $module;
        $this->wizardConfig = $this->wizardConfigService->loadWizardConfig($wizardId, $module);
        $this->initializeFormData();
    }






protected function initializeFormData()
{
    // Determine linkage strategy
    if (isset($this->wizardConfig['linkField'])) {
        // Simple linkage
        $this->linkUserFieldName = $this->wizardConfig['linkField'];
        $this->linkDatabaseFieldName = $this->wizardConfig['linkField'];
    } elseif (isset($this->wizardConfig['linkFields'])) {
        // Complex linkage
        $this->linkUserFieldName = $this->wizardConfig['linkFields']['userField'];
        $this->linkDatabaseFieldName = $this->wizardConfig['linkFields']['databaseField'];
    }

     
    foreach ($this->wizardConfig['steps'] as $stepIndex => $step) {
        
        if (!empty($step['model'])) {
            $alias = $this->getModelAlias($step['model']);
            $this->formData[$alias] = [];
            $this->modelFromAlias[$alias] = $step['model'];
            $this->aliasFromModel[$step['model']] = $alias;
            

            // Initialize user-facing link field on source step
            if (!empty($step['isLinkSource']) && $this->linkUserFieldName) {
                $this->formData[$alias][$this->linkUserFieldName] = '';
            }
            
            // Initialize database link field on dependent steps
            if (!empty($step['requiresLink']) && $this->linkDatabaseFieldName) {
                $this->formData[$alias][$this->linkDatabaseFieldName] = null;
                
            }
        }

        
    }
    
}


protected function syncLinkFieldValue()
{
    if (!$this->linkUserFieldName) return;
    
    // Step 1: Get user-facing value from source step
    $userValue = null;
    foreach ($this->wizardConfig['steps'] as $stepIndex => $step) {
        if (!empty($step['isLinkSource']) && !empty($step['model'])) {
            $alias = $this->aliasFromModel[$step['model']] ?? null;
            if ($alias && isset($this->formData[$alias][$this->linkUserFieldName])) {
                $userValue = $this->formData[$alias][$this->linkUserFieldName];
                break;
            }
        }
    }
    
    if ($userValue === null) return;
    
    $this->linkUserFieldValue = $userValue;
    
    // Step 2: Look up database ID if using complex linkage
    if ($this->linkUserFieldName !== $this->linkDatabaseFieldName) {
        // This assumes you have a service to resolve user field → database ID
        $this->linkDatabaseFieldValue = $this->resolveUserFieldValueToDatabaseId(
            $userValue, 
            $this->linkUserFieldName, 
            $this->linkDatabaseFieldName
        );
    } else {
        // Simple linkage - same value
        $this->linkDatabaseFieldValue = $userValue;
    }
    
    // Step 3: Propagate values to dependent steps
    foreach ($this->wizardConfig['steps'] as $stepIndex => $step) {
        if (empty($step['requiresLink']) || empty($step['model'])) continue;
        
        $alias = $this->aliasFromModel[$step['model']] ?? null;
        if (!$alias) continue;
        
        // Set user-facing field (for display)
        if ($this->linkUserFieldName) {
            $this->formData[$alias][$this->linkUserFieldName] = $userValue;
        }
        
        // Set database field (for saving)
        if ($this->linkDatabaseFieldName) {
            $this->formData[$alias][$this->linkDatabaseFieldName] = $this->linkDatabaseFieldValue;
        }
    }
}

protected function resolveUserFieldValueToDatabaseId($userValue, $userField, $databaseField)
{
    // For your specific case: employee_number → employee_id
    if ($userField === 'employee_number' && $databaseField === 'employee_id') {
        $employee = \App\Modules\Hr\Models\Employee::where('employee_number', $userValue)->first();
        return $employee ? $employee->id : null;
    }
    
    // Add other mappings as needed
    // You could also make this more generic using model config
    
    return $userValue; // fallback for simple cases
}



// Add this to handle link field updates
public function updatedFormData($value, $key)
{
    // $key will be something like "hr_employee.employee_number"
    /*if ($this->linkFieldName && strpos($key, $this->linkFieldName) !== false) {
        $this->syncLinkFieldValue();
    }*/
}









    protected function getModelAlias(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $module = $parts[2] ?? 'system';
        $model = \Str::snake(end($parts));
        return strtolower($module) . '_' . $model;
    }



public function nextStep()
{
    $this->validateCurrentStep();

    if ($this->currentStep < count($this->wizardConfig['steps']) - 1) {
        $this->currentStep++;
        // Check if we're now on the last step (confirmation page)
        $this->isConfirmPage = ($this->currentStep === count($this->wizardConfig['steps']) - 1);
    } else {
        $this->completeWizard();
    }
}

public function prevStep()
{
    if ($this->currentStep > 0) {
        $this->goToStep($this->currentStep - 1);
    }
}


public function goToStep($stepIndex)
{
    $this->currentStep = $stepIndex;
    $this->isConfirmPage = ($stepIndex === count($this->wizardConfig['steps']) - 1);
}



public function getConfirmationData()
{
    $confirmationData = [];
    
    foreach ($this->wizardConfig['steps'] as $stepIndex => $step) {
        if (empty($step['model'])) continue;
        
        $modelAlias = $this->aliasFromModel[$step['model']] ?? null;
        if (!$modelAlias || !isset($this->formData[$modelAlias])) continue;
        
        $modelConfig = $this->wizardConfigService->loadModelConfig($step['model']);
        $fieldDefinitions = $modelConfig['fieldDefinitions'] ?? [];
        $stepTitle = $step['title'] ?? 'Step ' . ($stepIndex + 1);
        
        foreach ($this->formData[$modelAlias] as $field => $value) {
            if (!isset($fieldDefinitions[$field])) continue;
            
            // Skip link fields on dependent steps that shouldn't be shown
            if (!empty($step['requiresLink'])) {
                if (($this->linkDatabaseFieldName && $field === $this->linkDatabaseFieldName) ||
                    ($this->linkUserFieldName && $field === $this->linkUserFieldName && (empty($value) || $value === 'Not provided'))) {
                    continue;
                }
            }
            
            $fieldDef = $fieldDefinitions[$field];
            $label = $fieldDef['label'] ?? ucwords(str_replace('_', ' ', $field));
            $formattedValue = $this->formatFieldValue($value, $fieldDef);
            
            // Skip "Not provided" for link fields
            if ($formattedValue === 'Not provided' && 
                !empty($step['requiresLink']) && 
                ($field === $this->linkUserFieldName || $field === $this->linkDatabaseFieldName)) {
                continue;
            }
            
            $confirmationData[$stepTitle][$label] = $formattedValue;
        }
    }
    
    return $confirmationData;
}


protected function formatFieldValue($value, $fieldDefinition)
{
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    $type = $fieldDefinition['field_type'] ?? 'text';

    // Handle array values (checkboxes, multi-select)
    if (is_array($value)) {
        return implode(', ', $value);
    }

    // Handle boolean values
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }

    // Handle date fields
    if (in_array($type, ['date', 'datetime', 'datetime-local'])) {
        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y g:i A');
        } catch (\Exception $e) {
            return $value; // Return as-is if parsing fails
        }
    }

    // Handle relationship fields
    if (isset($fieldDefinition['relationship'])) {
        $relationship = $fieldDefinition['relationship'];

        // If value is already an object/array with a label (e.g., from Select2), use it
        if (is_array($value) && isset($value['label'])) {
            return $value['label'];
        }
        if (is_object($value) && isset($value->label)) {
            return $value->label;
        }

        // If value is a scalar (ID), try to load the related model
        if (is_scalar($value) && !empty($relationship['model']) && !empty($relationship['display_field'])) {
            try {
                $modelClass = $relationship['model'];
                $displayField = $relationship['display_field'];

                // Ensure the model exists and is valid
                if (class_exists($modelClass)) {
                    $relatedModel = $modelClass::find($value);
                    if ($relatedModel && $relatedModel->{$displayField}) {
                        return $relatedModel->{$displayField};
                    }
                }
            } catch (\Exception $e) {
                // Optionally log error, but fall back gracefully
            }
        }

        // Fallback: return original value if resolution fails
        return $value;
    }

    // Handle file uploads
    if ($type === 'file') {
        if (is_object($value) && method_exists($value, 'getClientOriginalName')) {
            return $value->getClientOriginalName();
        }
        if (is_string($value) && !empty($value)) {
            return basename($value);
        }
    }

    return $value;
}







protected function validateCurrentStep()
{


    $currentStep = $this->wizardConfig['steps'][$this->currentStep];
    // Skip validation if no model (like confirmation step, but confirmation shouldn't reach here)
    if (empty($currentStep['model'])) {
        return;
    }

    $model = $currentStep['model'] ?? null;
    if (!$model || !isset($this->aliasFromModel[$model])) {
        return;
    }




    $modelAlias = $this->aliasFromModel[$model];
    $modelConfig = $this->wizardConfigService->loadModelConfig($model);

    $stepFieldDefinitions = $this->filterFieldDefinitionsForStep(
        $modelConfig['fieldDefinitions'] ?? [],
        $currentStep,
        $modelConfig
    );

    if (empty($stepFieldDefinitions)) return;


// Remove link fields from validation on dependent steps
if (!empty($currentStep['requiresLink'])) {
    if ($this->linkUserFieldName && isset($stepFieldDefinitions[$this->linkUserFieldName])) {
        unset($stepFieldDefinitions[$this->linkUserFieldName]);
    }
    if ($this->linkDatabaseFieldName && isset($stepFieldDefinitions[$this->linkDatabaseFieldName])) {
        unset($stepFieldDefinitions[$this->linkDatabaseFieldName]);
    }
}

    $rules = $this->validationService->getDynamicValidationRules(
        $stepFieldDefinitions, false, null, []
    );

    

    //\Log::debug('Raw rules:', $rules);

    $prefixedRules = [];
    foreach ($rules as $field => $rule) {
        // Safely remove 'fields.' prefix if present
        $cleanField = str_starts_with($field, 'fields.') ? substr($field, 7) : $field;
        $prefixedRules["formData.{$modelAlias}.{$cleanField}"] = $rule;
    }
    //\Log::debug('Final prefixed rules:', $prefixedRules);

    

// In validateCurrentStep()
$messages = $this->validationMessageGenerator->generateMessages(
    $prefixedRules,
    $stepFieldDefinitions, // ← pass the filtered field definitions
    $modelAlias
);

$this->validate($prefixedRules, $messages);



    
}

    protected function isFieldInCurrentStepGroups($field, $step, $modelConfig)
    {
        if (empty($step['groups'])) {
            return false;
        }

        foreach ($step['groups'] as $groupId) {
            if (isset($modelConfig['fieldGroups'][$groupId]['fields'])) {
                if (in_array($field, $modelConfig['fieldGroups'][$groupId]['fields'])) {
                    return true;
                }
            }
        }

        return false;
    }

protected function filterFieldDefinitionsForStep($fieldDefinitions, $step, $modelConfig)
{
    $filtered = [];
    $currentWizardId = $this->wizardConfig['id'] ?? null;

    foreach ($fieldDefinitions as $field => $definition) {
        // Inline fields: always include (no wizard filtering when [fields:] is specified under step definition) 
        if (!empty($step['fields']) && in_array($field, $step['fields'])) {
            $filtered[$field] = $definition;
            continue;
        }

        // Group-based fields: include only if in group AND marked for this wizard
        if ($this->isFieldInCurrentStepGroups($field, $step, $modelConfig)) {
            if ($currentWizardId && ($definition['wizard'][$currentWizardId] ?? false) === true) {
                $filtered[$field] = $definition;
            }

            // Note: if no wizard ID (edge case), include all group fields
        }
    }

    return $filtered;
}







protected function completeWizard()
{
    DB::transaction(function () {
        try {
            $this->formData = $this->handleAllFileUploads();
            
            $savedRecords = [];
            $this->linkSourceRecordId = null; // ← Track the ID from isLinkSource step

            foreach ($this->wizardConfig['steps'] as $step) {
                if (empty($step['model'])) continue;
                
                $modelClass = $step['model'];
                $alias = $this->aliasFromModel[$modelClass] ?? null;
                if (!$alias || !isset($this->formData[$alias])) continue;
                
                $data = $this->formData[$alias];

                // 👇 Handle link source: capture its ID after saving
                if (!empty($step['isLinkSource'])) {
                    // We'll save this record below and capture its ID
                }

                // 👇 Handle link dependency: inject link ID BEFORE saving
                if (!empty($step['requiresLink']) && $this->linkDatabaseFieldName) {
                    if ($this->linkSourceRecordId !== null) {
                        $data[$this->linkDatabaseFieldName] = $this->linkSourceRecordId;
                    } else {
                        // This shouldn't happen if steps are ordered correctly
                        throw new \LogicException("Link source record not saved before dependent step.");
                    }
                }

                // 👇 Resolve intra-wizard relationships (e.g., position → employee)
                $data = $this->resolveStepRelationships($data, $step, $savedRecords);

                // 👇 Save the record
                $record = new $modelClass();
                $record->fill($data);
                $record->save();

                $savedRecords[$alias] = $record;

                // 👇 Capture link source ID immediately after saving
                if (!empty($step['isLinkSource'])) {
                    $this->linkSourceRecordId = $record->id;
                }
            }

            $this->isCompleted = true;
            $this->dispatch('wizardCompleted', $this->wizardConfig['id']);
            
        } catch (\Exception $e) {
            \Log::error('Wizard completion error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);
            $this->addError('completion', 'Failed to complete wizard: ' . $e->getMessage());
            throw $e; // Re-throw to rollback transaction
        }
    });
}



// In WizardManager.php

public function getCompletionConfig()
{
    $defaultConfig = [
        'title' => 'Completed!',
        'message' => 'Your wizard has been completed successfully.',
        'actions' => [
            [
                'label' => 'Go to Dashboard',
                'url' => '/dashboard',
                'primary' => true,
            ]
        ]
    ];

    $completion = $this->wizardConfig['completion'] ?? [];
    
    // Resolve dynamic values (e.g., {id})
    // $linkSourceId = $this->getLinkSourceRecordId(); // ← You'll need to store this after save

    $actions = $completion['actions'] ?? [];
    foreach ($actions as &$action) {
        if (isset($action['url'])) {
            $action['url'] = str_replace('{id}', $this->linkSourceRecordId, $action['url']);
        }
    }

    return array_merge($defaultConfig, $completion, ['actions' => $actions]);
}



protected function resolveStepRelationships(array $data, array $step, array $savedRecords): array
{
    // Only process if step defines explicit fields (for relationship resolution)
    if (empty($step['fields'])) {
        return $data;
    }

    foreach ($step['fields'] as $fieldName => $fieldDefinition) {
        if (!isset($data[$fieldName])) continue;

        if (isset($fieldDefinition['relationship']) && $fieldDefinition['relationship']['type'] === 'belongsTo') {
            $rel = $fieldDefinition['relationship'];
            $relatedModelClass = $rel['model'] ?? null;
            $foreignKey = $rel['foreign_key'] ?? $fieldName;
            $relatedValue = $data[$fieldName] ?? null;

            // Check if related model is part of this wizard
            if ($relatedModelClass && isset($this->aliasFromModel[$relatedModelClass])) {
                $relatedAlias = $this->aliasFromModel[$relatedModelClass];
                
                if (isset($savedRecords[$relatedAlias])) {
                    // Use the actual saved ID from earlier step
                    $data[$foreignKey] = $savedRecords[$relatedAlias]->id;
                } else {
                    // Assume it's an existing ID (e.g., selected from dropdown)
                    $data[$foreignKey] = $relatedValue;
                }
            } else {
                // External model — keep the provided ID
                $data[$foreignKey] = $relatedValue;
            }

            // Clean up: remove original field if it's not the foreign key
            if ($fieldName !== $foreignKey) {
                unset($data[$fieldName]);
            }
        }
    }

    return $data;
}








    protected function handleAllFileUploads()
    {
        $processedData = $this->formData;

        foreach ($this->wizardConfig['steps'] as $step) {
            if (empty($step['model']) || empty($processedData[$step['model']])) {
                continue;
            }

            $modelClass = $step['model'];
            $record = new $modelClass();
            $processedData[$step['model']] = $this->wizardFormService->handleUploadedFiles(
                $record,
                $processedData[$step['model']]
            );
        }

        return $processedData;
    }

    protected function handleAllRelationships($savedRecords)
    {
        foreach ($this->wizardConfig['steps'] as $step) {
            if (empty($step['model']) || !isset($savedRecords[$step['model']])) {
                continue;
            }

            $record = $savedRecords[$step['model']];
            $modelConfig = $this->wizardConfigService->loadModelConfig($step['model']);
            $fieldDefinitions = $modelConfig['fieldDefinitions'] ?? [];

            $multiSelectFields = [];
            $singleSelectFields = [];

            // Extract relationship data (simplified — enhance as needed)
            foreach ($fieldDefinitions as $field => $definition) {
                if (isset($definition['relationship']) && isset($this->formData[$step['model']][$field])) {
                    // You can populate $multiSelectFields / $singleSelectFields here if needed
                }
            }

            $this->relationshipService->handleRelationships(
                $record,
                $fieldDefinitions,
                $multiSelectFields,
                $singleSelectFields,
                $this->formData[$step['model']] ?? []
            );
        }
    }


public function getFilteredFieldDefinitionsForStep($stepIndex)
{
    $step = $this->wizardConfig['steps'][$stepIndex] ?? null;
    if (empty($step['model'])) {
        return [];
    }

    $modelConfig = $this->wizardConfigService->loadModelConfig($step['model']);
    return $this->filterFieldDefinitionsForStep(
        $modelConfig['fieldDefinitions'] ?? [],
        $step,
        $modelConfig
    );
}



public function getFieldGroupsForStep($stepIndex)
{
    $step = $this->wizardConfig['steps'][$stepIndex];
    // Skip if no model
    if (empty($step['model'])) {
        return [];
    }
    
    $modelConfig = $this->wizardConfigService->loadModelConfig($step['model']);
    
    $stepGroups = [];
    if (!empty($step['groups'])) {
        foreach ($step['groups'] as $groupIndex) {
            if (isset($modelConfig['fieldGroups'][$groupIndex])) {
                $stepGroups[] = $modelConfig['fieldGroups'][$groupIndex];
            }
        }
    }
    
    return $stepGroups;
}



public function getFieldDefinitionsForStep($stepIndex)
{
    $step = $this->wizardConfig['steps'][$stepIndex];
    // Skip if no model
    if (empty($step['model'])) {
        return [];
    }
    
    $modelConfig = $this->wizardConfigService->loadModelConfig($step['model']);
    return $modelConfig['fieldDefinitions'] ?? [];
}

public function render()
{
    $currentStepConfig = $this->wizardConfig['steps'][$this->currentStep] ?? null;
    $model = $currentStepConfig['model'] ?? null;
    $modelAlias = $model ? ($this->aliasFromModel[$model] ?? null) : null;
    $fields = $modelAlias ? ($this->formData[$modelAlias] ?? []) : [];

    // ✅ Get ONLY the fields that should appear in this wizard step
    $filteredFieldDefinitions = $this->getFilteredFieldDefinitionsForStep($this->currentStep);

    // Build filtered field groups for UI
    $filteredFieldGroups = [];
    if (!empty($currentStepConfig['groups'])) {
        $modelConfig = $this->wizardConfigService->loadModelConfig($model);
        foreach ($currentStepConfig['groups'] as $groupId) {
            if (isset($modelConfig['fieldGroups'][$groupId])) {
                $group = $modelConfig['fieldGroups'][$groupId];
                // Filter group fields to only those in $filteredFieldDefinitions
                $group['fields'] = array_filter($group['fields'], function ($field) use ($filteredFieldDefinitions) {
                    return isset($filteredFieldDefinitions[$field]);
                });
                // Only include group if it has visible fields
                if (!empty($group['fields'])) {
                    $filteredFieldGroups[] = $group;
                }
            }
        }
    }

    $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
    $viewPath = "qf::components.livewire.$UIFramework";

    return view("$viewPath.wizards.wizard-manager", [
        'fields' => $fields,
        'currentModelAlias' => $modelAlias,
        'fieldGroups' => $filteredFieldGroups, // ✅ filtered
        'fieldDefinitions' => $filteredFieldDefinitions, // ✅ filtered
        'model' => $model,
        'modelName' => $model ? class_basename($model) : null,
        'multiSelectFormFields' => [],
        'singleSelectFormFields' => [],
        'readOnlyFields' => [],

        'linkUserFieldValue' => $this->linkUserFieldValue,
        'linkDatabaseFieldValue' => $this->linkDatabaseFieldValue,
        'linkUserFieldName' => $this->linkUserFieldName,
        'linkDatabaseFieldName' => $this->linkDatabaseFieldName,

        'currentStepConfig' => $currentStepConfig,
    ])->layout("$viewPath.layouts.app");
}




}





