<?php

namespace QuickerFaster\LaravelUI\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Aggregator
{
    protected string $table;
    protected string $model;
    protected string $column = 'id';
    protected string $groupBy = 'daily';
    protected string $groupByTable = '';
    protected string $groupByTableColumn = '';
    protected array $filters = [];
    protected string $aggregationMethod = 'count';
    protected ?string $timeColumn = 'created_at';
    protected ?string $fromTime = null;
    protected ?string $toTime = null;

    // Pivot table configuration
    protected ?string $pivotTable = null;
    protected ?string $pivotModelColumn = null;
    protected ?string $pivotRelatedColumn = null;
    protected ?string $pivotModelType = null;
    protected $pivotRelatedColumnIn = null;

    // Static data for non-database scenarios
    protected array $staticData = [];
    protected bool $useStaticData = false;

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

    public function setStaticData(array $data, array $labels = []): self
    {
        $this->staticData = $data;
        $this->useStaticData = true;

        if (!empty($labels)) {
            $this->staticData['labels'] = $labels;
        }

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

    public function setTimeColumn(string $column): self
    {
        $this->timeColumn = $column;
        return $this;
    }

    public function setTimeRange(string $fromTime, string $toTime): self
    {
        $this->fromTime = $fromTime;
        $this->toTime = $toTime;
        return $this;
    }





    public function setPivotJoin(
        ?string $pivotTable = null,
        ?string $pivotModelColumn = null,
        ?string $pivotRelatedColumn = null,
        ?string $pivotModelType = null,
        $pivotRelatedColumnIn = null
    ): self {
        $this->pivotTable = $pivotTable;
        $this->pivotModelColumn = $pivotModelColumn;
        $this->pivotRelatedColumn = $pivotRelatedColumn;
        $this->pivotModelType = $pivotModelType;
        $this->pivotRelatedColumnIn = $pivotRelatedColumnIn;
        return $this;
    }

    public function groupBy(string $groupBy): self
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function fetch(): array
    {
        // Handle static data scenarios
        if ($this->useStaticData) {
            return $this->fetchStaticData();
        }

        // Handle database scenarios
        if (!isset($this->table) && !isset($this->model)) {
            throw new \Exception("Table or Model is required for database queries");
        }

        $query = $this->buildBaseQuery();
        $this->applyPivotJoin($query);
        $this->applyFilters($query);
        $this->applyTimeRange($query);

        $groupByExpression = $this->getGroupByExpression();
        $aggregationColumn = $this->getAggregationColumn();

        $query->selectRaw("{$groupByExpression} as label, {$this->aggregationMethod}({$aggregationColumn}) as value")
            ->groupBy('label')
            ->orderBy('label', 'asc');

        $data = $query->get();

        $formattedLabels = $this->formatLabels($data->pluck('label')->toArray());

        return [
            'labels' => $formattedLabels,
            'data' => $data->pluck('value')->toArray(),
        ];
    }





    protected function fetchStaticData(): array
    {
        $data = $this->staticData['data'] ?? $this->staticData;
        $labels = $this->staticData['labels'] ?? [];

        // If labels not provided, generate default labels
        if (empty($labels) && !empty($data)) {
            $labels = array_map(fn($index) => "Item " . ($index + 1), array_keys($data));
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }




    protected function buildBaseQuery()
    {
        if (isset($this->model)) {
            return $this->model::query();
        }

        return DB::table($this->table);
    }

    protected function applyPivotJoin($query)
    {
        //dd($this->pivotTable, $this->pivotModelColumn, $this->pivotRelatedColumn);
        // Add validation for required pivot fields
        /*if (!$this->pivotTable || !$this->pivotModelColumn || !$this->pivotRelatedColumn) {
            \Log::warning('Pivot join skipped: Missing required pivot configuration', [
                'table' => $this->pivotTable,
                'model_column' => $this->pivotModelColumn,
                'related_column' => $this->pivotRelatedColumn
            ]);
            return;
        }*/

        $mainTable = $this->getMainTable();

        // Handle pivot relationships (many-to-many)
        if ($this->pivotTable && $this->pivotModelColumn && $this->pivotRelatedColumn) {
            // Your existing pivot join logic...
            if ($this->pivotModelType) {
                $query->join($this->pivotTable, function ($join) use ($mainTable) {
                    $join->on("{$mainTable}.id", '=', "{$this->pivotTable}.{$this->pivotModelColumn}")
                        ->where("{$this->pivotTable}.model_type", $this->pivotModelType);
                });
            } else {
                $query->join($this->pivotTable, "{$mainTable}.id", '=', "{$this->pivotTable}.{$this->pivotModelColumn}");
            }

            // Join with the related table
            if (!empty($this->groupByTable) && !empty($this->groupByTableColumn)) {
                $query->join($this->groupByTable, "{$this->pivotTable}.{$this->pivotRelatedColumn}", '=', "{$this->groupByTable}.id");
            }
        }
        // Handle direct relationships with group_by_table (one-to-many)
        elseif (!empty($this->groupByTable) && !empty($this->groupByTableColumn)) {
            // For direct relationships, join the main table with the group_by_table
            // Assuming the foreign key is in the main table (employees.department_id → departments.id)
            $foreignKey = $this->column; // This should be 'department_id' in your case

            $query->join($this->groupByTable, "{$mainTable}.{$foreignKey}", '=', "{$this->groupByTable}.id");
        }

        // Apply pivot filters if any
        if ($this->pivotRelatedColumnIn) {
            $relatedColumnIn = $this->resolvePivotRelatedColumnIn();
            if (!empty($relatedColumnIn)) {
                $query->whereIn("{$this->pivotTable}.{$this->pivotRelatedColumn}", $relatedColumnIn);
            }
        }
    }

    protected function applyFilters($query)
    {
        foreach ($this->filters as $filter) {
            if (is_callable($filter)) {
                $filter($query);
            } elseif (is_array($filter) && count($filter) === 3) {
                [$key, $operator, $value] = $filter;
                $resolvedValue = $this->resolveFilterValue($value);

                // Handle table prefix for ambiguous columns
                $qualifiedKey = $this->qualifyColumn($key, $this->getMainTable());

                if ($this->columnExists($key, $this->getMainTable())) {
                    $query->where($qualifiedKey, $operator, $resolvedValue);
                }
            }
        }
    }

    protected function applyTimeRange($query)
    {
        if ($this->fromTime && $this->toTime && $this->timeColumn) {
            // Always use the main table for time range to avoid ambiguity
            $mainTable = $this->getMainTable();
            $qualifiedTimeColumn = "{$mainTable}.{$this->timeColumn}";

            $query->whereBetween($qualifiedTimeColumn, [$this->fromTime, $this->toTime]);
        }
    }

    protected function getGroupByExpression(): string
    {
        // If grouping by a related table column
        if (!empty($this->groupByTable) && !empty($this->groupByTableColumn)) {
            return "{$this->groupByTable}.{$this->groupByTableColumn}";
        }

        // If grouping by a specific column
        if (!in_array($this->groupBy, ['daily', 'weekly', 'monthly', 'yearly'])) {
            // Qualify the column with table name to avoid ambiguity
            return $this->qualifyColumn($this->groupBy, $this->getMainTable());
        }

        // Time-based grouping - always use main table to avoid ambiguity
        $mainTable = $this->getMainTable();
        $qualifiedTimeColumn = "{$mainTable}.{$this->timeColumn}";

        return match ($this->groupBy) {
            'daily' => "DATE_FORMAT({$qualifiedTimeColumn}, '%Y-%m-%d')",
            'weekly' => "CONCAT(YEARWEEK({$qualifiedTimeColumn}, 1), '-W')",
            'monthly' => "DATE_FORMAT({$qualifiedTimeColumn}, '%Y-%m')",
            'yearly' => "YEAR({$qualifiedTimeColumn})",
            default => $this->qualifyColumn($this->groupBy, $this->getMainTable()),
        };
    }

    protected function getAggregationColumn(): string
    {
        if ($this->aggregationMethod === 'count') {
            return '*';
        }

        // For pivot queries, use the main table column (qualified)
        if ($this->pivotTable) {
            $mainTable = $this->getMainTable();
            return "{$mainTable}.{$this->column}";
        }

        // Always qualify the column to avoid ambiguity
        return $this->qualifyColumn($this->column, $this->getMainTable());
    }

    protected function getMainTable(): string
    {
        if (isset($this->model)) {
            return (new $this->model())->getTable();
        }

        return $this->table;
    }

    protected function qualifyColumn(string $column, string $table): string
    {
        // If column already contains a table reference, return as-is
        if (str_contains($column, '.')) {
            return $column;
        }

        // Otherwise, qualify it with the table name
        return "{$table}.{$column}";
    }

    protected function resolvePivotRelatedColumnIn()
    {
        if (is_string($this->pivotRelatedColumnIn)) {
            // Handle string expressions like "Role::doesntHave('permissions')->pluck('id')->toArray()"
            if (str_contains($this->pivotRelatedColumnIn, '::')) {
                return eval ("return {$this->pivotRelatedColumnIn};");
            }
        }

        return $this->pivotRelatedColumnIn;
    }

    protected function resolveFilterValue($value)
    {
        if (is_string($value) && str_contains($value, '::')) {
            // Handle static method calls in filters
            return eval ("return {$value};");
        }

        return $value;
    }

    protected function formatLabels(array $labels): array
    {
        return array_map(function ($label) {
            return $this->formatLabel($label);
        }, $labels);
    }

    protected function formatLabel(string $label): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $label)) {
            return \Carbon\Carbon::parse($label)->format('jS M Y');
        } elseif (preg_match('/^\d{4}-\d{2}$/', $label)) {
            return \Carbon\Carbon::parse($label . '-01')->format('F Y');
        } elseif (preg_match('/^\d{4}\d{2}-W$/', $label)) {
            $year = substr($label, 0, 4);
            $week = substr($label, 4, 2);
            return "Week {$week}, {$year}";
        } elseif (is_numeric($label)) {
            return (string) $label;
        }

        return $label;
    }

    protected function columnExists(string $column, string $table): bool
    {
        static $columnsCache = [];

        if (!isset($columnsCache[$table])) {
            $columnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($column, $columnsCache[$table], true);
    }
}




