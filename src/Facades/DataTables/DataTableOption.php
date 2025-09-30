<?php



namespace QuickerFaster\LaravelUI\Facades\DataTables;

use Illuminate\Support\Facades\Facade;





class DataTableOption extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DataTableOptionService';
    }
}

