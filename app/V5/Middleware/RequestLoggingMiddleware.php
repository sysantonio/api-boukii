<?php

namespace App\V5\Middleware;

use App\V5\Logging\V5Logger;
use Closure;
use Illuminate\Http\Request;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Initialize correlation tracking for this request
        $correlationId = $request->header('X-Correlation-ID')
            ?? V5Logger::initializeCorrelation();

        // Set persistent context for the request
        V5Logger::setPersistentContext([
            'request_start_time' => $startTime,
            'request_memory_start' => memory_get_usage(true),
        ]);

        // Add correlation ID to response headers
        if ($request->header('X-Correlation-ID')) {
            V5Logger::initializeCorrelation($request->header('X-Correlation-ID'));
        }

        // Log the incoming request with enhanced context
        V5Logger::logApiRequest($request, [
            'correlation_id' => $correlationId,
            'request_size_bytes' => $request->header('Content-Length'),
        ]);

        try {
            $response = $next($request);

            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            $memoryUsage = memory_get_usage(true) - $request->get('request_memory_start', 0);

            // Add correlation ID to response headers
            if (method_exists($response, 'header')) {
                $response->header('X-Correlation-ID', $correlationId);
            }

            // Log the response with performance metrics
            V5Logger::logApiResponse($request, $response, $responseTime, [
                'memory_delta_mb' => round($memoryUsage / 1024 / 1024, 2),
                'queries_count' => $this->getQueryCount(),
            ]);

            // Log performance metrics based on configurable thresholds
            $this->logPerformanceMetrics($request, $responseTime, $memoryUsage);

            return $response;

        } catch (\Throwable $exception) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            // Log the exception with correlation context
            V5Logger::logSystemError($exception, [
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'response_time_ms' => round($responseTime * 1000, 2),
                'correlation_id' => $correlationId,
            ]);

            throw $exception;
        } finally {
            // End correlation tracking and cleanup
            V5Logger::endRequest([
                'total_response_time_ms' => isset($responseTime) ? round($responseTime * 1000, 2) : null,
                'final_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        }
    }

    /**
     * Log performance metrics based on configurable thresholds
     */
    private function logPerformanceMetrics(Request $request, float $responseTime, int $memoryUsage): void
    {
        $config = config('v5_logging.monitoring', []);
        $thresholds = $config['performance_thresholds'] ?? [];

        $slowThreshold = ($thresholds['slow_request_ms'] ?? 1000) / 1000;
        $verySlowThreshold = ($thresholds['very_slow_request_ms'] ?? 2000) / 1000;
        $criticalThreshold = ($thresholds['critical_request_ms'] ?? 5000) / 1000;

        $memoryThresholds = $config['memory_thresholds'] ?? [];
        $memoryWarning = ($memoryThresholds['warning_mb'] ?? 128) * 1024 * 1024;
        $memoryCritical = ($memoryThresholds['critical_mb'] ?? 256) * 1024 * 1024;

        $context = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
        ];

        // Log slow requests
        if ($responseTime >= $criticalThreshold) {
            V5Logger::logPerformance('critical_slow_request', $responseTime, array_merge($context, [
                'severity' => 'critical',
                'threshold_exceeded' => 'critical_request_threshold',
            ]));
        } elseif ($responseTime >= $verySlowThreshold) {
            V5Logger::logPerformance('very_slow_request', $responseTime, array_merge($context, [
                'severity' => 'high',
                'threshold_exceeded' => 'very_slow_request_threshold',
            ]));
        } elseif ($responseTime >= $slowThreshold) {
            V5Logger::logPerformance('slow_request', $responseTime, array_merge($context, [
                'severity' => 'medium',
                'threshold_exceeded' => 'slow_request_threshold',
            ]));
        }

        // Log high memory usage
        if ($memoryUsage >= $memoryCritical) {
            V5Logger::logPerformance('critical_memory_usage', $responseTime, array_merge($context, [
                'alert_type' => 'CRITICAL_MEMORY_USAGE',
                'memory_threshold_exceeded' => 'critical',
            ]));
        } elseif ($memoryUsage >= $memoryWarning) {
            V5Logger::logPerformance('high_memory_usage', $responseTime, array_merge($context, [
                'alert_type' => 'HIGH_MEMORY_USAGE',
                'memory_threshold_exceeded' => 'warning',
            ]));
        }
    }

    /**
     * Get the current query count (if DB query logging is enabled)
     */
    private function getQueryCount(): int
    {
        try {
            return count(\DB::getQueryLog());
        } catch (\Exception $e) {
            return 0;
        }
    }
}
