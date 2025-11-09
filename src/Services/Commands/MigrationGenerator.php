<?php

namespace QuickerFaster\LaravelUI\Services\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class MigrationGenerator extends Command
{




    protected function getMigrationPath($module, $modelName, $is_pivot = false)
    {


        $migrationName = 'create_' . strtolower(Str::plural(Str::snake($modelName))) . '_table';

        if ($is_pivot) {
            $migrationName = 'create_' . strtolower(Str::singular(Str::snake($modelName))) . '_table';
        }


        $migrationPath = app_path("Modules/" . ucfirst($module) . "/Database/Migrations/");

        sleep(1);
        $migrationFullPath = $migrationPath . date('Y_m_d_His') . '_' . $migrationName . '.php';  // Modular path

        if (File::exists($migrationPath)) { // Delete the existing migration file
            foreach (File::files($migrationPath) as $file) {
                if (str_contains($file->getFilename(), $migrationName)) {
                    //echo "Overiding: {$file->getRealPath()}";
                    //File::delete($file->getRealPath());
                    return $migrationPath . $file->getBasename(); // Return the file if it exist
                }
            }
        } else {
            // Create the required migration path
            File::makeDirectory($migrationPath, 0755, true); // Create directory if it doesn't exist
        }

        return $migrationFullPath;
    }
    public function generateMigration($module, $modelName, $modelData)
    {
        if (isset($modelData['relations'])) {
            foreach ($modelData['relations'] as $relationName => $relationData) {
                switch ($relationData['type']) {
                    case 'belongsToMany':
                        $pivotTableName = $relationData['pivotTable'] ?? Str::singular(Str::lower($modelName)) . '_' . Str::singular(Str::lower(class_basename($relationData['model'])));
                        $foreignKey1 = $relationData['relatedKey'];
                        $foreignKey2 = $relationData['foreignKey'];

                        $model1 = Str::snake($modelName);
                        $model2 = Str::snake(class_basename($relationData['model']));
                        $this->generatePivotMigration($module, $pivotTableName, $model2, $model1, $foreignKey1, $foreignKey2);
                        break;

                    case 'morphToMany':
                        $pivotTableName = $relationData['pivotTable'];
                        $foreignKey = $relationData['foreignKey'];
                        $relatedPivotKey = $relationData['relatedPivotKey'];
                        $morphType = $relationData['morphType'];

                        $this->generatePolymorphicPivotMigration($module, $pivotTableName, $modelName, $foreignKey, $relatedPivotKey, $morphType);
                        break;

                    case 'belongsTo':
                        $isPivot = $modelData['isPivot'] ?? false;
                        if (!$isPivot) {
                            $migrationFullPath = $this->getMigrationPath($module, $modelName, $isPivot);
                            $stub = $this->getMigrationStub($module, $modelName, $modelData);
                            File::put($migrationFullPath, $stub);
                        }
                        break;

                    case 'hasMany':
                        $isPivot = $modelData['isPivot'] ?? false;
                        if (!$isPivot) {
                            $migrationFullPath = $this->getMigrationPath($module, $modelName, $isPivot);
                            $stub = $this->getMigrationStub($module, $modelName, $modelData);
                            File::put($migrationFullPath, $stub);
                        }
                        break;
                    case 'hasOne':
                        $isPivot = $modelData['isPivot'] ?? false;
                        if (!$isPivot) {
                            $migrationFullPath = $this->getMigrationPath($module, $modelName, $isPivot);
                            $stub = $this->getMigrationStub($module, $modelName, $modelData);
                            File::put($migrationFullPath, $stub);
                        }
                        break;
                        
                        
                    case 'morphTo':
                        break;

                    default:
                        break;
                }
            }
        } else {
            // No relationships defined; generate migration from fields
            $isPivot = $modelData['isPivot'] ?? false;
            $migrationFullPath = $this->getMigrationPath($module, $modelName, $isPivot);
            $stub = $this->getMigrationStub($module, $modelName, $modelData);
            File::put($migrationFullPath, $stub);
        }
    }



    protected function generatePolymorphicPivotMigration($module, $pivotTableName, $modelName, $foreignKey, $relatedPivotKey, $morphType)
    {
        $migrationName = 'create_' . $pivotTableName . '_table';
        $migrationFullPath = $this->getMigrationPath($module, $migrationName);

        $stubPath = __DIR__ . '/../../Stubs/polymorphic_pivot_migration.stub';
        $stub = File::get($stubPath);

        //$stub = File::get(app_path('Modules/system/Stubs/polymorphic_pivot_migration.stub')); // Create this stub

        $stub = str_replace('{{pivotTableName}}', $pivotTableName, $stub);
        $stub = str_replace('{{modelName}}', strtolower($modelName), $stub);
        $stub = str_replace('{{foreignKey}}', $foreignKey, $stub);
        $stub = str_replace('{{relatedPivotKey}}', $relatedPivotKey, $stub);
        $stub = str_replace('{{morphType}}', $morphType, $stub);

        File::put($migrationFullPath, $stub);

        echo "Pivot migration created: {$migrationFullPath}";
    }

    protected function getMigrationStub($module, $modelName, $modelData)
    {

        $fields = $modelData['fields']?? [];
        $moduleStubPath = app_path("Modules/{$module}/Stubs/Database/Migrations/{$modelName}.stub");
        if (File::exists($moduleStubPath)) {
            $stub = File::get($moduleStubPath);
        } else {
            //$coreStubPath = app_path('Modules/system/Stubs/migration.stub'); // Fallback
            $coreStubPath = __DIR__ . '/../../Stubs/migration.stub';

            if (!File::exists($coreStubPath)) {
                $this->error("Migration stub not found: {$coreStubPath} or {$moduleStubPath}");
                return "";
            }
            $stub = File::get($coreStubPath);
        }

        $columns = '';
        foreach ($fields as $fieldName => $fieldData) {
            if (str_contains($fieldName, '_confirmation'))
                continue; // eg. "password_confirmation"

            $type = $this->getFieldType($fieldData);
            $modifiers = $fieldData['modifiers'] ?? [];
            $foreign = $fieldData['foreign'] ?? null;

            $columnDefinition = "\$table->{$type}('{$fieldName}'"; // Open parentheses

            // Handle length for specific types
            if (in_array($type, ['string', 'varchar', 'char']) && isset($modifiers['length'])) {
                $columnDefinition .= ", {$modifiers['length']}";
            }

            // Handle decimal type (precision and scale)
            if ($type === 'decimal' && isset($modifiers['precision'])) {
                $parts = explode(',', $modifiers['precision']);
                $precision = $parts[0] ?? 8;
                $scale = $parts[1] ?? 2;
                $columnDefinition .= ", {$precision}, {$scale}";
            }

            $columnDefinition .= ")"; // Ensure it closes here

            // Handle modifiers (nullable, unique, default) as chained methods
            $columnDefinition = $this->addModifiers($columnDefinition, $modifiers);

            // Handle foreign keys separately
            if ($foreign) {
                $columnDefinition = "\$table->foreignId('{$fieldName}')";
                $columnDefinition = $this->addModifiers($columnDefinition, $modifiers);
                $columnDefinition .= "->constrained('{$foreign['table']}', '{$foreign['column']}')";

                if (isset($foreign['onDelete'])) {
                    $onDeleteMethod = Str::camel($foreign['onDelete']) . 'OnDelete';
                    $columnDefinition .= "->{$onDeleteMethod}()";
                }
            }

            $columnDefinition .= ";\n\t\t\t";
            $columns .= $columnDefinition;
        }


        $stub = str_replace('{{modelName}}', $modelName, $stub);
        $stub = str_replace('{{tableName}}', strtolower(Str::plural(Str::snake($modelName))), $stub);
        $stub = str_replace('{{columns}}', $columns, $stub);
        $stub = str_replace('{{indexes}}', $this->generateIndexes($modelData), $stub);
        return $stub;
        
    }



protected function generateIndexes($modelData): string
{

    $indexLines = [];
    if (isset($modelData["indexes"])) {
        foreach ($modelData['indexes'] ?? [] as $index) {
            $col = is_array($index) ? $index['column'] : $index;
            $indexLines[] = "\t\t\t\$table->index('{$col}');";
        }
    }

    if (isset($modelData["uniqueIndexes"])) {
        foreach ($modelData['uniqueIndexes'] ?? [] as $index) {
            $col = is_array($index) ? $index['column'] : $index;
            $indexLines[] = "\t\t\t\$table->unique('{$col}');";
        }
    }

    if (isset($modelData["compoundIndexes"])) {
        foreach ($modelData['compoundIndexes'] ?? [] as $cols) {
            //$cols = $compound['columns'];
            $colList = implode("', '", $cols);
            $indexLines[] = "\t\t\t\$table->index(['{$colList}']);";
        }
    }


    return implode("\n", $indexLines);
}




    private function getFieldType($fieldData)
    {
        $type = $fieldData['type'] ?? 'string'; // Default to string
        // Handle custome types like datetime picker to avoid database type errors
        if (str_contains($type, "datetime"))
            return "datetime";
        else if (str_contains($type, "date"))
            return "date";
        else if (str_contains($type, "time"))
            return "time";
        else if (str_contains($type, "text"))
            return "text";
        else if (
            str_contains($type, "select")
            || str_contains($type, "file")
            || str_contains($type, "checkbox")
            || str_contains($type, "radio")
            || str_contains($type, "email")
            || str_contains($type, "password")
        )
            return "string";
        else
            return $type;
    }



    private function addModifiers($columnDefinition, $modifiers)
    {
        // Handle modifiers (nullable, unique, default) as chained methods
        foreach ($modifiers as $modifierName => $modifierValue) {
            if ($modifierName === 'nullable' && $modifierValue === true) { // Check for nullable: true
                $columnDefinition .= "->nullable()";
            } elseif ($modifierName === 'unique' && $modifierValue === true) {
                $columnDefinition .= "->unique()";
            } elseif ($modifierName === 'default') { //e.g default:100
                
                if ($modifierValue === true) {
                    $columnDefinition .= "->default(true)";
                } elseif ($modifierValue === false) {
                    $columnDefinition .= "->default(false)";
                } elseif (is_string($modifierValue) && !is_numeric($modifierValue)) {
                    $columnDefinition .= "->default('" . str_replace("'", "\\'", $modifierValue) . "')";
                } else {
                    $columnDefinition .= "->default($modifierValue)";
                }

            }
        }

        return $columnDefinition;
    }



    protected function generatePivotMigration($module, $pivotTableName, $model1, $model2, $foreignKey1, $foreignKey2)
    {

        $migrationPath = $this->getMigrationPath($module, $pivotTableName, true);

        $stub = $this->getPivotMigrationStub($pivotTableName, $model1, $model2, $foreignKey1, $foreignKey2);

        File::put($migrationPath, $stub);

        echo "Migration file created: {$migrationPath}";
        echo "Please run `php artisan migrate` to apply the migration."; // Informative message
    }

    protected function getPivotMigrationStub($pivotTableName, $model1, $model2, $foreignKey1, $foreignKey2)
    {
        //$stub = File::get(app_path('Modules/system/Stubs/pivot_migration.stub'));
        $stubPath = __DIR__ . '/../../Stubs/pivot_migration.stub';
        $stub = File::get($stubPath);

        $stub = str_replace('{{pivotTableName}}', $pivotTableName, $stub);
        $stub = str_replace('{{model1}}', strtolower(Str::snake($model1)), $stub); // Model name for foreign key constraint
        $stub = str_replace('{{model2}}', strtolower(Str::snake($model2)), $stub); // Model name for foreign key constraint
        $stub = str_replace('{{foreignKey1}}', $foreignKey1, $stub);
        $stub = str_replace('{{foreignKey2}}', $foreignKey2, $stub);

        return $stub;
    }







}
