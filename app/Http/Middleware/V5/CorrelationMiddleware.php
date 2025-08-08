<?php

namespace App\Http\Middleware\V5;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\V5\Logging\CorrelationTracker;
use App\V5\Logging\V5Logger;

class CorrelationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        /*// Generate or extract correlation ID
        $correlationId = $request->header('X-Correlation-ID')
            ?? $request->query('correlation_id')
            ?? V5Logger::generateCorrelationId();

        // Set correlation ID for this request
        V5Logger::setCorrelationId($correlationId);
        CorrelationTracker::startRequest($correlationId, [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ]);

        $response = $next($request);

        // Add correlation ID to response headers
        $response->headers->set('X-Correlation-ID', $correlationId);

        // End request tracking
        CorrelationTracker::endRequest([
            'status_code' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent()),
        ]);

        return $response;*/
    }
}
