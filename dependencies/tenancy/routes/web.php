<?php

use Illuminate\Support\Facades\Route;
use QuickerFaster\LaravelUI\Http\Controllers\Central\Auth\VerificationController;
use QuickerFaster\LaravelUI\Http\Livewire\Auth\SignupForm;
use QuickerFaster\LaravelUI\Http\Livewire\Auth\QuickConfiguration;


use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;





/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;


// ========== LIVEWIRE ROUTES (CENTRAL) ==========
Route::middleware(['web'])->group(function () {
    // Livewire assets and update endpoint
    Route::post('/livewire/update', [HandleRequests::class, 'handleUpdate'])
        ->name('livewire.update');

    Route::get('/livewire/livewire.js', [FrontendAssets::class, 'returnJavaScriptAsFile'])
        ->name('livewire.script');




    // Step 1: Registration
    Route::get('/client-register', SignupForm::class)->name('central.client.register');

    // Email Verification
    Route::get('/verify', [VerificationController::class, 'show'])->name('central.verification.notice');
    Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('central.verification.verify');

    // Step 2: Quick Configuration (NO AUTH REQUIRED - uses session)
    Route::get('/configure', QuickConfiguration::class)->name('central.quick.configure');


    Route::group(['middleware' => 'auth'], function () {
        Route::get('/logout', [SessionsController::class, 'destroy'])->name('central.central.logout');
    });


    Route::get('/', function () {
        return view('home');
    });





    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [RegisterController::class, 'create']);
        Route::post('/register', [RegisterController::class, 'store']);
        Route::get('/login', [SessionsController::class, 'create']);
        Route::post('/session', [SessionsController::class, 'store']);
        Route::get('/login/forgot-password', [ResetController::class, 'create']);
        Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
        Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('central.password.reset');
        Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('central.password.update');

    });



});



foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::middleware(['web'])->group(function () {


            Route::post('/livewire/update', [HandleRequests::class, 'handleUpdate'])
                ->name('livewire.update');

            Route::get('/livewire/livewire.js', [FrontendAssets::class, 'returnJavaScriptAsFile'])
                ->name('livewire.script');



        });



    });
}
















