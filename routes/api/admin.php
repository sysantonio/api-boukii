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

    Route::get('clients/course/{id}', [\App\Http\Controllers\Admin\ClientsController::class, 'getClientsByCourse']);

    Route::post('monitors/available', [\App\Http\Controllers\Admin\MonitorController::class, 'getMonitorsAvailable'])
        ->name('api.admin.monitors.available');

    Route::post('monitors/available/{id}', [\App\Http\Controllers\Admin\MonitorController::class,
        'checkIfMonitorIsAvailable'])
        ->name('api.admin.monitor.availability');

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

    Route::post('bookings/mail/{id}',
        [\App\Http\Controllers\Admin\BookingController::class, 'mailBooking'])
        ->name('api.admin.bookings.mail');

    Route::post('bookings/refunds/{id}',
        [\App\Http\Controllers\Admin\BookingController::class, 'refundBooking'])
        ->name('api.admin.bookings.refund');

    Route::post('bookings/cancel',
        [\App\Http\Controllers\Admin\BookingController::class, 'cancelBookings'])
        ->name('api.admin.bookings.cancel');

    /** Statistics */
    Route::get('statistics/bookings/private', [\App\Http\Controllers\Admin\StatisticsController::class, 'getPrivateBookings'])
        ->name('api.admin.stats.private');

    Route::get('statistics/bookings/collective', [\App\Http\Controllers\Admin\StatisticsController::class, 'getCollectiveBookings'])
        ->name('api.admin.stats.collective');

    Route::get('statistics/bookings/activity', [\App\Http\Controllers\Admin\StatisticsController::class, 'getActivityBookings'])
        ->name('api.admin.stats.activity');

  Route::get('statistics/bookings/monitors', [\App\Http\Controllers\Admin\StatisticsController::class, 'getMonitorsBookings'])
        ->name('api.admin.stats.monitors');

    /** Mailing */
    Route::post('mails/send', [\App\Http\Controllers\Admin\MailController::class, 'sendMail']);

    /** Weather */
    Route::get('weather', [\App\Http\Controllers\Admin\HomeController::class, 'get12HourlyForecastByStation'])
        ->name('api.admin.weather');

    Route::get('weather/week', [\App\Http\Controllers\Admin\HomeController::class, 'get5DaysForecastByStation'])
        ->name('api.admin.weatherweek');

});
