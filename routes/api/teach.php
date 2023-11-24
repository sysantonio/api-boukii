<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;

// Routes for Boukii Teach (i.e. Monitor's) app
Route::post('login', [\App\Http\Controllers\Teach\AuthController::class, 'login'])->name('api.teach.login');

// Private
Route::middleware(['auth:sanctum', 'ability:teach:all'])->group(function() {
    Route::get('getAgenda', [\App\Http\Controllers\Teach\HomeController::class, 'getAgenda'])
        ->name('api.teach.home.agenda');

    Route::get('monitor/pastBookings', [\App\Http\Controllers\Teach\MonitorController::class, 'getPastBookings'])
        ->name('api.teach.monitor.pastBokkings');

    Route::get('clients', [\App\Http\Controllers\Teach\ClientsController::class, 'index'])
        ->name('api.teach.clients.index');

    Route::get('clients/{id}/bookings', [\App\Http\Controllers\Teach\ClientsController::class, 'getBookings'])
        ->name('api.teach.clients.bookings');

});
