<?php

use App\Http\Controllers\V5\LogDashboardWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V5 Web Routes
|--------------------------------------------------------------------------
|
| Web routes for V5 dashboard and admin interfaces
|
*/

Route::prefix('v5')->name('v5.')->middleware(['auth'])->group(function () {
    
    // Log Dashboard Routes
    Route::prefix('logs')->name('logs.')->group(function () {
        
        // Main dashboard
        Route::get('/', [LogDashboardWebController::class, 'index'])
            ->name('dashboard');
        
        // Search and filtering
        Route::get('/search', [LogDashboardWebController::class, 'search'])
            ->name('search');
        
        // Specialized log views
        Route::get('/payments', [LogDashboardWebController::class, 'payments'])
            ->name('payments');
        
        Route::get('/system-errors', [LogDashboardWebController::class, 'systemErrors'])
            ->name('system-errors');
        
        Route::get('/performance', [LogDashboardWebController::class, 'performance'])
            ->name('performance');
        
        Route::get('/realtime-alerts', [LogDashboardWebController::class, 'realtimeAlerts'])
            ->name('realtime-alerts');
        
        Route::get('/statistics', [LogDashboardWebController::class, 'statistics'])
            ->name('statistics');
        
        // Detail views
        Route::get('/correlation/{correlationId}', [LogDashboardWebController::class, 'correlationDetail'])
            ->name('correlation-detail');
        
        Route::get('/log/{logId}', [LogDashboardWebController::class, 'logDetail'])
            ->name('log-detail');
        
        // AJAX endpoints for real-time updates
        Route::get('/ajax/overview', [LogDashboardWebController::class, 'ajaxOverview'])
            ->name('ajax.overview');
        
        Route::get('/ajax/alerts', [LogDashboardWebController::class, 'ajaxAlerts'])
            ->name('ajax.alerts');
        
        // Actions
        Route::post('/alerts/{alertId}/resolve', [LogDashboardWebController::class, 'resolveAlert'])
            ->name('alerts.resolve');
    });
    
});