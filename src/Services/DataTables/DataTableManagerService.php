<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;
use QuickerFaster\LaravelUI\Traits\DataTable\DataTableControlsTrait;

class DataTableManagerService
{
    use DataTableControlsTrait;

    public function loadConfiguration($model, $moduleName = null, $modelName = null, $hiddenFields = [], $readOnlyFields = [])
    {
        if (!$modelName) {
            $modelName = class_basename($model);
        }
        
        if (!$moduleName) {
            $moduleName = DataTableConfig::extractModuleNameFromModel($model);
        }

        $config = DataTableConfig::getConfigFileFields($model);

        return [
            'modelName' => $modelName,
            'moduleName' => $moduleName,
            'config' => $config,
            'fieldDefinitions' => $config['fieldDefinitions'] ?? [],
            'simpleActions' => $config['simpleActions'] ?? [],
            'moreActions' => $config['moreActions'] ?? [],
            'fieldGroups' => $config['fieldGroups'] ?? [],
            'hiddenFields' => $this->mergeHiddenFields($config['hiddenFields'] ?? [], $hiddenFields),
            'controls' => $this->getControls($config),
            'columns' => $config['columns'] ?? [],
            'multiSelectFormFields' => $config['multiSelectFormFields'] ?? [],
            'singleSelectFormFields' => $config['singleSelectFormFields'] ?? [],
            'visibleColumns' => $this->getVisibleColumns($config['columns'] ?? [], $config['hiddenFields']['onTable'] ?? []),
            'readOnlyFields' => $readOnlyFields,
        ];
    }

    protected function mergeHiddenFields($configHiddenFields, $componentHiddenFields)
    {
        foreach ($configHiddenFields as $key => $hiddenField) {
            if (isset($componentHiddenFields[$key])) {
                $componentHiddenFields[$key] = array_unique(array_merge($componentHiddenFields[$key], $hiddenField));
            } else {
                $componentHiddenFields[$key] = $hiddenField;
            }
        }
        
        return $componentHiddenFields;
    }

    protected function getControls($config)
    {

        if (empty($config['controls']))
            return [];

        if (is_array($config['controls']) && !empty($config['controls'])) {
            return $config['controls'];
        }
         

        if (isset($config['controls']) && strtolower($config['controls']) === "all") {
            return $this->getDataTableAllControls();
        }
        
        return $this->getPreparedControls($config['controls'] ?? []);
    }

    protected function getVisibleColumns($columns, $hiddenOnTable)
    {
        return array_diff($columns, $hiddenOnTable);
    }

    public function getInlinableModels($fieldDefinitions)
    {
        $inlinableModels = [];
        
        foreach ($fieldDefinitions as $fieldData) {
            if (isset($fieldData['relationship']['inlineAdd']) && $fieldData['relationship']['inlineAdd']) {
                $inlinableModels[] = $fieldData['relationship']['model'];
            }
        }
        
        return array_unique($inlinableModels);
    }
}