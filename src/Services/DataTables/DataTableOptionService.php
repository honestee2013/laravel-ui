<?php


namespace QuickerFaster\LaravelUI\Services\DataTables;

class DataTableOptionService
{
   /* public function getOptionList(array $config)
    {
        if (!isset($config['model'], $config['column'])) {
            return []; // Prevent errors if config is incomplete
        }

        $modelClass = $config['model'];
        $key = isset($config['key'])? isset($config['key']): 'id';
        $column = $config['column'];

        if (!class_exists($modelClass)) {
            return []; // Return empty array if model does not exist
        }

        return $modelClass::pluck($column, $key)->toArray();
    }*/


    public function getOptionList($config)
    {
        // ... (Existing code)

        $modelClass = $config['model'];
        $column = $config['column']; // Could be 'user.name', 'company.name', etc.
        $key = $config['key'] ?? 'id';
        $hintField = $config['hintField'] ?? null;
        $filters = $config['filters'] ?? [];

        if (!class_exists($modelClass)) {
            return [];
        }

        $query = $modelClass::query();

        if (isset($filters)) {
            foreach ($filters as $filter) {
                $query->where($filter[0], $filter[1], $filter[2]);
            }
        }


        // Handle nested relationships dynamically
        if (strpos($column, '.') !== false) { // Check if the column has a dot (indicating a relationship)
            $relationshipParts = explode('.', $column); // Split the string (e.g., ['user', 'name'])
            $relationshipName = $relationshipParts[0]; // e.g., 'user'
            $relatedColumn = $relationshipParts[1];    // e.g., 'name'

            // Eager load the relationship
            $query->with($relationshipName);

            return $query->get()->mapWithKeys(function ($obj) use ($key, $relationshipName, $relatedColumn, $hintField) {
                $relatedModel = $obj->$relationshipName; // Admin the related model dynamically
                $val = $relatedModel ? $relatedModel->$relatedColumn : null;

                $key = $obj->$key;

                if (!empty($obj->hintField))
                    $val .= " (".$obj->hintField. ")";

                return [$key => $val];
            })->toArray();
        } else {
            // Handle regular columns (no relationship)
            if (!$hintField) {
                $model = $query->getModel();
                $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
                if (in_array($column, $columns) && in_array($key, $columns)) {
                    return $query->pluck($column, $key);
                } else {
                    // Handle the case where either $column or $key doesn't exist
                    return []; // Or you could throw an exception or return a specific error
                }
            }
        
            return $query->get()->mapWithKeys(function ($obj) use ($column, $key, $hintField) {
                $localObj = $obj->getAttributes(); // Get the attributes array
                $keyVal = $obj->$key;
                $val = null;
                if (array_key_exists($column, $localObj)) {
                    $val = $obj->$column;
                    if (array_key_exists($hintField, $localObj) && !empty($obj->$hintField)) {
                        $val .= " (" . $obj->$hintField . ")";
                    }
                    return [$keyVal => $val];
                }

                return [$keyVal => $obj->$column];
                
            })->toArray();
        }

    }


}
