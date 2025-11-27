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




//foreach (config('tenancy.central_domains') as $domain) {
//  Route::domain($domain)->group(function () {


/*
|--------------------------------------------------------------------------
| Central Domain Routes (quickerfaster.com)
|--------------------------------------------------------------------------
|
| These routes are for the marketing site and central SaaS hub.
| They are exempt from tenancy and live under /suites for SaaS functionality.
|
*/

// Marketing site (no auth, no tenancy)
Route::get('/', function () {
    return view('home');
})->name('marketing.home');




foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {


Route::middleware(['web'])->group(function () {
    // Register Livewire routes using the proper method
    $baseUrl = config('app.url');

    Route::post("{$baseUrl}/livewire/update", [HandleRequests::class, 'handleUpdate'])
        ->name('central.livewire.update')
        ->middleware('web');

    Route::get("{$baseUrl}/livewire/livewire.js", [FrontendAssets::class, 'returnJavaScriptAsFile'])
        ->name('central.livewire.script')
        ->middleware('web');







    // Central SaaS app (registration, auth, billing, etc.)
    //Route::prefix('suites')->middleware(['web'])->group(function () {

        // Authentication (guest-only)
        Route::middleware('guest')->group(function () {
            Route::get('/login', [SessionsController::class, 'create'])->name('central.login');
            Route::post('/session', [SessionsController::class, 'store']);
            Route::get('/register', [RegisterController::class, 'create'])->name('central.register.form');
            Route::post('/register', [RegisterController::class, 'store'])->name('central.register');
            Route::get('/forgot-password', [ResetController::class, 'create'])->name('central.password.request');
            Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
            Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('central.password.reset');
            Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('central.password.update');
        });

        // Registration flow (Livewire)
        Route::get('/client-register', SignupForm::class)->name('central.client.register');
        Route::get('/configure', QuickConfiguration::class)->name('central.quick.configure');

        // Email verification
        Route::get('/verify', [VerificationController::class, 'show'])->name('central.verification.notice');
        Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('central.verification.verify');

        // Authenticated routes
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [SessionsController::class, 'destroy'])->name('central.logout');
            // Add billing, account settings, etc. here later
            // Route::get('/billing', [BillingController::class, 'index'])->name('central.billing');
        });
    //});









    /*Route::group(['middleware' => 'auth'], function () {

        /*Route::get('/', [HomeController::class, 'home']);
        Route::get('dashboard', function () {
            return view('dashboard');
        })->name('dashboard');* /

        Route::get('billing', function () {
            return view('billing');
        })->name('billing');

        Route::get('profile', function () {
            //return view('profile');
            // Center the content
            echo "<div style='text-align: center; margin-top: 50px;'>";
            echo "<h1 class = 'text-primary'> Coming Soon! </h1>";
            echo "<a href='/dashboard' class = 'text-info text-decoration-underline'>Go to Dashboard</a>";
            echo "</div>";
        })->name('profile');

        Route::get('rtl', function () {
            return view('rtl');
        })->name('rtl');

        Route::get('user-management', function () {
            return view('laravel-examples/user-management');
        })->name('user-management');

        Route::get('tables', function () {
            return view('tables');
        })->name('tables');

        Route::get('virtual-reality', function () {
            return view('virtual-reality');
        })->name('virtual-reality');

        Route::get('static-sign-in', function () {
            return view('static-sign-in');
        })->name('sign-in');

        Route::get('static-sign-up', function () {
            return view('static-sign-up');
        })->name('sign-up');

        Route::get('/logout', [SessionsController::class, 'destroy'])->name('central.logout');

        Route::get('/user-profile', [InfoUserController::class, 'create']);
        Route::post('/user-profile', [InfoUserController::class, 'store']);
        Route::get('/login', function () {
            return view('dashboard');
        })->name('sign-up');
    });*/



    /*Route::get('/login', function () {
        return view('session/login-session');
    })->name('login');*/





});




});
}

















