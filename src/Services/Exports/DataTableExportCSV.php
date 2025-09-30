<?php

namespace QuickerFaster\LaravelUI\Services\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataTableExportCSV implements FromCollection, WithHeadings
{
    protected $data;
    protected $fieldDefinitions;
    protected $visibleColumns;

    public function __construct($data, $fieldDefinitions, $visibleColumns)
    {
        $this->data = $data;
        $this->fieldDefinitions = $fieldDefinitions;
        $this->visibleColumns = $visibleColumns;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            return collect($this->visibleColumns)->mapWithKeys(function ($col) use ($row) {
                $value = '';

                if (isset($this->fieldDefinitions[$col]['relationship'])) {
                    $rel = $this->fieldDefinitions[$col]['relationship'];
                    $dp = $rel['dynamic_property'];
                    $df = $rel['display_field'] ?? 'name';

                    if (in_array($rel['type'], ['hasMany', 'belongsToMany'])) {
                        $value = $row->$dp?->pluck($df)->implode(', ');
                    } else {
                        $value = $row->$dp?->{$df} ?? '';
                    }
                } else {
                    $value = $row->$col;
                }

                return [$col => $value];
            });
        });
    }

    public function headings(): array
    {
        return collect($this->visibleColumns)->map(function ($col) {
            return $this->fieldDefinitions[$col]['label'] ?? ucfirst(str_replace('_', ' ', $col));
        })->toArray();
    }
}
