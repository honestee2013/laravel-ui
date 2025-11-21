<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Http\Controllers\AuthController;

use App\Modules\Hr\Http\Controllers\ClockEventController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;




Route::post('/hr/test', function () {
    //return $controller->store($request);
    dd('api route test');
});


Route::middleware([
    'auth:sanctum',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

Route::post('hr/attendance/store', [ClockEventController::class, 'store'])
    ->name('hr.attendance.store');

});











Route::group([
    'prefix' => 'hr',
    'middleware' => 'auth:sanctum',
], function () {

    //Route::post('attendance/store', [ClockEventController::class, 'store'])
        //->name('hr.attendance.store');
    //Route::post('attendance/batch-store', [\App\Modules\Hr\Http\Controllers\AttendanceController::class, 'batchStore']);
    //Route::get('user/sync', [\App\Modules\Hr\Http\Controllers\UserSyncController::class, 'syncUser']);


    //Route::middleware('auth:sanctum')->get('/test', function (Request $request) {
        //return response()->json(['user' => $request->user()]);
    //});

});





