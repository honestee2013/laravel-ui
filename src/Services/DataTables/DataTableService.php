<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\System\Contracts\DataTable\CellFormatterInterface;

class DataTableService
{
    public function buildQuery($model, $columns, $fieldDefinitions, $search, $queryFilters, $pageQueryFilters, $sortField, $sortDirection, $hiddenFields,)
    {
        $modelClass = '\\' . ltrim($model, '\\');
        $query = (new $modelClass)->newQuery();

        // Apply search filters
        if (!empty($search)) {
            $query = $this->applySearch($query, $columns, $fieldDefinitions, $search, $hiddenFields);
        }

        // Apply query filters
        if ((is_array($queryFilters) && !empty($queryFilters)) || (is_array($pageQueryFilters) && !empty($pageQueryFilters))) {
            $query = $this->applyQueryFilters($query, $queryFilters, $pageQueryFilters, $fieldDefinitions);
        }

        // Apply sorting
        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    protected function applySearch(Builder $query, $columns, $fieldDefinitions, $search, $hiddenFields)
    {
        return $query->where(function ($q) use ($columns, $fieldDefinitions, $search, $hiddenFields) {
            foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
                // Skip hidden fields
                if (in_array($fieldName, $hiddenFields)) {
                    continue;
                }

                // Handle relationship fields
                if (isset($fieldDefinition['relationship'])) {
                    $this->applyRelationshipSearch($q, $fieldDefinition, $search);
                } else {
                    // Handle regular fields
                    $q->orWhere($fieldName, 'like', '%' . $search . '%');
                }
            }
        });
    }

    protected function applyRelationshipSearch(Builder $query, $fieldDefinition, $search)
    {
        $relationship = $fieldDefinition['relationship'];
        
        if (!isset($relationship['type']) || !isset($relationship['display_field']) || !isset($relationship['dynamic_property'])) {
            return;
        }

        $relationshipType = $relationship['type'];
        $displayField = explode(".", $relationship['display_field']);
        $displayField = count($displayField) > 1 ? "id" : $displayField[0];
        $dynamicProperty = $relationship['dynamic_property'];

        if (in_array($relationshipType, ['belongsTo', 'hasMany', 'belongsToMany'])) {
            $query->orWhereHas($dynamicProperty, function ($relatedQuery) use ($displayField, $search) {
                $relatedQuery->where($displayField, 'like', '%' . $search . '%');
            });
        }
    }

    protected function applyQueryFilters(Builder $query, $queryFilters, $pageQueryFilters, $fieldDefinitions)
    {
        foreach ($queryFilters as $filter) {
            if (!is_array($filter) || count($filter) !== 3) {
                continue;
            }

            [$field, $operator, $value] = $filter;

            // Handle relationship filters
            if (isset($fieldDefinitions[$field]["relationship"])) {
                $this->applyRelationshipFilter($query, $fieldDefinitions[$field], $field, $operator, $value);
            } else {
                // Handle regular filters
                if ($operator === 'in') {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $operator, $value);
                }
            }
        }

        // Add page filter query
        foreach ($pageQueryFilters as $filter) {
            if (!is_array($filter) || count($filter) !== 3) {
                continue;
            }

            [$field, $operator, $value] = $filter;
            $query->where($field, $operator, $value);
        }
        return $query;
    }

    protected function applyRelationshipFilter(Builder $query, $fieldDefinition, $field, $operator, $value)
    {
        $relationship = $fieldDefinition['relationship'];
        
        if (!isset($relationship['type']) || !isset($relationship['dynamic_property'])) {
            return;
        }

        // Check if we have a dot notation for relationship field
        if (str_contains($field, ".")) {
            [$relationshipField, $column] = explode(".", $field);
        } else {
            // If no column specified, use the display field
            $column = $relationship['display_field'] ?? 'id';
            if (str_contains($column, ".")) {
                $column = explode(".", $column)[1];
            }
        }

        $dynamicProperty = $relationship['dynamic_property'];
        
        $query->whereHas($dynamicProperty, function ($query) use ($column, $operator, $value) {
            if ($operator === 'in') {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    public function formatCellValue($row, $column, $fieldDefinitions)
    {
        $value = $row->$column;

        if (isset($fieldDefinitions[$column]["formatter"])) {
            $formatter = $fieldDefinitions[$column]["formatter"];
            
            if (is_callable($formatter)) {
                return $formatter($value, $row);
            } 
            
            if (class_exists($formatter) && in_array(CellFormatterInterface::class, class_implements($formatter))) {
                return $formatter::format($value, $row);
            }
        }

        return $value;
    }
}