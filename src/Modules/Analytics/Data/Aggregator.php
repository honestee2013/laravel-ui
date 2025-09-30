<?php
namespace App\Modules\Analytics\Data;

use Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Aggregator
{
    protected string $table;
    protected string $model;
    protected string $column;
    protected string $groupBy;
    protected string $groupByTable;
    protected string $groupByTableColumn;
    protected array $filters = [];
    protected string $aggregationMethod = 'count'; // Default to count


    public ?string $pivotTable = null;
    public ?string $pivotModelColumn = null;
    public ?string $pivotRelatedColumn = null;
    public ?string $pivotModelType = null;
    public $pivotRelatedColumnIn;

    
    



    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setColumn(string $column): self
    {
        $this->column = $column;
        return $this;
    }


    public function setGroupByTable(string $groupByTable): self
    {
        $this->groupByTable = $groupByTable;
        return $this;
    }


    public function setGroupByTableColumn(string $groupByTableColumn): self
    {
        $this->groupByTableColumn = $groupByTableColumn;
        return $this;
    }



    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function setAggregationMethod(string $method): self
    {
        $this->aggregationMethod = match ($method) {
            'sum', 'average', 'count', 'max', 'min' => $method,
            default => throw new \InvalidArgumentException("Invalid aggregation method: {$method}"),
        };
        return $this;
    }


    public function setPivotJoin($pivotTable, $pivotModelColumn, $pivotRelatedColumn, $pivotModelType, $pivotRelatedColumnIn): static
    {
        $this->pivotTable = $pivotTable;
        $this->pivotModelColumn = $pivotModelColumn;
        $this->pivotRelatedColumn = $pivotRelatedColumn;
        $this->pivotModelType = $pivotModelType;
        $this->pivotRelatedColumnIn = $pivotRelatedColumnIn;
        return $this;
    }




    public function groupBy(string $groupBy): self
    {
        $driver = DB::getDriverName();
    
        $this->groupBy = match ($groupBy) {
            'daily' => $driver === 'sqlite'
                ? "strftime('%Y-%m-%d', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m-%d')",
    
            'weekly' => $driver === 'sqlite'
                ? "strftime('%Y-W%W', created_at)"
                : "CONCAT(YEARWEEK(created_at, 1), '-W')",
    
            'monthly' => $driver === 'sqlite'
                ? "strftime('%Y-%m', created_at)"
                : "DATE_FORMAT(created_at, '%Y-%m')",
    
            'yearly' => $driver === 'sqlite'
                ? "strftime('%Y', created_at)"
                : "YEAR(created_at)",
    
            default => $groupBy,
        };
    
        return $this;
    }
    


    /*public function fetch(): array
    {
        $query = null;
        if (!isset($this->table) && !isset($this->model))
            throw new \Exception("Table or Model is required");

        if (isset($this->model)) {
            // Default: use the model's query builder instead of the raw table
            $query = $this->model::query();
            $this->table = (new $this->model())->getTable();

        } else{
            $query = DB::table($this->table);
        }



        $aggregationColumn = $this->aggregationMethod === 'count' ? '*' : $this->column;
        $query->selectRaw("{$this->groupBy} as label, {$this->aggregationMethod}({$aggregationColumn}) as value");

        foreach ($this->filters as $filter) {
            if (is_callable($filter)) {
                // Apply closure-based filters
                $filter($query);
            } elseif (is_array($filter) && count($filter) === 3) {
                // Apply key-operator-value filters
                [$key, $operator, $value] = $filter;

                // Check if column exists in the table
                if ($this->columnExists($key, $this->table)) {
                    $query->where($key, $operator, $value);

                } else {
                    Log::warning("Filter column '{$key}' does not exist in the table or model");
                }

            } else {

                throw new \InvalidArgumentException("Invalid filter format");
            }
        }

        // Group by the label and order by it
        $query->groupBy('label')->orderBy('label', 'asc');

        $data = $query->get();
        
        // Convert labels to user-friendly formats
        $formattedLabels = $data->pluck('label')->map(function ($label) {
            return $this->formatLabel($label);
        })->toArray();


        // Convert groupByTable IDs to their respective names
        if (!empty($formattedLabels) && isset($this->groupByTable) && isset($this->groupByTableColumn)) {
            if (Schema::hasTable($this->groupByTable) && $this->columnExists($this->groupByTableColumn, $this->groupByTable)) {
                if (is_array($formattedLabels)) {
                    $q = DB::table($this->groupByTable);
                    $q->whereIn("id", $formattedLabels);
                    $formattedLabels = $q->pluck($this->groupByTableColumn)->toArray();
                }
            }
        }

        return [
            'labels' => $formattedLabels,
            'data' => $data->pluck('value')->toArray(),
        ];
    }*/




    /*public function fetch(): array
    {
        // Safety checks
        if (!isset($this->table) && !isset($this->model)) {
            throw new \Exception("Table or Model is required");
        }
        // Pivot table aggregation logic
        if ($this->pivotTable && $this->pivotModelKey && $this->pivotRelatedKey && $this->pivotModelType) {
            $query = DB::table($this->pivotTable)
                ->join($this->groupByTable, "{$this->pivotTable}.{$this->pivotRelatedKey}", '=', "{$this->groupByTable}.id")
                ->select("{$this->groupByTable}.{$this->groupByTableColumn} as label", DB::raw("count(*) as value"))
                ->where("{$this->pivotTable}.model_type", $this->pivotModelType)
                ->groupBy("{$this->groupByTable}.{$this->groupByTableColumn}")
                ->orderBy("{$this->groupByTable}.{$this->groupByTableColumn}");
    
            $data = $query->get();
  
            return [
                'labels' => $data->pluck('label')->toArray(),
                'data' => $data->pluck('value')->toArray(),
            ];
        }
    
        // Standard aggregation
        $query = isset($this->model) ? $this->model::query() : DB::table($this->table);
        if (isset($this->model)) {
            $this->table = (new $this->model())->getTable();
        }
    
        $aggregationColumn = $this->aggregationMethod === 'count' ? '*' : $this->column;
        $query->selectRaw("{$this->groupBy} as label, {$this->aggregationMethod}({$aggregationColumn}) as value");
    
        foreach ($this->filters as $filter) {
            if (is_callable($filter)) {
                $filter($query);
            } elseif (is_array($filter) && count($filter) === 3) {
                [$key, $operator, $value] = $filter;
                if ($this->columnExists($key, $this->table)) {
                    $query->where($key, $operator, $value);
                } else {
                    Log::warning("Filter column '{$key}' does not exist in the table or model");
                }
            } else {
                throw new \InvalidArgumentException("Invalid filter format");
            }
        }
    
        $query->groupBy('label')->orderBy('label', 'asc');
        $data = $query->get();
    
        // Convert groupByTable IDs to user-friendly names if possible
        $formattedLabels = $data->pluck('label')->map(function ($label) {
            return $this->formatLabel($label);
        })->toArray();
    
        if (!empty($formattedLabels) && isset($this->groupByTable) && isset($this->groupByTableColumn)) {
            if (Schema::hasTable($this->groupByTable) && $this->columnExists($this->groupByTableColumn, $this->groupByTable)) {
                $q = DB::table($this->groupByTable)->whereIn("id", $formattedLabels);
                $formattedLabels = $q->pluck($this->groupByTableColumn)->toArray();
            }
        }
    
        return [
            'labels' => $formattedLabels,
            'data' => $data->pluck('value')->toArray(),
        ];
    }*/



    public function fetch(): array
{
    $query = null;

    if (!isset($this->table) && !isset($this->model)) {
        throw new \Exception("Table or Model is required");
    }

    if (isset($this->model)) {
        $query = $this->model::query();
        $this->table = (new $this->model())->getTable();
    } else {
        $query = DB::table($this->table);
    }

    $aggregationColumn = $this->aggregationMethod === 'count' ? '*' : $this->column;

    // Handle pivot table aggregation (e.g. model_has_roles)
    if ($this->pivotTable && $this->pivotModelColumn && $this->pivotRelatedColumn) {
        $query->join(
            $this->pivotTable,
            "{$this->table}.id",
            '=',
            "{$this->pivotTable}.{$this->pivotModelColumn}"
        );
    
        if ($this->pivotModelType) {
            $query->where("{$this->pivotTable}.model_type", $this->pivotModelType);
        }

    
       

        if ($this->pivotRelatedColumnIn && is_array($this->pivotRelatedColumnIn)) {
            $query->whereIn("{$this->pivotTable}.{$this->pivotRelatedColumn}", $this->pivotRelatedColumnIn);
        }

    
        $query->selectRaw(
            "{$this->pivotTable}.{$this->pivotRelatedColumn} as label, {$this->aggregationMethod}({$aggregationColumn}) as value"
        );
    } else {

        if ($this->groupByTable && $this->groupByTableColumn) {
            $query->join($this->groupByTable, "{$this->groupByTable}.user_id", '=', "{$this->table}.id");
        
            $query->selectRaw(
                "{$this->groupByTable}.{$this->groupByTableColumn} as label, {$this->aggregationMethod}({$aggregationColumn}) as value"
            );
        } else {
            $query->selectRaw(
                "{$this->groupBy} as label, {$this->aggregationMethod}({$aggregationColumn}) as value"
            );
        }
        
    }

    // Apply filters
    foreach ($this->filters as $filter) {
        if (is_callable($filter)) {
            $filter($query);
        } elseif (is_array($filter) && count($filter) === 3) {
            [$key, $operator, $value] = $filter;
            if ($this->columnExists($key, $this->table)) {
                $qualified = "{$this->table}.{$key}";
                $query->where($qualified, $operator, $value);
            } else {
                Log::warning("Filter column '{$key}' does not exist in the table or model");
            }
        } else {
            throw new \InvalidArgumentException("Invalid filter format");
        }
    }

    $query->groupBy('label')->orderBy('label', 'asc');
    $data = $query->get();

    // Convert label IDs to user-friendly names (e.g., role names)
    $formattedLabels = $data->pluck('label')->map(function ($label) {
        return $this->formatLabel($label);
    })->toArray();

    if (!empty($formattedLabels) && $this->groupByTable && $this->groupByTableColumn) {
        if (
            Schema::hasTable($this->groupByTable) &&
            $this->columnExists($this->groupByTableColumn, $this->groupByTable)
        ) {
            $q = DB::table($this->groupByTable)->whereIn("id", $data->pluck('label')->toArray());
            $formattedLabels = $q->pluck($this->groupByTableColumn)->toArray();
        }
    }

    return [
        'labels' => $formattedLabels,
        'data' => $data->pluck('value')->toArray(),
    ];
}

    





    protected function columnExists(string $column, $table = null): bool
    {
        static $columnsCache = [];

        // Determine the table name
        $table = $table ?? (new ($this->model))?->getTable() ?? null;

        if (!$table) {
            throw new \InvalidArgumentException('Table name or model must be defined to check column existence.');
        }

        // Cache the column listing for the table
        if (!isset($columnsCache[$table])) {
            $columnsCache[$table] = Schema::getColumnListing($table);
        }

        // Check if the column exists
        return in_array($column, $columnsCache[$table], true);
    }








    protected function formatLabel(string $label): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $label)) {
            // Daily format (YYYY-MM-DD)
            return \Carbon\Carbon::parse($label)->format('jS M Y'); // e.g., 21st Dec 2024
        } elseif (preg_match('/^\d{4}-\d{2}$/', $label)) {
            // Monthly format (YYYY-MM)
            return \Carbon\Carbon::parse($label . '-01')->format('F Y'); // e.g., December 2024
        } elseif (preg_match('/^\d{4}\d{2}-W$/', $label)) {
            // Weekly format (YearWeek-W)
            $year = substr($label, 0, 4);
            $week = substr($label, 4, 2);
            return "Week {$week}, {$year}";
        } elseif (is_numeric($label)) {
            // Yearly format (e.g., 2024)
            return (string)$label;
        }

        // Default: return as-is
        return $label;
    }






    public function setTimeRange(string $fromTime, string $toTime): self
    {
        $this->filters[] = function ($query) use ($fromTime, $toTime) {
            $table = $query->from; // get the main table name dynamically
            $query->whereBetween("{$table}.created_at", [$fromTime, $toTime]);
        };
    
        return $this;
    }
    



}
