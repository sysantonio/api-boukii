<?php

return [
    /*
    |--------------------------------------------------------------------------
    | V5 Logging Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the logging setup specifically for V5 modules
    | with enhanced structure, correlation tracking, and alerting capabilities.
    |
    */

    'channels' => [
        'v5_enterprise' => [
            'driver' => 'stack',
            'channels' => ['v5_daily', 'v5_database', 'v5_elasticsearch'],
            'ignore_exceptions' => false,
        ],

        'v5_daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/v5.log'),
            'level' => env('V5_LOG_LEVEL', 'debug'),
            'days' => 30,
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\V5JsonFormatter::class,
        ],

        'v5_payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/payments.log'),
            'level' => env('V5_PAYMENT_LOG_LEVEL', 'info'),
            'days' => 90, // Keep payment logs longer for compliance
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\PaymentFormatter::class,
        ],

        'v5_financial' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/financial.log'),
            'level' => 'info',
            'days' => 365, // Keep financial logs for 1 year
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\FinancialFormatter::class,
        ],

        'v5_security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/security.log'),
            'level' => 'warning',
            'days' => 180, // Keep security logs for 6 months
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\SecurityFormatter::class,
        ],

        'v5_alerts' => [
            'driver' => 'stack',
            'channels' => ['v5_alerts_file', 'v5_alerts_database'],
            'ignore_exceptions' => false,
        ],

        'v5_alerts_file' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/alerts.log'),
            'level' => 'info',
            'days' => 60,
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\AlertFormatter::class,
        ],

        'v5_alerts_database' => [
            'driver' => 'custom',
            'via' => \App\V5\Logging\DatabaseLogHandler::class,
            'level' => 'warning', // Only store warnings and above in DB
            'table' => 'v5_alert_logs',
        ],

        'v5_performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/v5/performance.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
            'formatter' => \App\V5\Logging\Formatters\PerformanceFormatter::class,
        ],

        'v5_database' => [
            'driver' => 'custom',
            'via' => \App\V5\Logging\DatabaseLogHandler::class,
            'level' => env('V5_DB_LOG_LEVEL', 'error'),
            'table' => 'v5_logs',
            'max_records' => 50000, // Rotate after 50k records
        ],

        'v5_elasticsearch' => [
            'driver' => 'custom', 
            'via' => \App\V5\Logging\ElasticsearchLogHandler::class,
            'level' => env('V5_ES_LOG_LEVEL', 'info'),
            'index' => 'v5-logs',
            'hosts' => env('ELASTICSEARCH_HOSTS', 'localhost:9200'),
        ],

        'v5_webhook' => [
            'driver' => 'custom',
            'via' => \App\V5\Logging\WebhookLogHandler::class,
            'level' => 'critical',
            'webhook_url' => env('V5_WEBHOOK_URL'),
            'timeout' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Processing Configuration
    |--------------------------------------------------------------------------
    */

    'processing' => [
        'correlation_tracking' => [
            'enabled' => env('V5_CORRELATION_TRACKING', true),
            'cache_ttl' => 3600, // 1 hour
            'max_breadcrumbs' => 50,
        ],

        'alert_processing' => [
            'enabled' => env('V5_ALERT_PROCESSING', true),
            'batch_size' => 100,
            'processing_interval' => 60, // seconds
        ],

        'log_aggregation' => [
            'enabled' => env('V5_LOG_AGGREGATION', true),
            'batch_size' => 1000,
            'aggregation_interval' => 300, // 5 minutes
        ],

        'data_retention' => [
            'enabled' => true,
            'cleanup_interval' => '0 2 * * *', // Daily at 2 AM
            'retention_policies' => [
                'debug' => 7, // days
                'info' => 30,
                'warning' => 90,
                'error' => 180,
                'critical' => 365,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'enabled' => env('V5_ALERTS_ENABLED', true),
        
        'thresholds' => [
            'error_rate_percent' => env('V5_ALERT_ERROR_RATE', 5.0),
            'payment_failure_rate_percent' => env('V5_ALERT_PAYMENT_FAILURE_RATE', 10.0),
            'response_time_ms' => env('V5_ALERT_RESPONSE_TIME', 2000),
            'memory_usage_percent' => env('V5_ALERT_MEMORY_USAGE', 85),
            'disk_usage_percent' => env('V5_ALERT_DISK_USAGE', 90),
            'fraud_score' => env('V5_ALERT_FRAUD_SCORE', 80),
        ],

        'notification_channels' => [
            'email' => [
                'enabled' => env('V5_EMAIL_ALERTS', true),
                'recipients' => explode(',', env('V5_ALERT_EMAIL_RECIPIENTS', '')),
                'priorities' => ['high', 'critical'],
            ],
            'slack' => [
                'enabled' => env('V5_SLACK_ALERTS', false),
                'webhook_url' => env('V5_SLACK_WEBHOOK_URL'),
                'channel' => env('V5_SLACK_CHANNEL', '#alerts'),
                'priorities' => ['critical'],
            ],
            'webhook' => [
                'enabled' => env('V5_WEBHOOK_ALERTS', false),
                'url' => env('V5_ALERT_WEBHOOK_URL'),
                'priorities' => ['high', 'critical'],
            ],
        ],

        'rate_limiting' => [
            'enabled' => true,
            'max_alerts_per_hour' => 50,
            'duplicate_alert_window' => 300, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search and Analysis Configuration
    |--------------------------------------------------------------------------
    */

    'search' => [
        'elasticsearch' => [
            'enabled' => env('V5_ELASTICSEARCH_ENABLED', false),
            'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
            'index_prefix' => 'v5-logs',
            'max_results' => 10000,
            'timeout' => 30,
        ],

        'database' => [
            'enabled' => true,
            'table' => 'v5_logs',
            'max_results' => 1000,
            'full_text_search' => env('DB_CONNECTION') === 'mysql',
        ],

        'file_search' => [
            'enabled' => true,
            'search_command' => 'grep', // or 'rg' for ripgrep
            'max_results' => 500,
            'timeout' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    */

    'export' => [
        'enabled' => true,
        'formats' => ['csv', 'excel', 'json'],
        'max_records' => 10000,
        'storage_disk' => 'local',
        'storage_path' => 'exports/logs',
        'cleanup_after_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'enabled' => env('V5_LOG_DASHBOARD', true),
        'cache_ttl' => 300, // 5 minutes
        'real_time_updates' => env('V5_REALTIME_DASHBOARD', true),
        'max_correlation_display' => 100,
        'performance_chart_points' => 24, // hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance and Privacy
    |--------------------------------------------------------------------------
    */

    'compliance' => [
        'gdpr' => [
            'enabled' => true,
            'anonymize_user_data' => true,
            'data_retention_days' => 365,
        ],
        
        'pci_dss' => [
            'enabled' => true,
            'mask_card_numbers' => true,
            'redact_sensitive_fields' => true,
        ],
        
        'encryption' => [
            'enabled' => env('V5_LOG_ENCRYPTION', false),
            'algorithm' => 'AES-256-CBC',
            'key' => env('V5_LOG_ENCRYPTION_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Fields Configuration
    |--------------------------------------------------------------------------
    | 
    | Additional sensitive fields that should be masked in logs beyond the
    | default ones defined in V5Logger. These will be merged with the defaults.
    |
    */

    'sensitive_fields' => [
        'ssn', 'social_security_number', 'tax_id', 'bank_routing_number',
        'account_number', 'swift_code', 'vat_number', 'personal_id',
        'passport_number', 'driver_license', 'birth_date', 'phone_verified',
        'email_verified_token', 'reset_token', 'verification_code',
        'two_factor_secret', 'backup_codes', 'webhook_secret',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enhanced Monitoring Configuration
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'performance_thresholds' => [
            'slow_request_ms' => env('V5_SLOW_REQUEST_THRESHOLD', 1000),
            'very_slow_request_ms' => env('V5_VERY_SLOW_REQUEST_THRESHOLD', 2000),
            'critical_request_ms' => env('V5_CRITICAL_REQUEST_THRESHOLD', 5000),
            'slow_query_ms' => env('V5_SLOW_QUERY_THRESHOLD', 1000),
            'very_slow_query_ms' => env('V5_VERY_SLOW_QUERY_THRESHOLD', 5000),
        ],

        'memory_thresholds' => [
            'warning_mb' => env('V5_MEMORY_WARNING_THRESHOLD', 128),
            'critical_mb' => env('V5_MEMORY_CRITICAL_THRESHOLD', 256),
        ],

        'response_size_thresholds' => [
            'large_response_bytes' => env('V5_LARGE_RESPONSE_THRESHOLD', 1048576), // 1MB
            'huge_response_bytes' => env('V5_HUGE_RESPONSE_THRESHOLD', 5242880),   // 5MB
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Level Mapping
    |--------------------------------------------------------------------------
    */

    'level_mapping' => [
        'auth_events' => [
            'login_success' => 'info',
            'login_failed' => 'warning',
            'logout' => 'info',
            'logout_forced' => 'warning',
            'token_expired' => 'warning',
            'account_locked' => 'error',
            'suspicious_activity' => 'error',
            'brute_force_attempt' => 'error',
            'password_reset_requested' => 'info',
            'password_reset_completed' => 'info',
            'two_factor_enabled' => 'info',
            'two_factor_disabled' => 'warning',
        ],

        'business_events' => [
            'booking_created' => 'info',
            'booking_updated' => 'info',
            'booking_cancelled' => 'warning',
            'payment_processed' => 'info',
            'payment_failed' => 'error',
            'refund_processed' => 'warning',
            'season_activated' => 'info',
            'season_closed' => 'warning',
        ],
    ],
];