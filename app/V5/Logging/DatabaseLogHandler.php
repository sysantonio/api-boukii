<?php

namespace App\V5\Logging;

use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    protected string $table;

    protected int $maxRecords;

    public function __construct($level = Level::Debug, bool $bubble = true, string $table = 'v5_logs', int $maxRecords = 50000)
    {
        parent::__construct($level, $bubble);
        $this->table = $table;
        $this->maxRecords = $maxRecords;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        try {
            $data = $this->formatRecordForDatabase($record);

            DB::table($this->table)->insert($data);

            // Cleanup old records if we're at the limit
            $this->cleanupOldRecords();

        } catch (\Exception $e) {
            // Fallback to file logging if database fails
            \Illuminate\Support\Facades\Log::channel('single')->error('Failed to write to database log', [
                'error' => $e->getMessage(),
                'original_message' => $record->message,
            ]);
        }
    }

    /**
     * Format log record for database storage
     */
    private function formatRecordForDatabase(LogRecord $record): array
    {
        $context = $record->context;
        $extra = $record->extra;

        return [
            'correlation_id' => $context['correlation_id'] ?? null,
            'level' => strtolower($record->level->name),
            'category' => $context['category'] ?? 'general',
            'operation' => $context['operation'] ?? null,
            'message' => $record->message,
            'context' => json_encode($this->filterContext($context)),
            'extra' => json_encode($extra),

            // Request information
            'request_method' => $context['request_method'] ?? null,
            'request_url' => $context['request_url'] ?? null,
            'user_ip' => $context['user_ip'] ?? null,
            'user_agent' => $this->truncate($context['user_agent'] ?? null, 500),

            // User and system context
            'user_id' => $context['user_id'] ?? null,
            'season_id' => $context['season_id'] ?? null,
            'school_id' => $context['school_id'] ?? null,

            // Performance metrics
            'memory_usage_mb' => $context['memory_usage_mb'] ?? null,
            'memory_peak_mb' => $context['memory_peak_mb'] ?? null,
            'response_time_ms' => $context['response_time_ms'] ?? $context['duration_ms'] ?? null,

            // System information
            'server_name' => $context['server_name'] ?? null,
            'environment' => $context['environment'] ?? null,
            'application_version' => $context['application_version'] ?? null,

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Filter context to remove sensitive data and large objects
     */
    private function filterContext(array $context): array
    {
        $filtered = $context;

        // Remove sensitive fields
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key', 'secret',
            'card_number', 'cvv', 'card_token', 'bank_account',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($filtered[$field])) {
                $filtered[$field] = '[REDACTED]';
            }
        }

        // Remove system fields that are stored in dedicated columns
        $systemFields = [
            'correlation_id', 'category', 'operation', 'request_method', 'request_url',
            'user_ip', 'user_agent', 'user_id', 'season_id', 'school_id',
            'memory_usage_mb', 'memory_peak_mb', 'response_time_ms', 'duration_ms',
            'server_name', 'environment', 'application_version',
        ];

        foreach ($systemFields as $field) {
            unset($filtered[$field]);
        }

        // Truncate large text fields
        foreach ($filtered as $key => $value) {
            if (is_string($value) && strlen($value) > 1000) {
                $filtered[$key] = substr($value, 0, 1000).'... [TRUNCATED]';
            }
        }

        return $filtered;
    }

    /**
     * Clean up old records to maintain the maximum limit
     */
    private function cleanupOldRecords(): void
    {
        $count = DB::table($this->table)->count();

        if ($count > $this->maxRecords) {
            $deleteCount = $count - $this->maxRecords + 1000; // Delete extra 1000 for buffer

            DB::table($this->table)
                ->orderBy('id')
                ->limit($deleteCount)
                ->delete();
        }
    }

    /**
     * Truncate string to specified length
     */
    private function truncate(?string $string, int $length): ?string
    {
        if ($string === null || strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length - 3).'...';
    }
}
