<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Report\Http\Controllers\ReportController;





Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('{modelName}/{id}', [ReportController::class, 'show'])->name('show');
    Route::get('{modelName}/{id}/export/{format}', [ReportController::class, 'export'])->name('export');
});



