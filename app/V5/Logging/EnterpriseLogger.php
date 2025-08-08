<?php

namespace App\V5\Logging;

use Illuminate\Support\Facades\Log;

class EnterpriseLogger
{
    private const CHANNEL = 'v5_enterprise';

    private const HIGH_PRIORITY_EVENTS = [
        'payment_failed', 'payment_fraud_detected', 'booking_cancelled_with_refund',
        'season_closed', 'user_locked', 'system_error', 'database_error',
    ];

    /**
     * Enhanced context for all logs
     */
    private static function getEnhancedContext(array $customContext = []): array
    {
        $request = request();

        $baseContext = [
            'correlation_id' => V5Logger::getCorrelationId(),
            'timestamp' => now()->toISOString(),
            'environment' => config('app.env'),
            'application_version' => config('app.version', '1.0.0'),
            'server_name' => gethostname(),
            'php_version' => PHP_VERSION,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        if ($request) {
            $baseContext = array_merge($baseContext, [
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_path' => $request->path(),
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('Referer'),
                'user_id' => $request->user()?->id,
                'season_id' => $request->get('season_id'),
                'school_id' => $request->get('school_id'),
                'locale' => app()->getLocale(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);
        }

        return array_merge($baseContext, $customContext);
    }

    /**
     * Log payment operations with full financial context
     */
    public static function logPaymentOperation(string $operation, array $paymentData, string $level = 'info'): void
    {
        $sensitiveFields = ['card_number', 'cvv', 'card_token', 'bank_account'];
        $sanitizedData = self::sanitizeFinancialData($paymentData, $sensitiveFields);

        $context = self::getEnhancedContext([
            'category' => 'payment',
            'operation' => $operation,
            'payment_id' => $paymentData['payment_id'] ?? null,
            'booking_id' => $paymentData['booking_id'] ?? null,
            'amount' => $paymentData['amount'] ?? null,
            'currency' => $paymentData['currency'] ?? 'CHF',
            'payment_method' => $paymentData['payment_method'] ?? null,
            'gateway' => $paymentData['gateway'] ?? null,
            'gateway_transaction_id' => $paymentData['gateway_transaction_id'] ?? null,
            'payment_status' => $paymentData['status'] ?? null,
            'customer_id' => $paymentData['customer_id'] ?? null,
            'payment_data' => $sanitizedData,
        ]);

        self::logWithPriority($operation, $level, 'Payment Operation', $context);
    }

    /**
     * Log booking operations with complete business context
     */
    public static function logBookingOperation(string $operation, array $bookingData, string $level = 'info'): void
    {
        $context = self::getEnhancedContext([
            'category' => 'booking',
            'operation' => $operation,
            'booking_id' => $bookingData['booking_id'] ?? null,
            'course_id' => $bookingData['course_id'] ?? null,
            'client_id' => $bookingData['client_id'] ?? null,
            'monitor_id' => $bookingData['monitor_id'] ?? null,
            'booking_date' => $bookingData['booking_date'] ?? null,
            'course_date' => $bookingData['course_date'] ?? null,
            'participants_count' => $bookingData['participants_count'] ?? null,
            'total_price' => $bookingData['total_price'] ?? null,
            'booking_status' => $bookingData['status'] ?? null,
            'payment_status' => $bookingData['payment_status'] ?? null,
            'cancellation_reason' => $bookingData['cancellation_reason'] ?? null,
            'booking_data' => $bookingData,
        ]);

        self::logWithPriority($operation, $level, 'Booking Operation', $context);
    }

    /**
     * Log database operations with query details
     */
    public static function logDatabaseOperation(string $query, float $duration, array $bindings = [], string $connection = 'default'): void
    {
        $context = self::getEnhancedContext([
            'category' => 'database',
            'query' => self::sanitizeQuery($query),
            'bindings' => self::sanitizeBindings($bindings),
            'duration_ms' => round($duration * 1000, 2),
            'connection' => $connection,
            'affected_rows' => null, // Would need to be passed from caller
        ]);

        $level = $duration > 1.0 ? 'warning' : 'debug';

        if ($duration > 5.0) {
            $level = 'critical';
            $context['alert'] = 'SLOW_QUERY_DETECTED';
        }

        Log::channel(self::CHANNEL)->{$level}('Database Operation', $context);
    }

    /**
     * Log authentication events with security context
     */
    public static function logAuthenticationEvent(string $event, array $authData, string $level = 'info'): void
    {
        $context = self::getEnhancedContext([
            'category' => 'authentication',
            'event' => $event,
            'user_id' => $authData['user_id'] ?? null,
            'email' => $authData['email'] ?? null,
            'season_id' => $authData['season_id'] ?? null,
            'role' => $authData['role'] ?? null,
            'login_method' => $authData['login_method'] ?? 'standard',
            'failed_attempts_count' => $authData['failed_attempts'] ?? null,
            'last_login' => $authData['last_login'] ?? null,
            'two_factor_enabled' => $authData['two_factor_enabled'] ?? false,
            'suspicious_activity' => $authData['suspicious_activity'] ?? false,
        ]);

        // Security events get higher priority
        if (in_array($event, ['login_failed', 'account_locked', 'suspicious_login'])) {
            $level = 'warning';
        }

        self::logWithPriority($event, $level, 'Authentication Event', $context);
    }

    /**
     * Log system errors with stack trace and environment
     */
    public static function logSystemError(\Throwable $exception, array $customContext = []): void
    {
        $context = self::getEnhancedContext([
            'category' => 'system_error',
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'previous_exception' => $exception->getPrevious() ? [
                'class' => get_class($exception->getPrevious()),
                'message' => $exception->getPrevious()->getMessage(),
            ] : null,
            'custom_context' => $customContext,
            'system_load' => self::getSystemLoad(),
            'disk_usage' => self::getDiskUsage(),
        ]);

        self::logWithPriority('system_error', 'critical', 'System Error', $context);
    }

    /**
     * Log business rule violations
     */
    public static function logBusinessRuleViolation(string $rule, array $context = []): void
    {
        $logContext = self::getEnhancedContext([
            'category' => 'business_rule_violation',
            'rule' => $rule,
            'violation_data' => $context,
        ]);

        self::logWithPriority('business_rule_violation', 'warning', 'Business Rule Violation', $logContext);
    }

    /**
     * Log performance metrics with detailed breakdown
     */
    public static function logPerformanceMetrics(string $operation, array $metrics): void
    {
        $context = self::getEnhancedContext([
            'category' => 'performance',
            'operation' => $operation,
            'total_duration_ms' => $metrics['total_duration_ms'] ?? null,
            'database_time_ms' => $metrics['database_time_ms'] ?? null,
            'cache_time_ms' => $metrics['cache_time_ms'] ?? null,
            'external_api_time_ms' => $metrics['external_api_time_ms'] ?? null,
            'memory_used_mb' => $metrics['memory_used_mb'] ?? null,
            'cache_hits' => $metrics['cache_hits'] ?? null,
            'cache_misses' => $metrics['cache_misses'] ?? null,
            'queries_count' => $metrics['queries_count'] ?? null,
            'cpu_usage_percent' => $metrics['cpu_usage_percent'] ?? null,
        ]);

        $level = 'info';
        if (($metrics['total_duration_ms'] ?? 0) > 2000) {
            $level = 'warning';
        }
        if (($metrics['total_duration_ms'] ?? 0) > 5000) {
            $level = 'critical';
            $context['alert'] = 'PERFORMANCE_DEGRADATION';
        }

        Log::channel(self::CHANNEL)->{$level}('Performance Metrics', $context);
    }

    /**
     * Log with priority-based routing and alerting
     */
    private static function logWithPriority(string $event, string $level, string $message, array $context): void
    {
        // Add priority flag for high-priority events
        if (in_array($event, self::HIGH_PRIORITY_EVENTS)) {
            $context['priority'] = 'HIGH';
            $context['requires_immediate_attention'] = true;
        }

        Log::channel(self::CHANNEL)->{$level}($message, $context);

        // Send to separate high-priority channel if needed
        if ($context['priority'] ?? false === 'HIGH') {
            Log::channel('v5_alerts')->{$level}($message, $context);
        }
    }

    /**
     * Create structured search tags for log aggregation
     */
    public static function createSearchTags(array $data): array
    {
        $tags = [];

        // Auto-generate searchable tags
        if (isset($data['user_id'])) {
            $tags[] = "user:{$data['user_id']}";
        }
        if (isset($data['booking_id'])) {
            $tags[] = "booking:{$data['booking_id']}";
        }
        if (isset($data['payment_id'])) {
            $tags[] = "payment:{$data['payment_id']}";
        }
        if (isset($data['season_id'])) {
            $tags[] = "season:{$data['season_id']}";
        }
        if (isset($data['school_id'])) {
            $tags[] = "school:{$data['school_id']}";
        }

        return $tags;
    }

    /**
     * Sanitize financial data
     */
    private static function sanitizeFinancialData(array $data, array $sensitiveFields): array
    {
        $sanitized = $data;

        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                if ($field === 'card_number' && is_string($sanitized[$field])) {
                    // Keep first 4 and last 4 digits
                    $sanitized[$field] = substr($sanitized[$field], 0, 4).'****'.substr($sanitized[$field], -4);
                } else {
                    $sanitized[$field] = '[REDACTED]';
                }
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize SQL query for logging
     */
    private static function sanitizeQuery(string $query): string
    {
        // Remove excessive whitespace
        $query = preg_replace('/\s+/', ' ', trim($query));

        // Truncate very long queries
        if (strlen($query) > 1000) {
            $query = substr($query, 0, 1000).'... [TRUNCATED]';
        }

        return $query;
    }

    /**
     * Sanitize query bindings
     */
    private static function sanitizeBindings(array $bindings): array
    {
        return array_map(function ($binding) {
            if (is_string($binding) && strlen($binding) > 100) {
                return substr($binding, 0, 100).'... [TRUNCATED]';
            }

            return $binding;
        }, $bindings);
    }

    /**
     * Get system load information
     */
    private static function getSystemLoad(): ?array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            return [
                '1_min' => $load[0] ?? null,
                '5_min' => $load[1] ?? null,
                '15_min' => $load[2] ?? null,
            ];
        }

        return null;
    }

    /**
     * Get disk usage information
     */
    private static function getDiskUsage(): ?array
    {
        $path = storage_path();
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $free = disk_free_space($path);
            $total = disk_total_space($path);

            return [
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
                'used_percent' => round((($total - $free) / $total) * 100, 2),
            ];
        }

        return null;
    }

    /**
     * Log custom events (compatibility method for CorrelationTracker)
     */
    public static function logCustomEvent(string $event, string $level, array $data = []): void
    {
        $context = self::getEnhancedContext([
            'category' => 'custom_event',
            'event' => $event,
            'custom_data' => $data,
        ]);

        self::logWithPriority($event, $level, 'Custom Event', $context);
    }
}
