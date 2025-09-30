<?php

namespace QuickerFaster\LaravelUI\Services\Imports;


use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Database\UniqueConstraintViolationException;


class DataImport implements ToModel, WithHeadingRow
{
    protected $model;
    protected $columns;

    public function __construct($model, $columns)
    {
        $this->model = $model;
        $this->columns = $columns;
    }

    public function model(array $row)
    {
        // Dynamically map fields from the import file based on the columns array
        $data = [];
        foreach ($this->columns as $column) {
            $data[$column] = $row[$column] ?? null;
        }

        return new $this->model($data);
    }



    public function import($path, $controller)
    {


        $controller->dispatch('swal:error', [
            'title' => 'Success!',
            'text' => 'Record deleted successfully.',
            'icon' => 'success',
        ]);


        $feedbackMessage = "";
        $success = false;

        try {

            $controller->validate([
                'file' => 'required|mimes:xls,xlsx,csv'
            ]);

            Excel::import(new DataImport($this->model, $this->columns), $path);
            $feedbackMessage = "Data Imported Successfully!";
            $success = true;
            //session()->flash('success_message', 'Data Imported Successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $feedback = $e->validator->errors()->all();
            $feedbackMessage = implode(', ', $feedback); // Just an example
        } catch (ValidationException $e) {
            // Handle Excel validation errors
            //session()->flash('error', 'There was a validation error in the imported file.');
            $feedbackMessage = "There was a validation error in the imported file.";
        } catch (UniqueConstraintViolationException $e) {
            //dd( $e->getMessage(), "duplicate");

            //session()->flash('error', 'There was a database error during the import. Please check... It seems like some rows where already imported from the file to the database!');
            $feedbackMessage = "It seems like some rows where already imported from the file to the database!";

        } catch (QueryException $e) {
            //dd(vars: $e->getMessage());
            // Handle database-related errors (e.g., incorrect data types)
            //session()->flash('error', 'There was a database error during the import.  Please check... It seems like the columns in the table do not tally with the ones in the database!');
            $feedbackMessage = " Make sure that the columns in the spreadsheet match the ones in this table!";

        } catch (\Exception $e) {
            //dd(vars: $e->getMessage());

            // Catch any other general exceptions
            //session()->flash('error', 'An error occurred while importing the data.');
            $feedbackMessage = "An error occurred while importing the data.";

        }

        if ($success == true) {
            $controller->dispatch('swal:success', [
                'title' => 'Success!',
                'text' => 'Record imported successfully.',
                'icon' => 'success',
            ]);
        } else { // Error message
            $controller->dispatch('swal:error', [
                'title' => 'Error!',
                'text' => $feedbackMessage,
                'icon' => 'error',
            ]);
        }


    }







}

