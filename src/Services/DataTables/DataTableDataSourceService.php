<?php


namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Support\Facades\Cache;

class DataTableDataSourceService
{
   
    // Look for the file inside app/Modules/[module name]/Data/Partials/Fields/
    public function getModuleDataPartialFields($moduleName, $filePath) {
        $fullFilePath =  app_path("/Modules/".ucfirst($moduleName)."/Data/Partials/Fields/$filePath");

        if (file_exists($fullFilePath)){
            return app()->isLocal()? include $fullFilePath : 
                Cache::remenberForEver($filePath, fn() => include $fullFilePath);
        } 

        return [];
    }

    public function getModuleMultipleFilesDataPartialFields($moduleName = "core", ...$filePaths) {
        if (!isset($filePaths))
            return [];

        $result = [];
        foreach ($filePaths as $key => $filePath) {
            $result = array_merge($result, $this->getModuleDataPartialFields($moduleName, $filePath));
        }

        return $result;
    }
}
