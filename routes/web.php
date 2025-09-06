<?php


use Illuminate\Support\Facades\Route;
use QuickerFaster\LaravelUI\Components\Livewire\Dashboard;
use QuickerFaster\LaravelUI\Components\Livewire\Settings;



//Route::middleware(['auth'])->group(function () {

    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/home', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/settings', Settings::class)->name('settings');



    Route::get('/employees', function () {
        return view('employees.index');
    })->name('employees.index');

    Route::get('/attendance', function () {
        return view('attendance.index');
    })->name('attendance.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/help', function () {
        return view('help');
    })->name('help');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

//});



