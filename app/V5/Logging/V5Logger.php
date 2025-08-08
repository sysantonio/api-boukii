<?php

namespace App\V5\Logging;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class V5Logger
{
    /**
     * Log levels
     */
    public const LEVEL_DEBUG = 'debug';

    public const LEVEL_INFO = 'info';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_ERROR = 'error';

    public const LEVEL_CRITICAL = 'critical';

    /**
     * Log categories for better organization
     */
    public const CATEGORY_API = 'api';

    public const CATEGORY_AUTH = 'auth';

    public const CATEGORY_BUSINESS = 'business';

    public const CATEGORY_PERFORMANCE = 'performance';

    public const CATEGORY_SECURITY = 'security';

    public const CATEGORY_DATABASE = 'database';

    public const CATEGORY_CACHE = 'cache';

    public const CATEGORY_VALIDATION = 'validation';

    public const CATEGORY_SYSTEM = 'system';

    /**
     * V5 specific log channel
     */
    private const DEFAULT_CHANNEL = 'v5_enterprise';

    /**
     * Sensitive fields to be masked in logs
     */
    private const SENSITIVE_FIELDS = [
        'password', 'password_confirmation', 'token', 'api_key', 'secret',
        'card_number', 'cvv', 'card_token', 'bank_account', 'iban',
        'credit_card', 'debit_card', 'payment_token', 'authorization_code',
    ];

    /**
     * Current correlation ID for the request
     */
    private static ?string $correlationId = null;

    /**
     * Context data that persists across log calls
     */
    private static array $persistentContext = [];

    /**
     * Initialize correlation tracking for the request
     */
    public static function initializeCorrelation(?string $correlationId = null): string
    {
        if ($correlationId) {
            self::$correlationId = $correlationId;
            CorrelationTracker::setCorrelationId($correlationId);
        } else {
            self::$correlationId = CorrelationTracker::initializeForRequest();
        }

        return self::$correlationId;
    }

    /**
     * Set persistent context data
     */
    public static function setPersistentContext(array $context): void
    {
        self::$persistentContext = array_merge(self::$persistentContext, $context);
    }

    /**
     * Clear persistent context
     */
    public static function clearPersistentContext(): void
    {
        self::$persistentContext = [];
    }

    /**
     * Log API request with enhanced context and correlation
     */
    public static function logApiRequest(Request $request, array $context = []): void
    {
        // Ensure correlation tracking is initialized
        if (! self::$correlationId) {
            self::initializeCorrelation();
        }

        CorrelationTracker::addBreadcrumb('api_request_received', [
            'method' => $request->method(),
            'path' => $request->path(),
            'query_params_count' => count($request->query()),
        ]);

        $logData = self::buildBaseLogData(self::CATEGORY_API, 'request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'query_string' => $request->getQueryString(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'content_length' => $request->header('Content-Length'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            'user_id' => $request->user()?->id,
            'season_id' => $request->get('season_id'),
            'school_id' => $request->get('school_id'),
            'locale' => app()->getLocale(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
        ]);

        // Add request payload with sensitive data filtering
        $payload = self::sanitizeData($request->except(self::SENSITIVE_FIELDS));
        if (! empty($payload)) {
            $logData['payload'] = $payload;
            $logData['payload_size_bytes'] = strlen(json_encode($payload));
        }

        $logData = array_merge($logData, $context);

        self::writeLog(self::LEVEL_INFO, 'API Request Received', $logData);
    }

    /**
     * Log API response with performance metrics
     */
    public static function logApiResponse(Request $request, $response, ?float $responseTime = null, array $context = []): void
    {
        $statusCode = $response->getStatusCode();
        $responseTimeMs = $responseTime ? round($responseTime * 1000, 2) : null;

        CorrelationTracker::addBreadcrumb('api_response_sent', [
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'success' => $statusCode < 400,
        ]);

        $logData = self::buildBaseLogData(self::CATEGORY_API, 'response', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'status_code' => $statusCode,
            'status_text' => method_exists($response, 'getStatusText') ? $response->getStatusText() : null,
            'user_id' => $request->user()?->id,
            'season_id' => $request->get('season_id'),
            'response_time_ms' => $responseTimeMs,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);

        // Add response metadata
        if (method_exists($response, 'getContent')) {
            $contentLength = strlen($response->getContent());
            $logData['response_size_bytes'] = $contentLength;

            // Log large responses
            if ($contentLength > 1024 * 1024) { // 1MB
                $logData['large_response'] = true;
            }
        }

        // Add response headers if relevant
        if ($response->headers) {
            $logData['response_headers'] = [
                'content_type' => $response->headers->get('Content-Type'),
                'cache_control' => $response->headers->get('Cache-Control'),
                'etag' => $response->headers->get('ETag'),
            ];
        }

        $logData = array_merge($logData, $context);

        // Determine log level based on status code and performance
        $level = self::determineResponseLogLevel($statusCode, $responseTimeMs);

        self::writeLog($level, 'API Response Sent', $logData);

        // Log performance issues separately
        if ($responseTimeMs && $responseTimeMs > 1000) {
            self::logPerformance('slow_api_response', $responseTime, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $statusCode,
            ]);
        }
    }

    /**
     * Determine appropriate log level for API response
     */
    private static function determineResponseLogLevel(int $statusCode, ?float $responseTimeMs): string
    {
        if ($statusCode >= 500) {
            return self::LEVEL_ERROR;
        }

        if ($statusCode >= 400) {
            return self::LEVEL_WARNING;
        }

        if ($responseTimeMs && $responseTimeMs > 2000) {
            return self::LEVEL_WARNING;
        }

        return self::LEVEL_INFO;
    }

    /**
     * Log authentication events with security context
     */
    public static function logAuthEvent(string $event, array $context = []): void
    {
        CorrelationTracker::addBreadcrumb("auth_{$event}", $context);

        $logData = self::buildBaseLogData(self::CATEGORY_AUTH, $event, [
            'event' => $event,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'session_id' => request()?->session()?->getId(),
            'referrer' => request()?->header('Referer'),
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        // Determine log level based on event type
        $level = self::getAuthEventLogLevel($event);

        self::writeLog($level, 'Authentication Event', $logData);

        // Log security events to security channel as well
        if (in_array($event, ['login_failed', 'account_locked', 'suspicious_activity'])) {
            self::writeLog($level, 'Security Event', $logData, 'v5_security');
        }
    }

    /**
     * Get appropriate log level for auth events
     */
    private static function getAuthEventLogLevel(string $event): string
    {
        $warningEvents = ['login_failed', 'logout_forced', 'token_expired'];
        $errorEvents = ['account_locked', 'suspicious_activity', 'brute_force_attempt'];

        if (in_array($event, $errorEvents)) {
            return self::LEVEL_ERROR;
        }

        if (in_array($event, $warningEvents)) {
            return self::LEVEL_WARNING;
        }

        return self::LEVEL_INFO;
    }

    /**
     * Log business logic events with entity tracking
     */
    public static function logBusinessEvent(string $entity, string $action, array $context = []): void
    {
        $operation = "{$entity}_{$action}";
        CorrelationTracker::addBreadcrumb($operation, array_merge($context, [
            'entity' => $entity,
            'action' => $action,
        ]));

        $logData = self::buildBaseLogData(self::CATEGORY_BUSINESS, $operation, [
            'entity' => $entity,
            'action' => $action,
            'user_id' => request()?->user()?->id,
            'season_id' => request()?->get('season_id'),
            'school_id' => request()?->get('school_id'),
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        // Add entity-specific tracking
        if (isset($context[strtolower($entity).'_id'])) {
            CorrelationTracker::addContextData(
                strtolower($entity).'_id',
                $context[strtolower($entity).'_id']
            );
        }

        self::writeLog(self::LEVEL_INFO, 'Business Event', $logData);
    }

    /**
     * Log performance metrics with detailed analysis
     */
    public static function logPerformance(string $operation, float $duration, array $context = []): void
    {
        $durationMs = round($duration * 1000, 2);

        CorrelationTracker::addBreadcrumb("performance_{$operation}", [
            'duration_ms' => $durationMs,
            'slow_operation' => $duration > 1.0,
        ]);

        $logData = self::buildBaseLogData(self::CATEGORY_PERFORMANCE, $operation, [
            'operation' => $operation,
            'duration_ms' => $durationMs,
            'duration_seconds' => $duration,
            'user_id' => request()?->user()?->id,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);

        $logData = array_merge($logData, $context);

        // Determine severity based on duration
        $level = self::getPerformanceLogLevel($duration);

        self::writeLog($level, 'Performance Metric', $logData, 'v5_performance');

        // Alert on critical performance issues
        if ($duration > 5.0) {
            $alertData = array_merge($logData, [
                'alert_type' => 'CRITICAL_PERFORMANCE',
                'threshold_exceeded' => '5000ms',
                'requires_immediate_attention' => true,
            ]);

            self::writeLog(self::LEVEL_CRITICAL, 'Critical Performance Alert', $alertData, 'v5_alerts');
        }
    }

    /**
     * Get appropriate log level for performance metrics
     */
    private static function getPerformanceLogLevel(float $duration): string
    {
        if ($duration > 5.0) {
            return self::LEVEL_CRITICAL;
        }

        if ($duration > 2.0) {
            return self::LEVEL_ERROR;
        }

        if ($duration > 1.0) {
            return self::LEVEL_WARNING;
        }

        return self::LEVEL_INFO;
    }

    /**
     * Log security events with threat analysis
     */
    public static function logSecurityEvent(string $event, string $level = self::LEVEL_WARNING, array $context = []): void
    {
        CorrelationTracker::addBreadcrumb("security_{$event}", array_merge($context, [
            'security_event' => true,
            'threat_level' => $level,
        ]));

        $request = request();
        $logData = self::buildBaseLogData(self::CATEGORY_SECURITY, $event, [
            'event' => $event,
            'threat_level' => $level,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id' => $request?->user()?->id,
            'session_id' => $request?->session()?->getId(),
            'referrer' => $request?->header('Referer'),
            'request_method' => $request?->method(),
            'request_path' => $request?->path(),
            'geo_location' => self::getGeoLocation($request?->ip()),
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        // Always log to security channel
        self::writeLog($level, 'Security Event', $logData, 'v5_security');

        // High-priority security events go to alerts
        if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL])) {
            $alertData = array_merge($logData, [
                'priority' => 'HIGH',
                'requires_immediate_attention' => true,
                'security_incident' => true,
            ]);

            self::writeLog($level, 'Security Alert', $alertData, 'v5_alerts');
        }
    }

    /**
     * Get basic geo location info (placeholder for IP geolocation service)
     */
    private static function getGeoLocation(?string $ip): ?array
    {
        if (! $ip || $ip === '127.0.0.1' || $ip === '::1') {
            return ['location' => 'localhost'];
        }

        // This would integrate with a geolocation service in production
        return ['ip' => $ip, 'location' => 'unknown'];
    }

    /**
     * Log database operations with query analysis
     */
    public static function logDatabaseOperation(string $operation, string $table, array $context = []): void
    {
        $operationKey = "db_{$operation}_{$table}";

        $logData = self::buildBaseLogData(self::CATEGORY_DATABASE, $operationKey, [
            'operation' => $operation,
            'table' => $table,
            'user_id' => request()?->user()?->id,
            'connection' => $context['connection'] ?? config('database.default'),
            'query_time_ms' => $context['duration_ms'] ?? null,
            'affected_rows' => $context['affected_rows'] ?? null,
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        // Determine log level based on operation performance
        $level = self::LEVEL_DEBUG;
        if (isset($context['duration_ms']) && $context['duration_ms'] > 1000) {
            $level = self::LEVEL_WARNING;
            $logData['slow_query'] = true;
        }

        if (isset($context['duration_ms']) && $context['duration_ms'] > 5000) {
            $level = self::LEVEL_ERROR;
            $logData['very_slow_query'] = true;
        }

        self::writeLog($level, 'Database Operation', $logData);

        // Track database breadcrumb for correlation
        CorrelationTracker::addBreadcrumb($operationKey, [
            'table' => $table,
            'operation' => $operation,
            'duration_ms' => $context['duration_ms'] ?? null,
        ]);
    }

    /**
     * Log cache operations with performance tracking
     */
    public static function logCacheOperation(string $operation, string $key, array $context = []): void
    {
        $operationKey = "cache_{$operation}";

        $logData = self::buildBaseLogData(self::CATEGORY_CACHE, $operationKey, [
            'operation' => $operation,
            'cache_key' => $key,
            'cache_driver' => config('cache.default'),
            'hit' => $context['hit'] ?? null,
            'miss' => $context['miss'] ?? null,
            'ttl_seconds' => $context['ttl'] ?? null,
            'value_size_bytes' => $context['size'] ?? null,
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        self::writeLog(self::LEVEL_DEBUG, 'Cache Operation', $logData);

        // Track cache efficiency in correlation
        CorrelationTracker::addBreadcrumb($operationKey, [
            'cache_key' => $key,
            'operation' => $operation,
            'result' => $context['hit'] ? 'hit' : 'miss',
        ]);
    }

    /**
     * Log validation errors with detailed context
     */
    public static function logValidationError(array $errors, array $context = []): void
    {
        CorrelationTracker::addBreadcrumb('validation_failed', [
            'error_count' => count($errors),
            'error_fields' => array_keys($errors),
        ]);

        $request = request();
        $logData = self::buildBaseLogData(self::CATEGORY_VALIDATION, 'validation_error', [
            'errors' => $errors,
            'error_count' => count($errors),
            'error_fields' => array_keys($errors),
            'user_id' => $request?->user()?->id,
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'input_data' => self::sanitizeData($request?->except(self::SENSITIVE_FIELDS) ?? []),
        ]);

        $logData = array_merge($logData, self::sanitizeData($context));

        self::writeLog(self::LEVEL_WARNING, 'Validation Error', $logData);
    }

    /**
     * Log custom events with structured data and correlation
     */
    public static function logCustomEvent(string $event, string $level, array $data = [], ?string $category = null): void
    {
        CorrelationTracker::addBreadcrumb($event, $data);

        $logData = self::buildBaseLogData($category ?? self::CATEGORY_SYSTEM, $event, [
            'event' => $event,
            'user_id' => request()?->user()?->id,
            'custom_data' => self::sanitizeData($data),
        ]);

        self::writeLog($level, 'Custom Event', $logData);
    }

    /**
     * Log system errors with full context
     */
    public static function logSystemError(\Throwable $exception, array $context = []): void
    {
        CorrelationTracker::addBreadcrumb('system_error_occurred', [
            'exception_class' => get_class($exception),
            'error_code' => $exception->getCode(),
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine(),
        ]);

        $logData = self::buildBaseLogData(self::CATEGORY_SYSTEM, 'system_error', [
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
            'context_data' => self::sanitizeData($context),
        ]);

        self::writeLog(self::LEVEL_ERROR, 'System Error', $logData);

        // Critical system errors also go to alerts
        if ($exception instanceof \Error || $exception instanceof \ParseError) {
            self::writeLog(self::LEVEL_CRITICAL, 'Critical System Error', $logData, 'v5_alerts');
        }
    }

    /**
     * Get current correlation ID
     */
    public static function getCorrelationId(): ?string
    {
        return self::$correlationId ?? CorrelationTracker::getCurrentCorrelationId();
    }

    /**
     * Build base log data structure with common fields
     */
    private static function buildBaseLogData(string $category, string $operation, array $specificData = []): array
    {
        $baseData = [
            'correlation_id' => self::getCorrelationId(),
            'category' => $category,
            'operation' => $operation,
            'timestamp' => Carbon::now()->toISOString(),
            'environment' => config('app.env'),
            'application_version' => config('app.version', '1.0.0'),
            'server_name' => gethostname(),
            'php_version' => PHP_VERSION,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ];

        return array_merge($baseData, self::$persistentContext, $specificData);
    }

    /**
     * Write log to specified channel with fallback
     */
    private static function writeLog(string $level, string $message, array $context, ?string $channel = null): void
    {
        $targetChannel = $channel ?? self::DEFAULT_CHANNEL;

        // Ensure the channel exists, fallback to default Laravel log if not
        try {
            Log::channel($targetChannel)->{$level}($message, $context);
        } catch (\InvalidArgumentException $e) {
            // Fallback to default channel if specified channel doesn't exist
            Log::{$level}("[FALLBACK] {$message}", array_merge($context, [
                'original_channel' => $targetChannel,
                'fallback_reason' => 'Channel not found',
            ]));
        }
    }

    /**
     * Enhanced data sanitization with configurable fields
     */
    private static function sanitizeData(array $data): array
    {
        $sensitiveFields = array_merge(
            self::SENSITIVE_FIELDS,
            config('v5_logging.sensitive_fields', [])
        );

        return self::recursiveSanitize($data, $sensitiveFields);
    }

    /**
     * Recursively sanitize nested arrays
     */
    private static function recursiveSanitize(array $data, array $sensitiveFields): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::recursiveSanitize($value, $sensitiveFields);
            } elseif (in_array($key, $sensitiveFields)) {
                if ($key === 'card_number' && is_string($value) && strlen($value) >= 8) {
                    // Keep first 4 and last 4 digits for card numbers
                    $data[$key] = substr($value, 0, 4).str_repeat('*', strlen($value) - 8).substr($value, -4);
                } elseif (is_string($value) && strlen($value) > 8) {
                    // For other sensitive fields, show pattern if long enough
                    $data[$key] = substr($value, 0, 2).str_repeat('*', strlen($value) - 4).substr($value, -2);
                } else {
                    $data[$key] = '[REDACTED]';
                }
            } elseif (is_string($value) && strlen($value) > 1000) {
                // Truncate very long strings
                $data[$key] = substr($value, 0, 1000).'... [TRUNCATED]';
            }
        }

        return $data;
    }

    /**
     * Get comprehensive flow summary for debugging
     */
    public static function getFlowSummary(): array
    {
        return [
            'correlation_id' => self::getCorrelationId(),
            'persistent_context' => self::$persistentContext,
            'correlation_summary' => CorrelationTracker::getFlowSummary(),
        ];
    }

    /**
     * End request logging and finalize correlation tracking
     */
    public static function endRequest(array $finalData = []): void
    {
        CorrelationTracker::endTracking($finalData);
        self::clearPersistentContext();
        self::$correlationId = null;
    }
}
