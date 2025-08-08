<?php

namespace App\V5\Integrations;

use Illuminate\Support\Facades\Log;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

class TelescopeIntegration
{
    /**
     * Register V5 custom entries with Telescope
     */
    public static function registerV5Entries(): void
    {
        // Add custom entry types for V5
        Telescope::tag(function (IncomingEntry $entry) {
            return self::getV5Tags($entry);
        });

        // Filter entries for V5 specific data
        Telescope::filter(function (IncomingEntry $entry) {
            return self::shouldRecordEntry($entry);
        });
    }

    /**
     * Create custom V5 entry in Telescope
     */
    public static function recordV5Entry(string $type, array $data): void
    {
        if (! class_exists('\Laravel\Telescope\Telescope')) {
            return; // Telescope not installed
        }

        try {
            Telescope::recordEntry(
                $type,
                $data + [
                    'v5_correlation_id' => \App\V5\Logging\V5Logger::getCorrelationId(),
                    'v5_timestamp' => now()->toISOString(),
                    'v5_module' => 'boukii_v5',
                ]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to record V5 entry in Telescope', [
                'error' => $e->getMessage(),
                'type' => $type,
            ]);
        }
    }

    /**
     * Record payment operation in Telescope
     */
    public static function recordPaymentOperation(string $operation, array $paymentData): void
    {
        self::recordV5Entry('v5_payment', [
            'operation' => $operation,
            'payment_id' => $paymentData['payment_id'] ?? null,
            'booking_id' => $paymentData['booking_id'] ?? null,
            'amount' => $paymentData['amount'] ?? null,
            'currency' => $paymentData['currency'] ?? 'CHF',
            'gateway' => $paymentData['gateway'] ?? null,
            'status' => $paymentData['status'] ?? null,
            'duration' => $paymentData['duration_ms'] ?? null,
        ]);
    }

    /**
     * Record booking operation in Telescope
     */
    public static function recordBookingOperation(string $operation, array $bookingData): void
    {
        self::recordV5Entry('v5_booking', [
            'operation' => $operation,
            'booking_id' => $bookingData['booking_id'] ?? null,
            'course_id' => $bookingData['course_id'] ?? null,
            'client_id' => $bookingData['client_id'] ?? null,
            'participants_count' => $bookingData['participants_count'] ?? null,
            'total_price' => $bookingData['total_price'] ?? null,
            'status' => $bookingData['status'] ?? null,
        ]);
    }

    /**
     * Record season operation in Telescope
     */
    public static function recordSeasonOperation(string $operation, array $seasonData): void
    {
        self::recordV5Entry('v5_season', [
            'operation' => $operation,
            'season_id' => $seasonData['season_id'] ?? null,
            'school_id' => $seasonData['school_id'] ?? null,
            'season_name' => $seasonData['name'] ?? null,
            'is_active' => $seasonData['is_active'] ?? null,
            'start_date' => $seasonData['start_date'] ?? null,
            'end_date' => $seasonData['end_date'] ?? null,
        ]);
    }

    /**
     * Get V5 specific tags for Telescope entries
     */
    private static function getV5Tags(IncomingEntry $entry): array
    {
        $tags = [];

        // Add V5 correlation ID as tag
        if (isset($entry->content['v5_correlation_id'])) {
            $tags[] = 'v5:correlation:'.$entry->content['v5_correlation_id'];
        }

        // Add V5 module tag
        if (isset($entry->content['v5_module'])) {
            $tags[] = 'v5:module:'.$entry->content['v5_module'];
        }

        // Add operation type tags
        if (str_starts_with($entry->type, 'v5_')) {
            $tags[] = 'v5:type:'.substr($entry->type, 3);

            if (isset($entry->content['operation'])) {
                $tags[] = 'v5:operation:'.$entry->content['operation'];
            }
        }

        // Add entity-specific tags
        if (isset($entry->content['payment_id'])) {
            $tags[] = 'v5:payment:'.$entry->content['payment_id'];
        }

        if (isset($entry->content['booking_id'])) {
            $tags[] = 'v5:booking:'.$entry->content['booking_id'];
        }

        if (isset($entry->content['season_id'])) {
            $tags[] = 'v5:season:'.$entry->content['season_id'];
        }

        if (isset($entry->content['user_id'])) {
            $tags[] = 'v5:user:'.$entry->content['user_id'];
        }

        return $tags;
    }

    /**
     * Determine if entry should be recorded in Telescope
     */
    private static function shouldRecordEntry(IncomingEntry $entry): bool
    {
        // Always record V5 specific entries
        if (str_starts_with($entry->type, 'v5_')) {
            return true;
        }

        // Record requests that involve V5 APIs
        if ($entry->type === EntryType::REQUEST) {
            $uri = $entry->content['uri'] ?? '';
            if (str_contains($uri, '/api/v5/') || str_contains($uri, '/v5/')) {
                return true;
            }
        }

        // Record queries that involve V5 tables
        if ($entry->type === EntryType::QUERY) {
            $sql = strtolower($entry->content['sql'] ?? '');
            if (str_contains($sql, 'seasons') ||
                str_contains($sql, 'user_season_roles') ||
                str_contains($sql, 'season_snapshots')) {
                return true;
            }
        }

        // Record jobs related to V5
        if ($entry->type === EntryType::JOB) {
            $name = $entry->content['name'] ?? '';
            if (str_contains($name, 'V5\\') || str_contains($name, 'v5')) {
                return true;
            }
        }

        // Default filtering rules
        return Telescope::isRecording();
    }

    /**
     * Add V5 dashboard link to Telescope
     */
    public static function addV5DashboardLink(): string
    {
        return route('v5.logs.dashboard');
    }

    /**
     * Get V5 entries from Telescope
     */
    public static function getV5Entries(array $filters = []): array
    {
        if (! class_exists('\Laravel\Telescope\Storage\EntryModel')) {
            return [];
        }

        $query = \Laravel\Telescope\Storage\EntryModel::query()
            ->where('type', 'like', 'v5_%')
            ->orderBy('sequence', 'desc');

        // Apply filters
        if (! empty($filters['correlation_id'])) {
            $query->whereJsonContains('content->v5_correlation_id', $filters['correlation_id']);
        }

        if (! empty($filters['operation'])) {
            $query->whereJsonContains('content->operation', $filters['operation']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->limit(100)->get()->toArray();
    }

    /**
     * Create Telescope watcher for V5 operations
     */
    public static function createV5Watcher(): array
    {
        return [
            'enabled' => env('V5_TELESCOPE_WATCHER', true),
            'ignore_paths' => [
                // Add paths to ignore if needed
            ],
            'ignore_commands' => [
                // Add commands to ignore if needed
            ],
        ];
    }
}
