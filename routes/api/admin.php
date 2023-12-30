<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;


// Public
Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('api.admin.login');
/*
Route::delete('logout', [\App\Http\Controllers\Auth\LogoutController::class, 'destroy'])->name('api.admin.logout');
Route::post('auth/recover-password', [\App\Http\Controllers\Auth\AuthController::class, 'recoverPassword'])->name('api.admin.recoverPassword');
Route::post('auth/reset-password/{token}', [\App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);*/


// Private
Route::middleware(['auth:sanctum', 'ability:admin:all'])->group(function() {

    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class)
        ->except(['create', 'edit']);

    Route::get('getPlanner', [\App\Http\Controllers\Admin\PlannerController::class, 'getPlanner'])
        ->name('api.admin.planner');

    Route::get('clients/mains', [\App\Http\Controllers\Admin\ClientsController::class, 'getMains']);

    Route::resource('clients', App\Http\Controllers\Admin\ClientsController::class)
        ->except(['create', 'edit']);

    Route::get('clients/{id}/utilizers', [\App\Http\Controllers\Admin\ClientsController::class, 'getUtilizers']);

    Route::post('monitors/available', [\App\Http\Controllers\Admin\MonitorController::class, 'getMonitorsAvailable'])
        ->name('api.admin.monitors.available');

    Route::post('planner/monitors/transfer', [\App\Http\Controllers\Admin\PlannerController::class, 'transferMonitor'])
        ->name('api.admin.planner.transfer');

    /** Booking **/
    Route::post('bookings/checkbooking',
        [\App\Http\Controllers\Admin\BookingController::class, 'checkClientBookingOverlap'])
        ->name('api.admin.bookings.bookingoverlap');

    /** Booking **/
    Route::post('bookings/payments/{id}',
        [\App\Http\Controllers\Admin\BookingController::class, 'payBooking'])
        ->name('api.admin.bookings.pay');

    /** Mailing */
    Route::post('mails/send', [\App\Http\Controllers\Admin\MailController::class, 'sendMail']);

});
