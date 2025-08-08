<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\V5\Logging\V5Logger;
use App\V5\Logging\CorrelationTracker;

class V5ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register V5 singletons
        $this->app->singleton('v5.logger', function ($app) {
            return new V5Logger();
        });

        $this->app->singleton('v5.correlation', function ($app) {
            return new CorrelationTracker();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadV5Routes();
        $this->loadV5Views();
        $this->loadV5Translations();
        $this->registerMiddleware();
        $this->setupLoggingChannels();
    }

    /**
     * Load V5 routes
     */
    private function loadV5Routes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('v5')
            ->group(function () {
                Route::get('/logs', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'dashboard'])
                    ->name('v5.logs.dashboard');

                Route::get('/logs/search', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'search'])
                    ->name('v5.logs.search');

                Route::get('/logs/payments', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'payments'])
                    ->name('v5.logs.payments');

                Route::get('/logs/correlation/{id}', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'correlationDetail'])
                    ->name('v5.logs.correlation');

                Route::get('/logs/system-errors', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'systemErrors'])
                    ->name('v5.logs.system-errors');

                Route::get('/logs/performance', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'performance'])
                    ->name('v5.logs.performance');

                Route::post('/logs/alerts/{id}/resolve', [\App\Http\Controllers\V5\LogDashboardWebController::class, 'resolveAlert'])
                    ->name('v5.logs.resolve-alert');
            });
    }

    /**
     * Load V5 views
     */
    private function loadV5Views(): void
    {
        $this->loadViewsFrom(resource_path('views/v5'), 'v5');
    }

    /**
     * Load V5 translations
     */
    private function loadV5Translations(): void
    {
        $this->loadTranslationsFrom(resource_path('lang'), 'v5');
    }

    /**
     * Register middleware
     */
    private function registerMiddleware(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        // Global middleware for correlation tracking
       // $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\V5\CorrelationMiddleware::class);
      //  $this->app['router']->pushMiddlewareToGroup('api', \App\Http\Middleware\V5\CorrelationMiddleware::class);

        // Performance tracking middleware for V5 routes
        //$this->app['router']->aliasMiddleware('v5.performance', \App\Http\Middleware\V5\PerformanceMiddleware::class);
       // $this->app['router']->aliasMiddleware('v5.logging', \App\Http\Middleware\V5\LoggingMiddleware::class);
    }

    /**
     * Setup logging channels for V5
     */
    private function setupLoggingChannels(): void
    {
        // Set default log channel for V5 components
        if (config('app.env') === 'production') {
            config(['logging.default' => 'v5_enterprise']);
        }
    }
}
