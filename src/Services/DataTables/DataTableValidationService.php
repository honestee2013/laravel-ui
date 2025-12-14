<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Validation\Rule;

class DataTableValidationService
{
    public function getDynamicValidationRules($fieldDefinitions, $isEditMode = false, $recordId = null, $hiddenFields = [])
    {
        $rules = [];
        
        foreach ($fieldDefinitions as $field => $definition) {
            if ($this->shouldValidateField($field, $isEditMode, $hiddenFields)) {
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


    
    protected function shouldValidateField($field, $isEditMode, $hiddenFields)
    {
        // Always validate file fields if they exist in request
        if (isset($this->fieldDefinitions[$field]['type']) && $this->fieldDefinitions[$field]['type'] === 'file') {
            return true;
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