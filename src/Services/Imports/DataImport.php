<?php

namespace QuickerFaster\LaravelUI\Services\Imports;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class DataImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected $model;
    protected $columns;
    protected $fieldDefinitions;
    protected $validationRules;
    protected $relationshipFields = [];
    protected $errors = [];
    protected $currentRowNumber = 2; // Start from row 2 (after header)

    public function __construct($model, $columns, $validationRules, $fieldDefinitions)
    {
        $this->model = $model;
        $this->columns = $columns;
        $this->validationRules = $validationRules;
        $this->fieldDefinitions = $fieldDefinitions;
        
        // Identify relationship fields
        foreach ($this->columns as $column) {
            if (str_ends_with($column, '_id')) {
                // $this->relationshipFields[$column] = Str::plural(str_replace('_id', '', $column));
                $this->relationshipFields[$column] = $fieldDefinitions[$column]['relationship']['model'];
            }
        }
        
        Log::info('DataImport constructed with:', [
            'model' => $model,
            'columns' => $columns,
            'validationRules' => $validationRules
        ]);
    }

    /**
     * Convert the Excel data to model
     */
    public function model(array $row)
    {
        Log::info("Processing row {$this->currentRowNumber}:", $row);
        
        // Skip rows that don't have the required column structure
        if (!$this->isValidDataRow($row)) {
            Log::info("Skipping row {$this->currentRowNumber} - not a valid data row");
            $this->currentRowNumber++;
            return null;
        }
        
        // Clean the row data
        $cleanedRow = $this->cleanRowData($row);
        
        // Validate the row data
        $validator = Validator::make($cleanedRow, $this->getRowValidationRules(), $this->getCustomMessages());
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$this->currentRowNumber}: {$error}";
            }
            Log::warning("Validation failed for row {$this->currentRowNumber}:", $validator->errors()->toArray());
            $this->currentRowNumber++;
            return null; // Skip this row
        }
        
        // Process relationship fields - convert names to IDs
        $processedRow = $this->processRelationshipFields($cleanedRow);
        
        // Process date fields
        $processedRow = $this->processDateFields($processedRow);
        
        Log::info("Processed row {$this->currentRowNumber} data:", $processedRow);
        
        $this->currentRowNumber++;
        return new $this->model($processedRow);
    }
    
    /**
     * Check if this is a valid data row (not from hidden sheet)
     */
    protected function isValidDataRow(array $row): bool
    {
        // Check if row has at least one of our expected columns
        foreach ($this->columns as $column) {
            if (isset($row[$column]) && $row[$column] !== null) {
                return true;
            }
        }
        
        // Check if it's an empty row (all null values)
        $hasAnyValue = false;
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                $hasAnyValue = true;
                break;
            }
        }
        
        // If it has values but none match our columns, it's probably from another sheet
        if ($hasAnyValue) {
            Log::warning("Row has values but no matching columns:", $row);
            return false;
        }
        
        // Empty row is still valid (will be skipped by SkipsEmptyRows)
        return true;
    }
    
    /**
     * Clean row data
     */
    protected function cleanRowData(array $row): array
    {
        $cleaned = [];
        
        foreach ($this->columns as $column) {
            $value = $row[$column] ?? null;
            
            // Handle special characters for phone field
            if ($column === 'phone' && ($value === '—' || $value === '–' || $value === '-')) {
                $value = null;
            }
            
            // Convert empty strings to null
            if (is_string($value) && trim($value) === '') {
                $value = null;
            }
            
            $cleaned[$column] = $value;
        }
        
        return $cleaned;
    }
    
    /**
     * Get validation rules for a single row
     */
    protected function getRowValidationRules(): array
    {
        $rules = $this->validationRules;
        
        // Adjust rules for relationship fields
        foreach ($this->relationshipFields as $field => $modelName) {
            $tableName = app($modelName)->getTable();
            if (isset($rules[$field])) {
                // Remove integer/numeric validation for relationship fields
                if (is_string($rules[$field])) {
                    $rules[$field] = preg_replace('/\b(integer|numeric)\b\|?|\|?\b(integer|numeric)\b/', '', $rules[$field]);
                    $rules[$field] = trim($rules[$field], '|');
                    
                    // Add exists validation for the name field
                    if (!str_contains($rules[$field], 'exists:')) {
                        $rules[$field] .= (empty($rules[$field]) ? '' : '|') . "exists:{$tableName},name";
                    }
                } elseif (is_array($rules[$field])) {
                    $rules[$field] = array_diff($rules[$field], ['integer', 'numeric']);
                    $rules[$field][] = "exists:{$tableName},name";
                }
            }
        }
        
        return $rules;
    }
    
    /**
     * Get custom validation messages
     */
    protected function getCustomMessages(): array
    {
        $messages = [];
        
        foreach ($this->relationshipFields as $field => $modelName) {
            $messages["{$field}.exists"] = "The selected " . str_replace('_', ' ', $field) . " does not exist. Please use a value from the dropdown.";
        }
        
        return $messages;
    }
    
    /**
     * Process relationship fields - convert names to IDs
     */
    protected function processRelationshipFields(array $row): array
    {
        foreach ($this->relationshipFields as $field => $modelName) {
            $tableName = app($modelName)->getTable();
            if (!empty($row[$field]) && !is_numeric($row[$field])) {
                try {
                    $record = DB::table($tableName)
                        ->where('name', $row[$field])
                        ->first();
                    
                    if ($record) {
                        $row[$field] = $record->id;
                        Log::info("Converted {$field} '{$row[$field]}' to ID: {$record->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error looking up {$field}: " . $e->getMessage());
                }
            }
        }
        
        return $row;
    }
    
    /**
     * Process date fields
     */
    protected function processDateFields(array $row): array
    {
        $dateFields = ['hire_date', 'date_of_birth'];
        
        foreach ($dateFields as $field) {
            if (!empty($row[$field])) {
                try {
                    if (is_numeric($row[$field])) {
                        // Excel serial date
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$field]);
                        $row[$field] = $date->format('Y-m-d');
                    } elseif (is_string($row[$field])) {
                        // Try to parse the date
                        $timestamp = strtotime($row[$field]);
                        if ($timestamp !== false) {
                            $row[$field] = date('Y-m-d', $timestamp);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to parse date for {$field}: " . $row[$field]);
                }
            }
        }
        
        return $row;
    }

    /**
     * Handle the import process - IMPORTANT: Specify to read only first sheet
     */


public function import($path, $controller)
{
    $feedbackMessage = "";
    $success = false;

    try {
        Log::info('Starting import process for file: ' . $path);
        
        // 1. File validation (Check if file was uploaded correctly)
        $controller->validate(['file' => 'required|mimes:xls,xlsx,csv']);
        
        // 2. Reset errors and row counter before each import
        $this->errors = [];
        $this->currentRowNumber = 2;
        
        // 3. Execute Import 
        Excel::import($this, $path);
        
        // 4. Check if there were validation errors during import
        if (!empty($this->errors)) {
            $errorCount = count($this->errors);
            $displayErrors = array_slice($this->errors, 0, 5);
            $feedbackMessage = "Found {$errorCount} error(s) during import:\n" . implode("\n", $displayErrors);
            
            if ($errorCount > 5) {
                $feedbackMessage .= "\n... and " . ($errorCount - 5) . " more error(s)";
            }
            
            $success = false;
            Log::warning('Import completed with errors:', $this->errors);
        } else {
            $feedbackMessage = "Data Imported Successfully!";
            $success = true;
            Log::info('Import completed successfully');
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handles standard Laravel validation (file upload issues)
        Log::error('File validation exception: ', ['errors' => $e->errors()]);
        $errors = $e->validator->errors()->all();
        $feedbackMessage = "File Error: " . implode(', ', $errors);
        $success = false;

    } catch (ExcelValidationException $e) {
        // This won't be triggered since we're not using WithValidation interface
        $failures = $e->failures();
        $this->errors = [];

        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()} ({$failure->attribute()}): " . implode(', ', $failure->errors());
        }

        $errorCount = count($this->errors);
        $displayErrors = array_slice($this->errors, 0, 5);
        $feedbackMessage = "Found {$errorCount} error(s) during import:\n" . implode("\n", $displayErrors);
        
        if ($errorCount > 5) {
            $feedbackMessage .= "\n... and " . ($errorCount - 5) . " more error(s)";
        }
        $success = false;

    } catch (\Exception $e) {
        Log::error('Import error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        $feedbackMessage = "Error: " . $e->getMessage();
        $success = false;
    }

    $icon = $success ? 'success' : 'error';
    $controller->dispatch("swal:$icon", [
        'title' => $success ? 'Success!' : 'Error!',
        'text' => $feedbackMessage,
        'icon' => $icon,
    ]);
    
    return $success;
}

    
    /**
     * Get errors for testing/debugging
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}