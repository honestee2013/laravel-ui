<?php 

namespace QuickerFaster\LaravelUI\Services\Database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSchemaService
{
    /**
     * Get all column names for a table
     */
    public static function getTableColumns(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    /**
     * Get column details with type information
     */
    public static function getColumnDetails(string $table): array
    {
        $columns = [];
        $schema = DB::getDoctrineSchemaManager();
        $tableDetails = $schema->listTableDetails($table);

        foreach ($tableDetails->getColumns() as $column) {
            $columns[$column->getName()] = [
                'name' => $column->getName(),
                'type' => $column->getType()->getName(),
                'nullable' => !$column->getNotnull(),
                'default' => $column->getDefault(),
                'length' => $column->getLength(),
                'autoincrement' => $column->getAutoincrement(),
            ];
        }

        return $columns;
    }

    /**
     * Get foreign key relationships for a table
     */
    public static function getForeignKeys(string $table): array
    {
        $foreignKeys = [];
        $schema = DB::getDoctrineSchemaManager();
        $tableDetails = $schema->listTableDetails($table);

        foreach ($tableDetails->getForeignKeys() as $foreignKey) {
            $localColumn = $foreignKey->getLocalColumns()[0];
            $foreignKeys[$localColumn] = [
                'foreign_table' => $foreignKey->getForeignTableName(),
                'foreign_column' => $foreignKey->getForeignColumns()[0],
                'constraint_name' => $foreignKey->getName(),
            ];
        }

        return $foreignKeys;
    }

    /**
     * Check if column exists in database
     */
    public static function isDatabaseColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    /**
     * Get fillable columns (excluding timestamps, soft deletes, etc.)
     */
    public static function getFillableColumns(string $table, $modelClass = null): array
    {
        $columns = self::getTableColumns($table);
        
        // Remove common non-fillable columns
        $nonFillable = ['id', 'created_at', 'updated_at', 'deleted_at'];
        
        // If model class is provided, use its fillable/guarded properties
        if ($modelClass && class_exists($modelClass)) {
            $model = app($modelClass);
            
            // If model has fillable property, use it
            if (property_exists($model, 'fillable') && !empty($model->fillable)) {
                return $model->fillable;
            }
            
            // If model has guarded, exclude guarded from all columns
            if (property_exists($model, 'guarded')) {
                $guarded = $model->guarded?? [];
              
                if (in_array('*', $guarded)) {
                    return []; // All columns are guarded
                }
                $columns = array_diff($columns, $guarded);
            }
        }
        
        // Filter out non-fillable columns
        return array_values(array_diff($columns, $nonFillable));
    }
}