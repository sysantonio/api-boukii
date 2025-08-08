<?php

namespace App\V5\Logging;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CorrelationTracker
{
    private const CACHE_PREFIX = 'correlation_';

    private const CACHE_TTL = 3600; // 1 hour

    private static ?string $currentCorrelationId = null;

    private static array $breadcrumbs = [];

    private static array $contextData = [];

    /**
     * Initialize correlation tracking for a request
     */
    public static function initializeForRequest(): string
    {
        if (self::$currentCorrelationId === null) {
            self::$currentCorrelationId = self::generateCorrelationId();
            self::addBreadcrumb('request_started', ['timestamp' => now()->toISOString()]);
        }

        return self::$currentCorrelationId;
    }

    /**
     * Get current correlation ID
     */
    public static function getCurrentCorrelationId(): ?string
    {
        return self::$currentCorrelationId;
    }

    /**
     * Set correlation ID (for continuing existing flows)
     */
    public static function setCorrelationId(string $correlationId): void
    {
        self::$currentCorrelationId = $correlationId;
        self::loadExistingContext($correlationId);
    }

    /**
     * Add breadcrumb to track flow
     */
    public static function addBreadcrumb(string $action, array $data = []): void
    {
        $breadcrumb = [
            'action' => $action,
            'timestamp' => now()->toISOString(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'data' => $data,
        ];

        self::$breadcrumbs[] = $breadcrumb;

        // Keep only last 50 breadcrumbs to prevent memory issues
        if (count(self::$breadcrumbs) > 50) {
            self::$breadcrumbs = array_slice(self::$breadcrumbs, -50);
        }

        self::persistContext();
    }

    /**
     * Add context data that persists throughout the flow
     */
    public static function addContextData(string $key, $value): void
    {
        self::$contextData[$key] = $value;
        self::persistContext();
    }

    /**
     * Get all context data
     */
    public static function getContextData(): array
    {
        return self::$contextData;
    }

    /**
     * Get all breadcrumbs
     */
    public static function getBreadcrumbs(): array
    {
        return self::$breadcrumbs;
    }

    /**
     * Track a business operation with automatic breadcrumb
     */
    public static function trackOperation(string $operation, callable $callback, array $context = [])
    {
        $startTime = microtime(true);
        self::addBreadcrumb("operation_started_{$operation}", $context);

        try {
            $result = $callback();

            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

            self::addBreadcrumb("operation_completed_{$operation}", [
                'duration_ms' => round($duration, 2),
                'success' => true,
            ]);

            return $result;
        } catch (\Throwable $e) {
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            self::addBreadcrumb("operation_failed_{$operation}", [
                'duration_ms' => round($duration, 2),
                'error' => $e->getMessage(),
                'success' => false,
            ]);

            throw $e;
        }
    }

    /**
     * Track payment flow specifically
     */
    public static function trackPaymentFlow(string $paymentId, string $step, array $data = []): void
    {
        self::addContextData('payment_id', $paymentId);
        self::addBreadcrumb("payment_{$step}", array_merge($data, [
            'payment_id' => $paymentId,
        ]));

        // Log payment flow step
        PaymentLogger::logPaymentOperation("payment_flow_{$step}", array_merge($data, [
            'payment_id' => $paymentId,
            'correlation_id' => self::$currentCorrelationId,
            'flow_step' => $step,
        ]), 'info');
    }

    /**
     * Track booking flow
     */
    public static function trackBookingFlow(string $bookingId, string $step, array $data = []): void
    {
        self::addContextData('booking_id', $bookingId);
        self::addBreadcrumb("booking_{$step}", array_merge($data, [
            'booking_id' => $bookingId,
        ]));

        EnterpriseLogger::logBookingOperation("booking_flow_{$step}", array_merge($data, [
            'booking_id' => $bookingId,
            'correlation_id' => self::$currentCorrelationId,
            'flow_step' => $step,
        ]), 'info');
    }

    /**
     * Get complete flow summary
     */
    public static function getFlowSummary(): array
    {
        $summary = [
            'correlation_id' => self::$currentCorrelationId,
            'total_breadcrumbs' => count(self::$breadcrumbs),
            'context_data' => self::$contextData,
            'breadcrumbs' => self::$breadcrumbs,
        ];

        if (! empty(self::$breadcrumbs)) {
            $firstBreadcrumb = self::$breadcrumbs[0];
            $lastBreadcrumb = end(self::$breadcrumbs);

            $startTime = \Carbon\Carbon::parse($firstBreadcrumb['timestamp']);
            $endTime = \Carbon\Carbon::parse($lastBreadcrumb['timestamp']);

            $summary['flow_duration_ms'] = $endTime->diffInMilliseconds($startTime);
            $summary['started_at'] = $firstBreadcrumb['timestamp'];
            $summary['last_activity_at'] = $lastBreadcrumb['timestamp'];
        }

        return $summary;
    }

    /**
     * End correlation tracking and persist final state
     */
    public static function endTracking(array $finalData = []): void
    {
        self::addBreadcrumb('request_completed', $finalData);

        $summary = self::getFlowSummary();

        // Log complete flow for analysis
        EnterpriseLogger::logCustomEvent('correlation_flow_completed', 'info', $summary);

        // Persist final state
        self::persistContext();

        // Clear in-memory data
        self::$breadcrumbs = [];
        self::$contextData = [];
    }

    /**
     * Search for correlations by criteria
     */
    public static function findCorrelations(array $criteria): array
    {
        $results = [];

        // This is a simplified implementation
        // In production, you'd want to use a proper search system like Elasticsearch
        $cacheKeys = Cache::getRedis()->keys(self::CACHE_PREFIX.'*');

        foreach ($cacheKeys as $key) {
            $data = Cache::get($key);
            if ($data && self::matchesCriteria($data, $criteria)) {
                $results[] = $data;
            }
        }

        return $results;
    }

    /**
     * Get related correlations for a specific entity
     */
    public static function getRelatedCorrelations(string $entityType, string $entityId): array
    {
        return self::findCorrelations([
            "context_data.{$entityType}_id" => $entityId,
        ]);
    }

    /**
     * Generate unique correlation ID
     */
    private static function generateCorrelationId(): string
    {
        return sprintf(
            'v5_corr_%s_%s_%s',
            date('Ymd_His'),
            substr(md5(request()?->ip() ?? gethostname()), 0, 8),
            Str::random(8)
        );
    }

    /**
     * Persist context to cache for cross-request tracking
     */
    private static function persistContext(): void
    {
        if (self::$currentCorrelationId) {
            $data = [
                'correlation_id' => self::$currentCorrelationId,
                'breadcrumbs' => self::$breadcrumbs,
                'context_data' => self::$contextData,
                'last_updated' => now()->toISOString(),
                'request_info' => [
                    'url' => request()?->fullUrl(),
                    'method' => request()?->method(),
                    'user_id' => request()?->user()?->id,
                    'ip' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ],
            ];

            Cache::put(
                self::CACHE_PREFIX.self::$currentCorrelationId,
                $data,
                self::CACHE_TTL
            );
        }
    }

    /**
     * Load existing context from cache
     */
    private static function loadExistingContext(string $correlationId): void
    {
        $data = Cache::get(self::CACHE_PREFIX.$correlationId);

        if ($data) {
            self::$breadcrumbs = $data['breadcrumbs'] ?? [];
            self::$contextData = $data['context_data'] ?? [];
        }
    }

    /**
     * Check if cached data matches search criteria
     */
    private static function matchesCriteria(array $data, array $criteria): bool
    {
        foreach ($criteria as $key => $value) {
            if (strpos($key, '.') !== false) {
                // Handle nested keys like "context_data.booking_id"
                $keys = explode('.', $key);
                $current = $data;

                foreach ($keys as $k) {
                    if (! isset($current[$k])) {
                        return false;
                    }
                    $current = $current[$k];
                }

                if ($current !== $value) {
                    return false;
                }
            } else {
                if (! isset($data[$key]) || $data[$key] !== $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
