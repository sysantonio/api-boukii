<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\StatisticsController;
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
        ->except(['create', 'edit'])->names([
            'index' => 'api.admin.courses.index',
            'store' => 'api.admin.courses.store',
            'show' => 'api.admin.courses.show',
            'update' => 'api.admin.courses.update',
            'destroy' => 'api.admin.courses.destroy',
        ]);

    Route::get('/courses/{id}/export/{lang}', [App\Http\Controllers\Admin\CourseController::class, 'exportDetails']);

    Route::get('/courses/{id}/sells/', [App\Http\Controllers\Admin\CourseController::class, 'getSellStats']);

    Route::get('getPlanner', [\App\Http\Controllers\Admin\PlannerController::class, 'getPlanner'])
        ->name('api.admin.planner');

    Route::get('clients/mains', [\App\Http\Controllers\Admin\ClientsController::class, 'getMains'])
        ->name('api.admin.clients.main');

    Route::resource('clients', App\Http\Controllers\Admin\ClientsController::class)
        ->except(['create', 'edit'])->names([
            'index' => 'api.admin.clients.index',
            'store' => 'api.admin.clients.store',
            'show' => 'api.admin.clients.show',
            'update' => 'api.admin.clients.update',
            'destroy' => 'api.admin.clients.destroy',
        ]);

    Route::get('clients/{id}/utilizers', [\App\Http\Controllers\Admin\ClientsController::class, 'getUtilizers'])
        ->name('api.admin.clients.utilizers');

    Route::get('clients/course/{id}', [\App\Http\Controllers\Admin\ClientsController::class, 'getClientsByCourse'])
        ->name('api.admin.clients.courses.find');

    Route::post('monitors/available', [\App\Http\Controllers\Admin\MonitorController::class, 'getMonitorsAvailable'])
        ->name('api.admin.monitors.available');

    Route::post('monitors/available/{id}', [\App\Http\Controllers\Admin\MonitorController::class,
        'checkIfMonitorIsAvailable'])
        ->name('api.admin.monitor.availability');

    Route::post('planner/monitors/transfer', [\App\Http\Controllers\Admin\PlannerController::class, 'transferMonitor'])
        ->name('api.admin.planner.transfer');

    /** Booking **/
    Route::post('bookings',
        [\App\Http\Controllers\Admin\BookingController::class, 'store'])
        ->name('api.admin.bookings.store');

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

    Route::post('bookings/update',
        [\App\Http\Controllers\Admin\BookingController::class, 'update'])
        ->name('api.admin.bookings.update');

    Route::post('bookings/update/{id}/payment',
        [\App\Http\Controllers\Admin\BookingController::class, 'updatePayment'])
        ->name('api.admin.bookings.updatePayment');

    /** Statistics */
/*    Route::get('statistics/bookings', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getTotalAvailablePlacesByCourseType'])
        ->name('api.admin.stats.bookings');

    Route::get('statistics/bookings/sells', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getCoursesWithDetails'])
        ->name('api.admin.stats.bookings.sells');

    Route::get('statistics/bookings/dates', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getBookingUsersByDateRange'])
        ->name('api.admin.stats.bookingsDates');

    Route::get('statistics/bookings/sports', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getBookingUsersBySport'])
        ->name('api.admin.stats.bookingsSports');

    Route::get('statistics/total', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getTotalPrice'])
        ->name('api.admin.stats.bookingsSports');


    Route::get('statistics/monitors/total', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getTotalMonitorPrice'])
        ->name('api.admin.stats.bookingsSports');

  Route::get('statistics/bookings/monitors', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getMonitorsBookings'])
        ->name('api.admin.stats.monitors');

    Route::get('statistics/bookings/monitors/active', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getActiveMonitors'])
        ->name('api.admin.stats.monitors.active');

    Route::get('statistics/bookings/monitors/hours', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getTotalWorkedHours'])
        ->name('api.admin.stats.monitors.hours');

    Route::get('statistics/bookings/monitors/sports', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getTotalWorkedHoursBySport'])
        ->name('api.admin.stats.monitors.sports');

  Route::get('statistics/bookings/monitors/{id}', [\App\Http\Controllers\Admin\StatisticsControllerOld::class, 'getMonitorDailyBookings'])
        ->name('api.admin.stats.monitors.id');*/


    /** Mailing */
    Route::post('mails/send', [\App\Http\Controllers\Admin\MailController::class, 'sendMail']);

    /** Weather */
    Route::get('weather', [\App\Http\Controllers\Admin\HomeController::class, 'get12HourlyForecastByStation'])
        ->name('api.admin.weather');

    Route::get('weather/week', [\App\Http\Controllers\Admin\HomeController::class, 'get5DaysForecastByStation'])
        ->name('api.admin.weatherweek');

    // Main Analytics Endpoints
    Route::get('/analytics/summary', [AnalyticsController::class, 'getSummary']);
    Route::get('/analytics/courses', [AnalyticsController::class, 'getCourseAnalytics']);
    Route::get('/analytics/revenue', [AnalyticsController::class, 'getRevenueAnalytics']);
    Route::get('/analytics/payment-details', [AnalyticsController::class, 'getPaymentDetails']);

    // Financial Dashboard
    Route::get('/analytics/financial-dashboard', [AnalyticsController::class, 'getFinancialDashboard']);

    // Pending Payments Management
    Route::get('/analytics/pending-payments', [AnalyticsController::class, 'getPendingPayments']);

    // Performance Comparison
    Route::get('/analytics/performance-comparison', [AnalyticsController::class, 'getPerformanceComparison']);

    // Legacy Statistics Endpoints (for backward compatibility)
    Route::get('/statistics/bookings/monitors/active', [StatisticsController::class, 'getActiveMonitors']);
    Route::get('/statistics/bookings/monitors', [StatisticsController::class, 'getMonitorsBookings']);
    Route::get('/statistics/bookings/monitors/hours', [StatisticsController::class, 'getTotalWorkedHours']);
    Route::get('/statistics/bookings/monitors/sports', [StatisticsController::class, 'getTotalWorkedHoursBySport']);
    Route::get('/statistics/bookings/monitors/total', [StatisticsController::class, 'getTotalMonitorPrice']);
    Route::get('/statistics/bookings/dates', [StatisticsController::class, 'getBookingUsersByDateRange']);
    Route::get('/statistics/bookings/sports', [StatisticsController::class, 'getBookingUsersBySport']);
    Route::get('/statistics/bookings/sells', [StatisticsController::class, 'getCoursesWithDetails']);
    Route::get('/statistics/bookings', [StatisticsController::class, 'getTotalAvailablePlacesByCourseType']);
    Route::get('/statistics/total', [StatisticsController::class, 'getTotalPrice']);

    // Monitor specific analytics
    Route::get('/analytics/monitors/{monitorId}/daily', [AnalyticsController::class, 'getMonitorDailyAnalytics']);
    Route::get('/analytics/monitors/{monitorId}/performance', [AnalyticsController::class, 'getMonitorPerformance']);

    // Real-time analytics
    Route::get('/analytics/realtime/dashboard', [AnalyticsController::class, 'getRealtimeDashboard']);
    Route::get('/analytics/realtime/bookings', [AnalyticsController::class, 'getRealtimeBookings']);

    // Export endpoints
    Route::post('/analytics/export/csv', [AnalyticsController::class, 'exportToCSV']);
    Route::post('/analytics/export/pdf', [AnalyticsController::class, 'exportToPDF']);
    Route::post('/analytics/export/excel', [AnalyticsController::class, 'exportToExcel']);

    // Custom analytics queries
    Route::post('/analytics/custom-query', [AnalyticsController::class, 'executeCustomQuery']);

    // Analytics preferences/settings
    Route::get('/analytics/preferences', [AnalyticsController::class, 'getAnalyticsPreferences']);
    Route::post('/analytics/preferences', [AnalyticsController::class, 'saveAnalyticsPreferences']);

});
