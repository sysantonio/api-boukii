<?php

namespace App\V5\Logging\Formatters;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class V5JsonFormatter extends JsonFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalize($record);

        // Restructure for V5 format
        $v5Record = [
            '@timestamp' => $normalized['datetime'],
            '@version' => '1',
            'level' => strtolower($normalized['level_name']),
            'message' => $normalized['message'],
            'channel' => $normalized['channel'],
            'correlation_id' => $normalized['context']['correlation_id'] ?? null,
            'category' => $normalized['context']['category'] ?? 'general',
            'operation' => $normalized['context']['operation'] ?? null,
            'user_id' => $normalized['context']['user_id'] ?? null,
            'season_id' => $normalized['context']['season_id'] ?? null,
            'school_id' => $normalized['context']['school_id'] ?? null,
            'request' => [
                'method' => $normalized['context']['request_method'] ?? null,
                'url' => $normalized['context']['request_url'] ?? null,
                'ip' => $normalized['context']['user_ip'] ?? null,
                'user_agent' => $normalized['context']['user_agent'] ?? null,
            ],
            'system' => [
                'memory_usage_mb' => $normalized['context']['memory_usage_mb'] ?? null,
                'memory_peak_mb' => $normalized['context']['memory_peak_mb'] ?? null,
                'server_name' => $normalized['context']['server_name'] ?? null,
                'environment' => $normalized['context']['environment'] ?? null,
            ],
            'context' => $this->filterContext($normalized['context'] ?? []),
            'extra' => $normalized['extra'] ?? [],
        ];

        // Remove null values to keep logs clean
        $v5Record = $this->removeNullValues($v5Record);

        return $this->toJson($v5Record, true)."\n";
    }

    /**
     * Filter context to remove system fields that are now in dedicated sections
     */
    private function filterContext(array $context): array
    {
        $systemFields = [
            'correlation_id', 'category', 'operation', 'user_id', 'season_id', 'school_id',
            'request_method', 'request_url', 'user_ip', 'user_agent',
            'memory_usage_mb', 'memory_peak_mb', 'server_name', 'environment',
        ];

        return array_diff_key($context, array_flip($systemFields));
    }

    /**
     * Remove null values recursively
     */
    private function removeNullValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->removeNullValues($value);
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
