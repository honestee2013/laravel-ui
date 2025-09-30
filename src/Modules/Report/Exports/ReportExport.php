<?php

namespace App\Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;



class ReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $fieldKeys;
    protected $fieldLabels;

    public function __construct(array $exportData)
    {
        $this->data = $exportData['data'];
        $this->fieldKeys = array_keys($exportData['fields']);
        $this->fieldLabels = array_values($exportData['fields']);
    }

    public function collection(): Collection
    {
        return collect($this->data)->map(function ($item) {
            return collect($this->fieldKeys)->mapWithKeys(function ($field) use ($item) {
                return [$field => data_get($item, $field, '')];
            });
        });
    }

    public function headings(): array
    {
        return $this->fieldLabels;
    }
}
