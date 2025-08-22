<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/v3/admin')
                ->group(base_path('routes/api/admin_v3.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/v5_web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            [$maxAttempts, $decayMinutes] = array_map('intval', explode(',', config('rate_limits.api')));

            $keyParts = [$request->ip()];
            if ($user = $request->user()) {
                $keyParts[] = $user->id;
            }

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by(implode('|', $keyParts));
        });

        RateLimiter::for('auth', function (Request $request) {
            [$maxAttempts, $decayMinutes] = array_map('intval', explode(',', config('rate_limits.auth')));

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($request->ip());
        });

        RateLimiter::for('logging', function (Request $request) {
            [$maxAttempts, $decayMinutes] = array_map('intval', explode(',', config('rate_limits.logging')));

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($request->ip());
        });

        RateLimiter::for('context', fn (Request $r) => Limit::perMinute(30)->by(optional($r->user())->id ?: $r->ip()));
    }
}
