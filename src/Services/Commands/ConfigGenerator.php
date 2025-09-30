<?php

namespace QuickerFaster\LaravelUI\Services\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class ConfigGenerator extends Command
{



    public function generateConfigFile($module, $modelName, $modelData, $command)
    {

        $configPath = app_path("Modules/{$module}/Data/" . strtolower(Str::snake($modelName)) . '.php'); // Modular path

        if (!File::exists(dirname($configPath))) {
            File::makeDirectory(dirname($configPath), 0755, true); // Create directory if it doesn't exist
        }
        $stub = $this->getConfigStub($module, $modelName, $modelData, $command);
        File::put($configPath, $stub);

        $command->info("Config file created: {$configPath}");
    }



    protected function getConfigStub($module, $modelName, $modelData, $command)
    {
        $configStubPath =  __DIR__ . '/../../Stubs/config.stub';
        $stub = File::get($configStubPath);

        $includes = $modelData['includes'] ?? [];
        $includedConfig = [];

        foreach ($includes as $includeFile) {
            $includePath = app_path("Modules/{$module}/Data/{$includeFile}"); // Adjust path as needed
            if (File::exists($includePath)) {
                $includedConfig = array_merge($includedConfig, include $includePath);
            } else {
                $command->warn("Included file not found: {$includePath}"); // Handle missing files
            }
        }

        $yamlFieldDefinitions = [];
        foreach ($modelData['fields'] as $fieldName => $field) {
            if (isset($field['partial'])) {
                $partialPath = app_path("Modules/{$module}/Data/{$field['partial']}");
                if (File::exists($partialPath)) {
                    $partialFieldDefinitions = include $partialPath;
                    // Merge partial definitions into the main definitions.
                    $yamlFieldDefinitions = array_merge($yamlFieldDefinitions, $partialFieldDefinitions);
                } else {
                    $command->warn("Partial field definition file not found: {$partialPath}");
                }
            } else { // Regular field definition

                // Relationship (belogsTo)
                if (isset($field['foreign'])) {
                    $relatedModel = null;
                    $relationshipType = null;
                    $displayField = null;
                    $hintField = null;
                    $inlineAdd = null;
                    // Find the related model and relationship type from the relations section
                    if (isset($modelData['relations'])) {
                        foreach ($modelData['relations'] as $relationName => $relationData) {
                            if ($relationData['foreignKey'] == $fieldName) { // Match by foreign key
                                $relatedModel = $relationData['model'];
                                $relationshipType = $relationData['type'];
                                $displayField = $relationData['displayField'] ?? 'name';
                                $hintField = $relationData['hintField'] ?? null;
                                $inlineAdd = $relationData['inlineAdd'] ?? false;
                                break; // Found it, exit the loop
                            }
                        }
                    }
                    $dynamic_property = $this->getDynamicProperty($relationshipType, $fieldName);

                    if ($relatedModel && $relationshipType) { // Only if the relationship is found.
                        $yamlFieldDefinitions[$fieldName]['relationship'] = [
                            'model' => $relatedModel, // Correct model path from relations
                            'type' => $relationshipType, // Correct relationship type from relations
                            'display_field' => $displayField, // Or whichever field you want to display
                            'dynamic_property' => $dynamic_property,
                            'foreign_key' => $fieldName,
                            'inlineAdd' => $inlineAdd,
                        ];
                        $yamlFieldDefinitions[$fieldName]['options'] = [
                            'model' => $relatedModel, // Correct model path from relations
                            'column' => $displayField, // Or whichever field you want to display
                            'hintField' => $hintField, // Or whichever field you want to display
                        ];
                    } else {
                        $command->warn("Relationship not found for field: {$fieldName}. Ensure it's defined in the 'relations' section.");
                    }

                    $label = Str::title(str_replace("_", " ", Str::snake($dynamic_property)));

                }
            }

            $display = $relationData['display'] ?? 'inline'; // Default to inline if not specified

            // Handle relations (hasMany, belongsToMany, morphTo, morphToMany)
            if (isset($modelData['relations'])) {
                foreach ($modelData['relations'] as $relationName => $relationData) {
                    $relatedModel = $relationData['model'];
                    $relationshipType = $relationData['type'];
                    $foreignKey = $relationData['foreignKey'] ?? null; // Make foreignKey nullable
                    $displayField = $relationData['displayField'] ?? 'name';
                    $hintField = $relationData['hintField'] ?? null;
                    $label = Str::title(str_replace("_", " ", Str::snake($relationName)));
                    $inlineAdd = $relationData['inlineAdd'] ?? false;

                    $display = $relationData['display'] ?? $display;

                    switch ($relationshipType) {
                        case 'hasMany':
                        case 'belongsToMany':
                            // ... (Existing code for hasMany/belongsToMany remains the same)
                            // Add a virtual field for the hasMany relationship
                            $yamlFieldDefinitions[$relationName] = [
                                'field_type' => 'checkbox', // Or a suitable type for relationships
                                'relationship' => [
                                    'model' => $relatedModel,
                                    'type' => $relationshipType,
                                    'display_field' => $displayField, // Or the field you want to display
                                    'hintField' => $hintField, // Or whichever field you want to display
                                    'dynamic_property' => $relationName, // The name of the relationship
                                    'foreign_key' => $foreignKey,
                                    'local_key' => 'id', // Local key for the relationship
                                    'inlineAdd' => $inlineAdd, // Or false, depending on your needs
                                ],
                                'options' => [
                                    'model' => $relatedModel,
                                    'column' => $displayField, // Or the field you want to display
                                    'hintField' => $hintField, // Or whichever field you want to display
                                ],
                                'label' => Str::title($label), // Label for the field
                                'multiSelect' => true,
                            ];



                            break;

                        case 'morphTo': // Handle morphTo
                            $yamlFieldDefinitions[$relationName] = [
                                'field_type' => 'morphTo', // Indicate it's a morphTo relationship
                                'relationship' => [
                                    'model' => $relatedModel, // Not necessarily needed, but good to have
                                    'type' => $relationshipType,
                                    'dynamic_property' => $relationName,
                                ],
                                'label' => Str::title($relationName),
                            ];
                            break;

                        case 'morphToMany': // Handle morphToMany
                            $pivotTable = $relationData['pivotTable'];
                            $relatedPivotKey = $relationData['relatedPivotKey'];
                            $morphType = $relationData['morphType'];
                            $inlineAdd = $relationData['inlineAdd'] ?? false;


                            $yamlFieldDefinitions[$relationName] = [
                                'field_type' => 'morphToMany', // Indicate it's a morphToMany relationship
                                'relationship' => [
                                    'model' => $relatedModel,
                                    'type' => $relationshipType,
                                    'display_field' => $displayField,
                                    'dynamic_property' => $relationName,
                                    'foreign_key' => $foreignKey,
                                    'related_pivot_key' => $relatedPivotKey,
                                    'morph_type' => $morphType,
                                    'pivot_table' => $pivotTable,
                                    'inlineAdd' => $inlineAdd,
                                ],
                                'options' => [
                                    'model' => $relatedModel,
                                    'column' => $displayField,
                                    'hintField' => $hintField, // Or whichever field you want to display
                                ],
                                'label' => Str::title($relationName),
                                'multiSelect' => true,
                            ];
                            break;
                    }
                }
            }




            $yamlFieldDefinitions[$fieldName]["display"] = $display;
            $yamlFieldDefinitions[$fieldName]["field_type"] = $field['type'] ?? "string";


            // HTML form Input type correction
            if ($yamlFieldDefinitions[$fieldName]['field_type'] == 'decimal')
                $yamlFieldDefinitions[$fieldName]['field_type'] = 'number';

            if (isset($field['validation'])) {
                $validationString = implode('|', $field['validation']);
                $yamlFieldDefinitions[$fieldName]['validation'] = $validationString;
            }

            if (isset($field['options'])) {
                $options = $field['options'];
                if (!is_array($options)) {
                    $options = array_values(explode(',', Str::title($options)));
                    $yamlFieldDefinitions[$fieldName]['options'] = array_combine($options, $options); // map as ["value" => "value"]
                } else {
                    $yamlFieldDefinitions[$fieldName]['options'] = $options;
                }

            }

            // Default label
            $label = str_replace("_id", "", $fieldName);
            $label = Str::title(str_replace("_", " ", Str::snake($label)));



            // Other field configuration
            $yamlFieldDefinitions[$fieldName]['label'] = $field["label"]?? $label;

            // Check auto generate
            if (isset($field['autoGenerate']) && $field['autoGenerate'] == true) {
                $yamlFieldDefinitions[$fieldName]['autoGenerate'] =  $field['autoGenerate'];
            } 

            // Check multiselect
            if (isset($field['multiSelect']) && $field['multiSelect'] == true) {
                $yamlFieldDefinitions[$fieldName]['multiSelect'] = true;
            }

            // Check Reactivity
            if (isset($field['reactivity']) ){
                $yamlFieldDefinitions[$fieldName]['reactivity'] = $field['reactivity'];
            }

            /*foreach ($field as $key => $value) {
                $yamlFieldDefinitions[$fieldName][$key] = $value ;
            }*/


            //if ($fieldName== "status" )
              //dd($yamlFieldDefinitions[$fieldName]);

        }




        // ... (Rest of the code for fieldGroups, merging configs, and replacements remains the same)

        // Handle fieldGroups
        $fieldGroups = $modelData['fieldGroups'] ?? [];
        $fieldGroupsString = var_export($fieldGroups, true);
        $fieldGroupsString = str_replace("array (", "[", str_replace(")", "]", $fieldGroupsString)); // Clean up var_export


        $report = $modelData['report'] ?? [];
        if ($report) {
            if (isset($report["model"]))
                $report["model"] = "App\\Modules\\{$module}\\Models\\{$report["model"]}";
            if (isset($report["itemsModel"]))
                $report["itemsModel"] = "App\\Modules\\{$module}\\Models\\{$report["itemsModel"]}";
            if (isset($report["recordModel"]))
                $report["recordModel"] = "App\\Modules\\{$module}\\Models\\{$report["recordModel"]}";
            
        }

        // Merge included configs and YAML generated configs (include overrides YAML if both exist).
        $ucModule = ucfirst($module);
        $configData = array_merge(
            $includedConfig, // Include first, so it has lower override priority.
            [
                "model" => "App\\Modules\\{$ucModule}\\Models\\{$modelName}",
                "fieldDefinitions" => $yamlFieldDefinitions,
                "hiddenFields" => $modelData['hiddenFields'] ?? [],
                "simpleActions" => $modelData['simpleActions'] ?? [],
                "isTransaction" => $modelData['isTransaction'] ?? false,
                "dispatchEvents" => $modelData['dispatchEvents'] ?? false,
                "controls" => $modelData['controls'] ?? [],
                "fieldGroups" => $fieldGroups, // Add fieldGroups to the config data
                "moreActions" => $modelData['moreActions'] ?? [],
                "report" => $report, // Add report configuration
            ],
        );

        /*foreach ($modelData as $key => $modelDataItem) {
            if (!array_search($key, array_keys($configData)))
                $configData[$key] = $modelDataItem;
        }*/


        $configString = var_export($configData, true);
        $configString = str_replace("=> \n", "=>", $configString); // Place [ on the same line

        $configString = str_replace("array (", "[", str_replace(")", "]", $configString)); // Clean up var_export
        $configString = str_replace("],", "], \n", $configString); // Add newline after ] closes

        $stub = str_replace('{{configData}}', $configString, $stub);

        return $stub;

    }











    private function getDynamicProperty($relationshipType, $fieldName)
    {
        $fieldName = str_replace("_id", "", $fieldName);
        $fieldName = Str::camel($fieldName); // eg user_name to userName

        if ($relationshipType == "hasMany" || $relationshipType == "blongsToMany")
            $fieldName = Str::plural($fieldName);

        return $fieldName;
    }



}
