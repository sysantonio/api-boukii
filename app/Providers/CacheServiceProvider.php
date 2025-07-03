<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Configurar tags de cache para diferentes tipos de datos
        Cache::macro('financeDashboard', function ($schoolId, $level = 'fast') {
            return Cache::tags(['finance', "school:{$schoolId}", "level:{$level}"]);
        });
    }
}
