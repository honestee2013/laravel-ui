<?php


namespace App\Modules\Report\Services;

use QuickerFaster\CodeGen\Facades\DataTables\DataTableConfig;


use Illuminate\Support\Str;



class ReportService
{
    protected $modelName;
    protected $id;
    protected $module;
    protected $config;
    protected $record;
    protected $items;
    protected $baseNamespace;

    public static function make($modelName, $id, $module = null)
    {
        $service = new self();
        $service->modelName = $modelName;
        $service->id = $id;
        $service->module = $module ?? 'Hr';

        $service->baseNamespace = "App\\Modules\\{$service->module}";

        $service->loadConfig();
        $service->loadData();

        return $service;
    }

    protected function loadConfig()
    {
        $baseNamespace = "{$this->baseNamespace}\\Data\\";

        // Temporary bootstrap config to extract models
        $bootstrapConfig = DataTableConfig::getConfigFileFields($baseNamespace . $this->modelName);

        $recordModelClass = $bootstrapConfig['report']['recordModel'] ?? $bootstrapConfig['report']['model'];
        $itemsModelClass  = $bootstrapConfig['report']['itemsModel'];

        // Convert model class to snake_case file name
        $recordFile = Str::snake(class_basename($recordModelClass));
        $itemsFile  = Str::snake(class_basename($itemsModelClass));

        $this->config = [
            'record' => DataTableConfig::getConfigFileFields($baseNamespace . $recordFile),
            'items'  => DataTableConfig::getConfigFileFields($baseNamespace . $itemsFile),
        ];
    }


    protected function loadData()
    {
        $reportConfig = $this->config['record']['report'];

        // Resolve models
        $recordModelClass = $reportConfig['recordModel'] ?? $reportConfig['model'];
        $itemModelClass   = $reportConfig['itemsModel'];

        // Load main record
        $this->record = $recordModelClass::findOrFail($this->id);

        // Load related items
        $query = $itemModelClass::query();

        // Apply filters (if defined)
        if (!empty($reportConfig['filters']) && is_array($reportConfig['filters'])) {
            foreach ($reportConfig['filters'] as $filter) {
                if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                    $query->where($filter['column'], $filter['operator'], $filter['value']);
                }
            }
        }

        // Apply foreign key constraint
        $foreignKey = $reportConfig['foreignKey'] ?? Str::snake($this->modelName) . '_id'; //eg. payroll_run_id

        $query->where($foreignKey, $this->id);
        ///$query->where($foreignKey, $this->id);

        $this->items = $query->get();

    }

    public function getViewData()
    {
        $reportConfig = $this->config['record']['report'];

        return [
            'reportTitle'   => Str::headline($this->modelName) . ' Report',
            'reportHeader'  => $this->record,
            'reportItems'   => $this->items,
            'config'        => [
                'fieldDefinitions' => $this->config['items']['fieldDefinitions'],
                'tableFields'      => $this->config['items']['report']['tableFields'] ?? [],
                'headerFields'     => $reportConfig['headerFields'] ?? [],
                'summaryFields'    => $reportConfig['summaryFields'] ?? [],
                'signatories'      => $reportConfig['signatories'] ?? [],
            ]
        ];
    }

    public function getExportData()
    {
       // dd($this->items, $this->config['items']['report']['tableFields'] ?? []);
        return [
            'data'   => $this->items,
            'fields' => $this->config['items']['report']['tableFields'] ?? []
        ];
    }
}
