<?php

use App\Http\Controllers\Admin\V3\DashboardController;
use Illuminate\Support\Facades\Route;


Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
    Route::get('/summary', 'summary');
    Route::get('/courses', 'courseStats');
    Route::get('/weather', 'weather');
    Route::get('/sales', 'sales');
    Route::get('/reservations', 'reservations');
});

