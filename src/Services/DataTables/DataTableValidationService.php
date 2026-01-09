<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Validation\Rule;

class DataTableValidationService
{
    public function getDynamicValidationRules($fields, $fieldDefinitions, $isEditMode = false, $model = null,  $recordId = null, $hiddenFields = [])
    {
        $rules = [];
        
        foreach ($fieldDefinitions as $field => $definition) {
            if ($this->shouldValidateField($fields, $fieldDefinitions, $field, $isEditMode, $model, $recordId, $hiddenFields)) {
                if (isset($definition['validation'])) {
                    $ruleKey = "{$field}"; // "fields.{$field}"
                    $rules[$ruleKey] = $this->adjustUniqueRule(
                        $definition['validation'], 
                        $isEditMode, 
                        $recordId
                    );

                    // Always validate file fields if they exist in request
                } elseif (isset($definition['field_type']) && $definition['field_type'] === 'file') {
                    $ruleKey = "{$field}"; // "fields.{$field}"
                    $rules[$ruleKey] = $this->getDefaultFileValidationRules($definition);
                } else {
                    $ruleKey = "{$field}"; // "fields.{$field}"
                    $rules[$ruleKey] = $this->adjustUniqueRule(
                        'sometimes', 
                        $isEditMode, 
                        $recordId   
                    );

                }
            }
        }

        return $rules;
    }


    protected function getDefaultFileValidationRules($definition)
    {
        $fileTypes = $definition['fileTypes'] ?? ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];//, 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'gif', 'svg'];
        $maxSizeMB = $definition['maxSizeMB'] ?? 1; // Default to 1MB
        $maxSizeKB = $maxSizeMB * 1024; // Convert MB to KB for Laravel validation
        return "file|mimes:" . implode(',', $fileTypes) . "|max:{$maxSizeKB}";
    }



    protected function shouldValidateField($fields, $fieldDefinitions, $field, $isEditMode, $modelClass = null,  $recordId = null, $hiddenFields = [])
    {

        // Always validate file fields if they exist in request
        if (isset($fieldDefinitions[$field]['type']) && $fieldDefinitions[$field]['type'] === 'file') {
            return true;
        }

       // If password fiied is changed on edit form validate
        if ($field === 'password' || $field === 'password_confirmation') {
            // $modelClass eg. App\Modules\Admin\Models\User
            return $isEditMode && isset($fields['password']);
                
        }


        
        $formType = $isEditMode ? 'onEditForm' : 'onNewForm';
        return !in_array($field, $hiddenFields[$formType] ?? []);
    }
    
    protected function adjustUniqueRule($validation, $isEditMode, $recordId)
    {
        if ($isEditMode && $recordId && str_contains($validation, 'unique')) {
            return preg_replace(
                '/unique:([^,]+),([^,]+)/',
                "unique:$1,$2,{$recordId}",
                $validation
            );
        }
        
        return $validation;
    }
}