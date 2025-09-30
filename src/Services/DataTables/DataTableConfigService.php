<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use QuickerFaster\LaravelUI\Repositories\DataTables\FieldRepository;;


class DataTableConfigService
{
    protected FieldRepository $fieldRepository;
    protected string $moduleName;
    protected string $configFileName;
    protected string $configPath;
    private $defaultHiddenFields = ["remember_token"];







    public function getConfigFileFieldsFromFile($configPath) {
        if (!$configPath)
            return [];

        // Return the file content
        return include $configPath ?? [];
    }

    public function getConfigFileFields($model) {
        $config = [];
        $defaultConfigFile = $this->getDefaultConfigFile($model);

        // If config file is not found, return database fields
        if (!$defaultConfigFile) {
            $defaulFields = (new FieldRepository($model))->getTableFieldsWithTypes();
            $config["fieldDefinitions"] = $this->removeUnwantedFields($defaulFields)?? [];
        } else {
            $config =  $this->getConfigFileFieldsFromFile($defaultConfigFile)?? [];
        }

        return $this->finaliseConfig($config);
    }


    private function finaliseConfig($config) {
       // $config["fieldDefinitions"] = $config["fieldDefinitions"]?? [];
        $config["hiddenFields"] = $config["hiddenFields"]?? [];
        $config["moreActions"] = $config["moreActions"]?? [];
        $config["simpleActions"] = $config["simpleActions"]?? [];
        $config["controls"] = $config["controls"]?? [];

        $config["hiddenFields"] = $this->initialiseHiddenFields($config["hiddenFields"]) ?? [];
        $config["moreActions"] = $config["moreActions"] ?? [];
        $config = $this->setFieldGroups($config);
        $config = $this->setColumns($config);

        $config = $this->setMultiSelectFormFields($config);
        $config = $this->setSingleSelectFormFields($config);


        return $config;
    }


    private function setFieldGroups($config) {
        $fieldGroups = $config["fieldGroups"]?? [];
        if (!$fieldGroups && isset($config["fieldDefinitions"])) {
            $fieldGroups = ["fields" => array_keys($config["fieldDefinitions"])];
            $config["fieldGroups"] = [$fieldGroups];
            return $config;
        }

        // Add the default field group
        $fieldDefinitions = array_keys($config["fieldDefinitions"]);
        $groupedFields = [];
        $ungroupedFields = $fieldDefinitions;
        foreach ($fieldGroups as $group) {
            $groupedFields = array_merge($groupedFields, $group['fields']);
        }

        $ungroupedFields = array_diff($ungroupedFields, $groupedFields);

        if ($ungroupedFields)
            $fieldGroups[] = ["fields" => $ungroupedFields];


        $config["fieldGroups"] = $fieldGroups;

        return $config;

    }



    private function removeUnwantedFields($defaulFields) {
        return $defaulFields; // to be implemented later remove created_at, id etc
    }


    private function getDefaultConfigFile($model) {

        // If module not found return
        $moduleName = explode("\\", $model)[2]?? null;
        if (!$moduleName)
            return null;

        // Config files names are  expected to be in snake case
        // Assuming class like .../User not .../User::class
        $configFileName = Str::snake(class_basename($model));
        $defaultConfigDir = "Modules/$moduleName/Data"; // Data/ is the default

        $fileFullPath = app_path("$defaultConfigDir/$configFileName.php");
       // If file do not exist return
        if (!File::exists($fileFullPath))
            return null;

        return $fileFullPath;
    }


    private function setColumns($config) {
        // Populate the table fields
        $config["columns"] = [];

        foreach (array_keys($config["fieldDefinitions"]) as $fieldName) {
            $config["columns"][] = $fieldName;
        }
        $config["visibleColumns"] = $config["columns"];
        return $config;
    }


    private function setMultiSelectFormFields($config) {
        $available = false;
        foreach (array_keys($config["fieldDefinitions"]) as $fieldName) {
            // Handle multi selection form fields
            if (is_array($config["fieldDefinitions"][$fieldName])
                && isset($config["fieldDefinitions"][$fieldName]['multiSelect'])
                && $config["fieldDefinitions"][$fieldName]['multiSelect'])
            {
                $config["multiSelectFormFields"][$fieldName] = [];
                $available = true;
            }
        }

        // To avoid error set "multiSelectFormFields" to empty array
        if (!$available)
            $config["multiSelectFormFields"] = [];

        return $config;
    }


    private function setSingleSelectFormFields($config) {
        $available = false;
        foreach (array_keys($config["fieldDefinitions"]) as $fieldName) {
            // Handle multi selection form fields
            if (is_array($config["fieldDefinitions"][$fieldName])
                    && !isset($config["fieldDefinitions"][$fieldName]['multiSelect'])
                    && isset($config["fieldDefinitions"][$fieldName]['options'])
                    && !isset($config["fieldDefinitions"][$fieldName]['options']['model'])
                    && !isset($config["fieldDefinitions"][$fieldName]['options']['column'])
                ) // Filter only option that do not have model defined e.g locations => ['Kano' => 'Kano']
            {
                $config["singleSelectFormFields"][$fieldName] = [];
                $available = true;
            }
        }
        // To avoid error set "singleSelectFormFields" to empty array
        if (!$available)
            $config["singleSelectFormFields"] = [];

        return $config;
    }


    public function extractModuleNameFromModel($model)
    {
        // Assuming the model namespace is something like "App\Modules\Inventory\Models\Item"
        $namespaceParts = explode('\\', $model);
        // Extract the module name from the namespace (e.g., "Inventory")
        return $namespaceParts[2] ?? 'default';
    }


    private function initialiseHiddenFields($hiddenFields) {
        // Setup the none existing hidden fields
        if (!isset($hiddenFields['onTable']))
            $hiddenFields['onTable'] = [];
        if (!isset($hiddenFields['onDetail']))
            $hiddenFields['onDetail'] = [];
        if (!isset($hiddenFields['onNewForm']))
            $hiddenFields['onNewForm'] = [];
        if (!isset($hiddenFields['onEditForm']))
            $hiddenFields['onEditForm'] = [];
        if (!isset($hiddenFields['onQuery']))
            $hiddenFields['onQuery'] = [];

        // Add the default hidden fields
        foreach ($this->defaultHiddenFields as $field) {
            if (!in_array($field, array_keys($hiddenFields['onTable'])))
                $hiddenFields['onTable'][] = $field;
            if (!in_array($field, array_keys($hiddenFields['onDetail'])))
                $hiddenFields['onDetail'][] = $field;
            if (!in_array($field, array_keys($hiddenFields['onNewForm'])))
                $hiddenFields['onNewForm'][] = $field;
            if (!in_array($field, array_keys($hiddenFields['onEditForm'])))
                $hiddenFields['onEditForm'][] = $field;
            if (!in_array($field, array_keys($hiddenFields['onQuery'])))
                $hiddenFields['onQuery'][] = $field;
        }

        return $hiddenFields;
    }


    //////////// INITIALISATION METHODS /////////////////
    private function initialiseFieldDefinitions($fieldDefinitions) {
        // If is not defined in the config file, porpulate it from the database table column fields
        if (!$fieldDefinitions) {
            $fieldDefinitions = $this->fieldRepository->getTableFields();
        }

        return $fieldDefinitions;
    }



    public function getSupportedImageColumnNames() {
        return ['image', 'photo', 'picture', 'logo', 'invoice', 'avatar', 'profile_picture'];
    }


    public function getSupportedDocumentColumnNames() {
        return ['document_path', 'document', 'file', 'attachment', 'report', 'manual'];
    }


    public function getSupportedImageExtensions() {
        return ['jpg', 'jpeg', 'png'];
        //return ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'tiff'];
    }

    public function getSupportedDocumentExtensions() {
        return ['pdf', 'doc'];
        //return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'rtf'];
    }   

}


