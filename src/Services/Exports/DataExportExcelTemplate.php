<?php

namespace QuickerFaster\LaravelUI\Services\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class DataExportExcelTemplate implements FromCollection, WithHeadings, WithEvents
{
    protected $data;
    protected $fieldDefinitions;
    protected $visibleColumns;
    protected $withData;

    public function __construct($data, $fieldDefinitions, $visibleColumns, $withData = false)
    {
        $this->data = $data;
        $this->fieldDefinitions = $fieldDefinitions;
        $this->visibleColumns = $visibleColumns;
        $this->withData = $withData;
    }



    public function headings(): array
    {
        return $this->visibleColumns;
    }

    /**
     * Register Events to handle Data Validation (Dropdowns)
     */
public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $spreadsheet = $event->sheet->getParent();
            $sheet = $event->sheet->getDelegate();
            
            // 1. Create the Options Sheet
            $optionsSheet = $spreadsheet->createSheet();
            $optionsSheet->setTitle('DataOptions');
            $optionsSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            $columnOffset = 0; // To keep track of where we put lists in the hidden sheet

            foreach ($this->visibleColumns as $index => $columnName) {
                $definition = $this->fieldDefinitions[$columnName] ?? null;

                // Check if this column has a relationship
                if ($definition && isset($definition['relationship'])) {
                    
                    $modelClass = $definition['relationship']['model'] ?? null;
                    if (!$modelClass || !class_exists($modelClass)) continue;

                    // 2. Fetch Data and Write to Hidden Sheet
                    $items = $modelClass::pluck('name')->toArray();
                    if (empty($items)) continue;

                    $optColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnOffset + 1);
                    foreach ($items as $rowIdx => $name) {
                        $optionsSheet->setCellValue($optColLetter . ($rowIdx + 1), $name);
                    }

                    // 3. Create a NAMED RANGE (LibreOffice loves these)
                    $rangeName = "list_" . $columnName;
                    $lastRow = count($items);
                    $spreadsheet->addNamedRange(
                        new \PhpOffice\PhpSpreadsheet\NamedRange(
                            $rangeName, 
                            $optionsSheet, 
                            "\$${optColLetter}\$1:\$${optColLetter}\$${lastRow}"
                        )
                    );

                    // 4. Apply Validation to the Main Sheet
                    $mainColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                    $validation = $sheet->getDataValidation("${mainColLetter}2:${mainColLetter}100");
                    
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    
                    // Use the Named Range as the formula
                    $validation->setFormula1($rangeName); 

                    $columnOffset++;
                }
            }
        },
    ];
}



    public function collection()
    {

        if (!$this->withData) // Empty template
            return new Collection();

        return $this->data->map(function ($row) {
            return collect($this->visibleColumns)->mapWithKeys(function ($col) use ($row) {
                $value = '';

                if (isset($this->fieldDefinitions[$col]['relationship'])) {
                    $rel = $this->fieldDefinitions[$col]['relationship'];
                    $dp = $rel['dynamic_property'];
                    $df = $rel['display_field'] ?? 'name';

                    /*if (in_array($rel['type'], ['hasMany', 'belongsToMany'])) {
                        $value = $row->$dp?->pluck($df)->implode(', ');
                    } else {
                        $value = $row->$dp?->{$df} ?? '';
                    }*/

                    if (str_ends_with($col, '_id')) 
                        $value = $row->$dp?->{$df} ?? '';
                } else {
                    $value = $row->$col;
                }

                return [$col => $value];
            });
        });
    }




}
