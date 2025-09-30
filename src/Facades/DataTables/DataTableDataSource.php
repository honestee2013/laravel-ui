<?php 

namespace QuickerFaster\LaravelUI\Facades\DataTables;

use Illuminate\Support\Facades\Facade;



class DataTableDataSource extends Facade {

    protected static function getFacadeAccessor() {
        return "DataTableDataSourceService";
    }

}

