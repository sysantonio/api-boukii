<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\School;

class CacheControl
{

    /**
     * Check if request contains the "slug" of an active School, or goodbye.
     *
     * @param Request $request
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Solo para endpoints de analytics
        if ($request->is('api/admin/analytics/*') || $request->is('api/admin/finance/*')) {
            $optimizationLevel = $request->get('optimization_level', 'balanced');

            $cacheTimes = [
                'fast' => 300,      // 5 minutos
                'balanced' => 600,  // 10 minutos
                'detailed' => 1800  // 30 minutos
            ];

            $cacheTime = $cacheTimes[$optimizationLevel] ?? 300;

            $response->headers->set('Cache-Control', "public, max-age={$cacheTime}");
            $response->headers->set('X-Cache-Level', $optimizationLevel);
        }

        return $response;
    }

}
