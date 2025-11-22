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


            Route::get('/login', function () {
                return view('session/login-session');
            })->name('central.login');




            // Step 1: Registration
            Route::get('/client-register', SignupForm::class)->name('register');

            // Email Verification
            Route::get('/verify', [VerificationController::class, 'show'])->name('verification.notice');
            Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('verification.verify');

            // Step 2: Quick Configuration (NO AUTH REQUIRED - uses session)
            Route::get('/configure', QuickConfiguration::class)->name('quick.configure');

            // Step 3: Dashboard (REQUIRES AUTHENTICATION)
            /*Route::middleware(['auth', 'tenant'])->prefix('/app')->group(function () {
                Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
                // ... other tenant routes
            });*/





            Route::group(['middleware' => 'auth'], function () {
                Route::get('/logout', [SessionsController::class, 'destroy'])->name('central.logout');
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

            Route::get('/', function () {
                return view('home');
            });



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

















