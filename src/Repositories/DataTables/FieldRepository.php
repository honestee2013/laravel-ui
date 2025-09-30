<?php

namespace QuickerFaster\LaravelUI\Repositories\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Eloquent\Model;

class FieldRepository
{
    protected string $model;
    protected string $tableName;

    /**
     * FieldRepository constructor.
     *
     * @param string $modelClass Fully qualified model class name.
     */
    public function __construct(string $modelClass)
    {
        $this->model = $modelClass;
        $this->tableName = (new $modelClass)->getTable();
    }



  /**
     * Get the model's table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getModel(): string
    {
        return $this->model;
    }




    /**
     * Get database fields for the current model's table.
     *
     * @return array
     */
    public function getTableFields(): array
    {
        if (!Schema::hasTable($this->tableName)) {
            return [];
        }

        return Schema::getColumnListing($this->tableName);
    }


    public function getTableFieldsWithTypes($tableName = null)
    {
        if (!$tableName)
            $tableName = $this->tableName;

        $fields = [];

        if (!Schema::hasTable($tableName))
            return $fields;

        // Query the INFORMATION_SCHEMA to get column names and types
        $columns = DB::select(
            "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA = '" . env('DB_DATABASE') . "'"
        );

        // Prepare an array to store columns and their types
        foreach ($columns as $column) {
            $fields[$column->COLUMN_NAME] = $column->DATA_TYPE;
        }

        return $fields;
    }
}
