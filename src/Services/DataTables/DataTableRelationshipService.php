<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class DataTableRelationshipService
{
    public function handleRelationships($record, $fieldDefinitions, $multiSelectFields, $singleSelectFields, $fields)
    {
        foreach ($fieldDefinitions as $fieldName => $definition) {
            if (isset($definition['relationship'])) {
                $this->processRelationship(
                    $record, 
                    $definition['relationship'], 
                    $multiSelectFields[$fieldName] ?? null,
                    $singleSelectFields[$fieldName] ?? null,
                    $fields[$fieldName] ?? null,
                    $fieldName
                );
            }
        }
    }
    
    protected function processRelationship($record, $relationship, $multiSelectValue, $singleSelectValue, $fieldValue, $fieldName)
    {
        $type = $relationship['type'] ?? null;
        $dynamicProperty = $relationship['dynamic_property'] ?? null;
        
        if (!$type || !$dynamicProperty) {
            return;
        }
        
        try {
            switch ($type) {
                case 'hasMany':
                    $this->handleHasMany($record, $relationship, $multiSelectValue, $dynamicProperty);
                    break;
                    
                case 'belongsTo':
                    $this->handleBelongsTo($record, $relationship, $fieldValue, $fieldName);
                    break;
                    
                case 'belongsToMany':
                    $this->handleBelongsToMany($record, $relationship, $multiSelectValue, $dynamicProperty);
                    break;
                    
                case 'morphTo':
                    $this->handleMorphTo($record, $relationship, $fieldValue, $fieldName);
                    break;
                    
                case 'morphMany':
                    $this->handleMorphMany($record, $relationship, $multiSelectValue, $dynamicProperty);
                    break;
                    
                case 'morphToMany':
                    $this->handleMorphToMany($record, $relationship, $multiSelectValue, $dynamicProperty);
                    break;
                    
                default:
                    Log::warning("Unsupported relationship type: {$type}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to process relationship {$type} for field {$fieldName}: " . $e->getMessage());
            throw $e;
        }
    }
    
    protected function handleHasMany($record, $relationship, $values, $dynamicProperty)
    {
        if (!$values || !is_array($values)) {
            return;
        }
        
        $foreignKey = $relationship['foreign_key'] ?? null;
        $modelClass = $relationship['model'] ?? null;
        
        if ($foreignKey && $modelClass) {
            // Clear previous relationships
            $record->{$dynamicProperty}()->update([$foreignKey => null]);
            
            // Set new relationships
            $modelClass::whereIn('id', $values)->update([$foreignKey => $record->id]);
        }
    }
    
    protected function handleBelongsTo($record, $relationship, $value, $fieldName)
    {
        $foreignKey = $relationship['foreign_key'] ?? $fieldName;
        
        if (!is_null($value)) {
            $record->{$foreignKey} = $value;
            $record->save();
        }
    }
    
    protected function handleBelongsToMany($record, $relationship, $values, $dynamicProperty)
    {
        if (!$values || !is_array($values)) {
            return;
        }
        
        $record->{$dynamicProperty}()->sync($values);
    }
    
    protected function handleMorphTo($record, $relationship, $value, $fieldName)
    {
        $morphType = $relationship['morph_type'] ?? $fieldName . '_type';
        $morphId = $relationship['morph_id'] ?? $fieldName . '_id';
        
        if (!is_null($value)) {
            // Assuming value is an array with 'type' and 'id'
            if (is_array($value) && isset($value['type']) && isset($value['id'])) {
                $record->{$morphType} = $value['type'];
                $record->{$morphId} = $value['id'];
                $record->save();
            }
        }
    }
    
    protected function handleMorphMany($record, $relationship, $values, $dynamicProperty)
    {
        if (!$values || !is_array($values)) {
            return;
        }
        
        $morphType = $relationship['morph_type'] ?? 'model_type';
        $morphId = $relationship['morph_id'] ?? 'model_id';
        
        // Clear previous relationships
        $record->{$dynamicProperty}()->update([
            $morphType => null,
            $morphId => null
        ]);
        
        // Set new relationships
        $modelClass = $relationship['model'] ?? null;
        if ($modelClass) {
            $modelClass::whereIn('id', $values)->update([
                $morphType => get_class($record),
                $morphId => $record->id
            ]);
        }
    }
    
    protected function handleMorphToMany($record, $relationship, $values, $dynamicProperty)
    {
        if (!$values || !is_array($values)) {
            return;
        }
        
        $record->{$dynamicProperty}()->sync($values);
    }
}