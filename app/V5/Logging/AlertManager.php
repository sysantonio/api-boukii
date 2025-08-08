<?php

namespace App\V5\Logging;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AlertManager
{
    private const ALERT_CACHE_PREFIX = 'v5_alert_';

    private const ALERT_TTL = 3600; // 1 hour

    // Alert thresholds
    private const THRESHOLDS = [
        'error_rate' => 5.0, // 5% error rate triggers alert
        'payment_failure_rate' => 10.0, // 10% payment failure rate
        'response_time_ms' => 2000, // 2 seconds response time
        'memory_usage_percent' => 85, // 85% memory usage
        'disk_usage_percent' => 90, // 90% disk usage
        'fraud_score' => 80, // Fraud score above 80
    ];

    // Alert priorities
    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_CRITICAL = 'critical';

    /**
     * Process incoming log data and trigger alerts if thresholds are exceeded
     */
    public static function processLogForAlerts(array $logData): void
    {
        // Check various alert conditions
        self::checkErrorRateAlert($logData);
        self::checkPaymentFailureAlert($logData);
        self::checkPerformanceAlert($logData);
        self::checkSecurityAlert($logData);
        self::checkSystemResourceAlert($logData);
        self::checkBusinessRuleAlert($logData);
        self::checkFinancialDiscrepancyAlert($logData);
    }

    /**
     * Check for error rate alerts
     */
    private static function checkErrorRateAlert(array $logData): void
    {
        if (($logData['level'] ?? '') === 'error') {
            $key = 'error_count_'.date('Y-m-d-H');
            $errorCount = Cache::increment($key, 1);
            Cache::expire($key, 3600); // Expire after 1 hour

            $totalCount = Cache::get('total_requests_'.date('Y-m-d-H'), 0);

            if ($totalCount > 100) { // Only check if we have sufficient data
                $errorRate = ($errorCount / $totalCount) * 100;

                if ($errorRate > self::THRESHOLDS['error_rate']) {
                    self::triggerAlert([
                        'type' => 'error_rate_threshold',
                        'priority' => self::PRIORITY_HIGH,
                        'message' => "Error rate ({$errorRate}%) exceeds threshold",
                        'data' => [
                            'error_rate' => $errorRate,
                            'error_count' => $errorCount,
                            'total_requests' => $totalCount,
                            'threshold' => self::THRESHOLDS['error_rate'],
                        ],
                        'correlation_id' => $logData['correlation_id'] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Check for payment failure alerts
     */
    private static function checkPaymentFailureAlert(array $logData): void
    {
        if (($logData['category'] ?? '') === 'payment' &&
            str_contains($logData['operation'] ?? '', 'failed')) {

            $key = 'payment_failures_'.date('Y-m-d-H');
            $failureCount = Cache::increment($key, 1);
            Cache::expire($key, 3600);

            $totalPayments = Cache::get('total_payments_'.date('Y-m-d-H'), 0);

            if ($totalPayments > 10) {
                $failureRate = ($failureCount / $totalPayments) * 100;

                if ($failureRate > self::THRESHOLDS['payment_failure_rate']) {
                    self::triggerAlert([
                        'type' => 'payment_failure_threshold',
                        'priority' => self::PRIORITY_CRITICAL,
                        'message' => "Payment failure rate ({$failureRate}%) exceeds threshold",
                        'data' => [
                            'failure_rate' => $failureRate,
                            'failure_count' => $failureCount,
                            'total_payments' => $totalPayments,
                            'threshold' => self::THRESHOLDS['payment_failure_rate'],
                            'payment_id' => $logData['payment_id'] ?? null,
                            'gateway' => $logData['gateway'] ?? null,
                        ],
                        'correlation_id' => $logData['correlation_id'] ?? null,
                        'requires_immediate_attention' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Check for performance alerts
     */
    private static function checkPerformanceAlert(array $logData): void
    {
        $responseTime = $logData['response_time_ms'] ?? $logData['duration_ms'] ?? null;

        if ($responseTime && $responseTime > self::THRESHOLDS['response_time_ms']) {
            // Check if this is a pattern, not just a single slow request
            $key = 'slow_requests_'.date('Y-m-d-H');
            $slowCount = Cache::increment($key, 1);
            Cache::expire($key, 3600);

            if ($slowCount >= 5) { // 5 slow requests in an hour
                self::triggerAlert([
                    'type' => 'performance_degradation',
                    'priority' => self::PRIORITY_HIGH,
                    'message' => "Multiple slow requests detected ({$slowCount} requests > {$responseTime}ms)",
                    'data' => [
                        'slow_request_count' => $slowCount,
                        'latest_response_time' => $responseTime,
                        'threshold' => self::THRESHOLDS['response_time_ms'],
                        'operation' => $logData['operation'] ?? null,
                        'url' => $logData['request_url'] ?? null,
                    ],
                    'correlation_id' => $logData['correlation_id'] ?? null,
                ]);
            }
        }
    }

    /**
     * Check for security alerts
     */
    private static function checkSecurityAlert(array $logData): void
    {
        // Failed login attempts
        if (($logData['event'] ?? '') === 'login_failed') {
            $ip = $logData['user_ip'] ?? $logData['ip'] ?? null;

            if ($ip) {
                $key = "failed_logins_{$ip}_".date('Y-m-d-H');
                $failedCount = Cache::increment($key, 1);
                Cache::expire($key, 3600);

                if ($failedCount >= 5) { // 5 failed attempts from same IP
                    self::triggerAlert([
                        'type' => 'suspicious_login_activity',
                        'priority' => self::PRIORITY_HIGH,
                        'message' => "Multiple failed login attempts from IP: {$ip}",
                        'data' => [
                            'failed_attempts' => $failedCount,
                            'ip_address' => $ip,
                            'user_agent' => $logData['user_agent'] ?? null,
                            'attempted_email' => $logData['email'] ?? null,
                        ],
                        'correlation_id' => $logData['correlation_id'] ?? null,
                    ]);
                }
            }
        }

        // Fraud detection
        if (($logData['category'] ?? '') === 'payment' &&
            isset($logData['risk_score']) &&
            $logData['risk_score'] > self::THRESHOLDS['fraud_score']) {

            self::triggerAlert([
                'type' => 'fraud_detection',
                'priority' => self::PRIORITY_CRITICAL,
                'message' => "High fraud risk detected (score: {$logData['risk_score']})",
                'data' => [
                    'risk_score' => $logData['risk_score'],
                    'risk_level' => $logData['risk_level'] ?? null,
                    'fraud_indicators' => $logData['fraud_indicators'] ?? [],
                    'payment_id' => $logData['payment_id'] ?? null,
                    'customer_id' => $logData['customer_id'] ?? null,
                    'ip_address' => $logData['ip_address'] ?? null,
                ],
                'correlation_id' => $logData['correlation_id'] ?? null,
                'requires_immediate_attention' => true,
            ]);
        }
    }

    /**
     * Check for system resource alerts
     */
    private static function checkSystemResourceAlert(array $logData): void
    {
        // Memory usage
        if (isset($logData['memory_usage_mb']) && isset($logData['memory_peak_mb'])) {
            $memoryUsagePercent = ($logData['memory_usage_mb'] / 512) * 100; // Assuming 512MB limit

            if ($memoryUsagePercent > self::THRESHOLDS['memory_usage_percent']) {
                self::triggerAlert([
                    'type' => 'high_memory_usage',
                    'priority' => self::PRIORITY_MEDIUM,
                    'message' => "High memory usage detected ({$memoryUsagePercent}%)",
                    'data' => [
                        'memory_usage_percent' => $memoryUsagePercent,
                        'memory_usage_mb' => $logData['memory_usage_mb'],
                        'memory_peak_mb' => $logData['memory_peak_mb'],
                        'threshold' => self::THRESHOLDS['memory_usage_percent'],
                    ],
                    'correlation_id' => $logData['correlation_id'] ?? null,
                ]);
            }
        }

        // Disk usage
        if (isset($logData['disk_usage']['used_percent'])) {
            $diskUsagePercent = $logData['disk_usage']['used_percent'];

            if ($diskUsagePercent > self::THRESHOLDS['disk_usage_percent']) {
                self::triggerAlert([
                    'type' => 'high_disk_usage',
                    'priority' => self::PRIORITY_HIGH,
                    'message' => "High disk usage detected ({$diskUsagePercent}%)",
                    'data' => [
                        'disk_usage_percent' => $diskUsagePercent,
                        'free_gb' => $logData['disk_usage']['free_gb'] ?? null,
                        'total_gb' => $logData['disk_usage']['total_gb'] ?? null,
                        'threshold' => self::THRESHOLDS['disk_usage_percent'],
                    ],
                    'correlation_id' => $logData['correlation_id'] ?? null,
                ]);
            }
        }
    }

    /**
     * Check for business rule alerts
     */
    private static function checkBusinessRuleAlert(array $logData): void
    {
        if (($logData['category'] ?? '') === 'business_rule_violation') {
            self::triggerAlert([
                'type' => 'business_rule_violation',
                'priority' => self::PRIORITY_MEDIUM,
                'message' => "Business rule violation: {$logData['rule']}",
                'data' => [
                    'rule' => $logData['rule'] ?? null,
                    'violation_data' => $logData['violation_data'] ?? [],
                    'user_id' => $logData['user_id'] ?? null,
                    'season_id' => $logData['season_id'] ?? null,
                ],
                'correlation_id' => $logData['correlation_id'] ?? null,
            ]);
        }
    }

    /**
     * Check for financial discrepancy alerts
     */
    private static function checkFinancialDiscrepancyAlert(array $logData): void
    {
        if (($logData['type'] ?? '') === 'financial_transaction' &&
            ($logData['reconciliation_status'] ?? '') === 'discrepancy') {

            self::triggerAlert([
                'type' => 'financial_discrepancy',
                'priority' => self::PRIORITY_HIGH,
                'message' => 'Financial discrepancy detected in transaction',
                'data' => [
                    'transaction_id' => $logData['transaction_id'] ?? null,
                    'amount' => $logData['amount'] ?? null,
                    'currency' => $logData['currency'] ?? null,
                    'gateway' => $logData['gateway'] ?? null,
                    'school_id' => $logData['school_id'] ?? null,
                ],
                'correlation_id' => $logData['correlation_id'] ?? null,
                'requires_manual_review' => true,
            ]);
        }
    }

    /**
     * Trigger an alert
     */
    private static function triggerAlert(array $alertData): void
    {
        $alertId = self::generateAlertId();
        $alert = array_merge($alertData, [
            'alert_id' => $alertId,
            'timestamp' => now()->toISOString(),
            'resolved' => false,
            'created_at' => now()->toISOString(),
        ]);

        // Store alert
        self::storeAlert($alert);

        // Log the alert
        Log::channel('v5_alerts')->{self::getLogLevel($alert['priority'])}('Alert Triggered', $alert);

        // Send notifications based on priority
        self::sendNotifications($alert);

        // Update real-time alert cache
        self::updateRealtimeAlerts($alert);
    }

    /**
     * Store alert for persistence
     */
    private static function storeAlert(array $alert): void
    {
        $key = self::ALERT_CACHE_PREFIX.$alert['alert_id'];
        Cache::put($key, $alert, self::ALERT_TTL * 24); // Keep alerts for 24 hours
    }

    /**
     * Send notifications based on alert priority
     */
    private static function sendNotifications(array $alert): void
    {
        switch ($alert['priority']) {
            case self::PRIORITY_CRITICAL:
                // Send immediate notifications (email, SMS, Slack)
                self::sendImmediateNotification($alert);
                break;
            case self::PRIORITY_HIGH:
                // Send email notification
                self::sendEmailNotification($alert);
                break;
            case self::PRIORITY_MEDIUM:
                // Add to dashboard notifications
                self::addDashboardNotification($alert);
                break;
            case self::PRIORITY_LOW:
                // Just log, no immediate notification
                break;
        }
    }

    /**
     * Update real-time alerts cache
     */
    private static function updateRealtimeAlerts(array $alert): void
    {
        $alerts = Cache::get('v5_realtime_alerts', []);
        array_unshift($alerts, $alert);

        // Keep only last 100 alerts
        $alerts = array_slice($alerts, 0, 100);

        Cache::put('v5_realtime_alerts', $alerts, self::ALERT_TTL);
    }

    /**
     * Generate unique alert ID
     */
    private static function generateAlertId(): string
    {
        return 'alert_'.date('Ymd_His').'_'.substr(uniqid(), -6);
    }

    /**
     * Get log level based on priority
     */
    private static function getLogLevel(string $priority): string
    {
        return match ($priority) {
            self::PRIORITY_CRITICAL => 'critical',
            self::PRIORITY_HIGH => 'error',
            self::PRIORITY_MEDIUM => 'warning',
            self::PRIORITY_LOW => 'info',
            default => 'info',
        };
    }

    /**
     * Send immediate notification for critical alerts
     */
    private static function sendImmediateNotification(array $alert): void
    {
        // Implementation would depend on your notification channels
        // Example: send to Slack, email, SMS, etc.

        Log::channel('v5_alerts')->critical('IMMEDIATE ATTENTION REQUIRED', $alert);
    }

    /**
     * Send email notification
     */
    private static function sendEmailNotification(array $alert): void
    {
        $recipients = self::getEmailRecipients($alert['priority']);

        if (! empty($recipients)) {
            try {
                \Illuminate\Support\Facades\Mail::to($recipients)
                    ->send(new \App\Mail\V5\AlertNotification($alert));

                Log::channel('v5_alerts')->info('Alert email sent', [
                    'alert_id' => $alert['alert_id'],
                    'recipients_count' => count($recipients),
                    'priority' => $alert['priority'],
                ]);
            } catch (\Exception $e) {
                Log::channel('v5_alerts')->error('Failed to send alert email', [
                    'alert_id' => $alert['alert_id'],
                    'error' => $e->getMessage(),
                    'recipients' => $recipients,
                ]);
            }
        }
    }

    /**
     * Get email recipients based on alert priority
     */
    private static function getEmailRecipients(string $priority): array
    {
        $allRecipients = explode(',', env('V5_ALERT_EMAIL_RECIPIENTS', ''));
        $allRecipients = array_filter(array_map('trim', $allRecipients));

        // Filter by priority if configured
        $priorityConfig = config('v5_logging.alerts.notification_channels.email.priorities', ['high', 'critical']);

        if (in_array($priority, $priorityConfig)) {
            return $allRecipients;
        }

        // For critical alerts, also send to emergency contacts
        if ($priority === 'critical') {
            $emergencyContacts = explode(',', env('V5_EMERGENCY_CONTACTS', ''));
            $emergencyContacts = array_filter(array_map('trim', $emergencyContacts));

            return array_unique(array_merge($allRecipients, $emergencyContacts));
        }

        return [];
    }

    /**
     * Add dashboard notification
     */
    private static function addDashboardNotification(array $alert): void
    {
        $notifications = Cache::get('dashboard_notifications', []);
        array_unshift($notifications, $alert);

        // Keep only last 50 notifications
        $notifications = array_slice($notifications, 0, 50);

        Cache::put('dashboard_notifications', $notifications, 3600 * 24);
    }

    /**
     * Get alert configuration
     */
    public static function getAlertConfiguration(): array
    {
        return [
            'thresholds' => self::THRESHOLDS,
            'priorities' => [
                self::PRIORITY_LOW,
                self::PRIORITY_MEDIUM,
                self::PRIORITY_HIGH,
                self::PRIORITY_CRITICAL,
            ],
            'notification_channels' => [
                'email' => true,
                'slack' => true,
                'sms' => false,
                'webhook' => true,
            ],
        ];
    }

    /**
     * Update alert thresholds
     */
    public static function updateThresholds(array $newThresholds): void
    {
        // Implementation to update thresholds
        // This could be stored in database or config
    }
}
