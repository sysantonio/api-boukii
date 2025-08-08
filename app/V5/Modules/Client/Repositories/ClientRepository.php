<?php

namespace App\V5\Modules\Client\Repositories;

use App\V5\Modules\Client\Models\Client;
use App\V5\Modules\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

/**
 * V5 Client Repository
 * 
 * Handles all client data access operations with season context enforcement.
 * Provides methods for CRUD operations, filtering, searching, profiling, and statistics.
 */
class ClientRepository
{
    /**
     * Create a new client
     */
    public function create(array $data): Client
    {
        // Ensure school context is provided
        if (!isset($data['school_id'])) {
            throw new \InvalidArgumentException('School context is required for V5 clients');
        }

        // Generate client reference if not provided
        if (!isset($data['client_reference'])) {
            $data['client_reference'] = Client::generateClientReference($data['school_id']);
        }

        return Client::create($data);
    }

    /**
     * Find client by ID with school context
     */
    public function findById(int $id, int $schoolId): ?Client
    {
        return Client::where('id', $id)
                     ->forSchool($schoolId)
                     ->first();
    }

    /**
     * Find client by reference with school context
     */
    public function findByReference(string $reference, int $schoolId): ?Client
    {
        return Client::where('client_reference', $reference)
                     ->forSchool($schoolId)
                     ->first();
    }

    /**
     * Find client by email with school context
     */
    public function findByEmail(string $email, int $schoolId): ?Client
    {
        return Client::where('email', $email)
                     ->forSchool($schoolId)
                     ->first();
    }

    /**
     * Update client
     */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);
        return $client->fresh();
    }

    /**
     * Delete client (soft delete)
     */
    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    /**
     * Get clients with filtering and pagination
     */
    public function getClients(
        int $schoolId,
        array $filters = [],
        int $page = 1,
        int $limit = 20,
        array $with = ['school']
    ): LengthAwarePaginator {
        $query = Client::forSchool($schoolId)
                       ->with($with);

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
                    ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get clients by status
     */
    public function getClientsByStatus(
        int $schoolId,
        string $status,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->withStatus($status)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get clients by profile type
     */
    public function getClientsByProfile(
        int $schoolId,
        string $profileType,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->withProfile($profileType)
                     ->orderBy('total_spent', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get clients by loyalty tier
     */
    public function getClientsByTier(
        int $schoolId,
        string $tier,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->withLoyaltyTier($tier)
                     ->orderBy('total_spent', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get active clients
     */
    public function getActiveClients(
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->active()
                     ->orderBy('last_activity_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get recent clients (created in last N days)
     */
    public function getRecentClients(
        int $schoolId,
        int $days = 30,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->recent($days)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get VIP clients
     */
    public function getVipClients(
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->where(function ($query) {
                         $query->withProfile(Client::PROFILE_VIP)
                               ->orWhere('loyalty_tier', Client::TIER_PLATINUM)
                               ->orWhere('total_spent', '>=', 2000);
                     })
                     ->orderBy('total_spent', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get at-risk clients (high cancellation rate, inactive, etc.)
     */
    public function getRiskClients(
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->where(function ($query) {
                         $query->withProfile(Client::PROFILE_RISK)
                               ->orWhere('status', Client::STATUS_BLOCKED)
                               ->orWhere('last_activity_at', '<', now()->subMonths(6));
                     })
                     ->orderBy('last_activity_at', 'asc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get clients with bookings in specific season
     */
    public function getClientsWithSeasonBookings(
        int $schoolId,
        int $seasonId,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->withSeasonBookings($seasonId)
                     ->with(['seasonBookings' => function ($query) use ($seasonId) {
                         $query->where('season_id', $seasonId);
                     }])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get client bookings across all seasons or specific season
     */
    public function getClientBookings(
        int $clientId,
        int $schoolId,
        ?int $seasonId = null,
        int $limit = 20
    ): Collection {
        $client = $this->findById($clientId, $schoolId);
        
        if (!$client) {
            return collect();
        }

        $query = $seasonId 
            ? $client->seasonBookings($seasonId)
            : $client->bookings();

        return $query->with(['course', 'monitor', 'extras', 'equipment', 'payments'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Search clients by query
     */
    public function searchClients(
        string $query,
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Client::forSchool($schoolId)
                     ->search($query)
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get client statistics for dashboard
     */
    public function getClientStats(int $schoolId): array
    {
        $baseQuery = Client::forSchool($schoolId);

        return [
            'total_clients' => (clone $baseQuery)->count(),
            'active_clients' => (clone $baseQuery)->active()->count(),
            'inactive_clients' => (clone $baseQuery)->withStatus(Client::STATUS_INACTIVE)->count(),
            'blocked_clients' => (clone $baseQuery)->withStatus(Client::STATUS_BLOCKED)->count(),
            'pending_clients' => (clone $baseQuery)->withStatus(Client::STATUS_PENDING)->count(),
            
            // Profile distribution
            'new_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_NEW)->count(),
            'regular_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_REGULAR)->count(),
            'vip_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_VIP)->count(),
            'frequent_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_FREQUENT)->count(),
            'seasonal_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_SEASONAL)->count(),
            'risk_clients' => (clone $baseQuery)->withProfile(Client::PROFILE_RISK)->count(),
            
            // Loyalty tiers
            'bronze_clients' => (clone $baseQuery)->withLoyaltyTier(Client::TIER_BRONZE)->count(),
            'silver_clients' => (clone $baseQuery)->withLoyaltyTier(Client::TIER_SILVER)->count(),
            'gold_clients' => (clone $baseQuery)->withLoyaltyTier(Client::TIER_GOLD)->count(),
            'platinum_clients' => (clone $baseQuery)->withLoyaltyTier(Client::TIER_PLATINUM)->count(),
            
            // Time-based stats
            'new_this_month' => (clone $baseQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
            'new_this_week' => (clone $baseQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'new_today' => (clone $baseQuery)->whereDate('created_at', now())->count(),
            
            // Financial stats
            'total_spent' => (clone $baseQuery)->sum('total_spent'),
            'average_spent' => (clone $baseQuery)->avg('total_spent'),
            'total_bookings' => (clone $baseQuery)->sum('total_bookings'),
            'average_bookings' => (clone $baseQuery)->avg('total_bookings'),
            'average_rating' => (clone $baseQuery)->avg('average_rating'),
        ];
    }

    /**
     * Get clients requiring attention (inactive, risk, etc.)
     */
    public function getClientsRequiringAttention(int $schoolId): array
    {
        $now = now();
        
        return [
            'inactive_clients' => Client::forSchool($schoolId)
                                        ->active()
                                        ->where('last_activity_at', '<', $now->subMonths(3))
                                        ->orWhere('last_booking_at', '<', $now->subMonths(6))
                                        ->limit(50)
                                        ->get(),
            
            'risk_clients' => Client::forSchool($schoolId)
                                    ->withProfile(Client::PROFILE_RISK)
                                    ->limit(20)
                                    ->get(),
            
            'blocked_clients' => Client::forSchool($schoolId)
                                       ->withStatus(Client::STATUS_BLOCKED)
                                       ->limit(20)
                                       ->get(),
            
            'incomplete_profiles' => Client::forSchool($schoolId)
                                           ->active()
                                           ->where(function ($query) {
                                               $query->whereNull('email')
                                                     ->orWhereNull('phone')
                                                     ->orWhereNull('date_of_birth');
                                           })
                                           ->limit(30)
                                           ->get(),
        ];
    }

    /**
     * Find potential duplicate clients
     */
    public function findPotentialDuplicates(
        int $schoolId,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null,
        ?int $excludeId = null
    ): Collection {
        $query = Client::forSchool($schoolId)
                       ->where('first_name', 'LIKE', "%{$firstName}%")
                       ->where('last_name', 'LIKE', "%{$lastName}%");

        if ($email) {
            $query->orWhere('email', $email);
        }

        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->limit(10)->get();
    }

    /**
     * Update client booking statistics
     */
    public function updateBookingStats(int $clientId, int $schoolId): void
    {
        $client = $this->findById($clientId, $schoolId);
        
        if ($client) {
            $client->updateBookingStats();
        }
    }

    /**
     * Bulk update client profiles
     */
    public function bulkUpdateProfiles(int $schoolId): int
    {
        $clients = Client::forSchool($schoolId)->get();
        $updated = 0;

        foreach ($clients as $client) {
            $oldProfile = $client->profile_type;
            $client->updateProfileType();
            
            if ($client->profile_type !== $oldProfile) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Bulk update loyalty tiers
     */
    public function bulkUpdateLoyaltyTiers(int $schoolId): int
    {
        $clients = Client::forSchool($schoolId)->get();
        $updated = 0;

        foreach ($clients as $client) {
            $oldTier = $client->loyalty_tier;
            $client->updateLoyaltyTier();
            
            if ($client->loyalty_tier !== $oldTier) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Get client ranking by spending
     */
    public function getClientRanking(
        int $schoolId,
        string $period = 'all', // 'all', 'year', 'season', 'month'
        int $limit = 100
    ): Collection {
        $query = Client::forSchool($schoolId);

        // Apply period filter if needed
        if ($period === 'year') {
            $query->whereHas('bookings', function ($bookingQuery) {
                $bookingQuery->whereYear('created_at', now()->year);
            });
        } elseif ($period === 'month') {
            $query->whereHas('bookings', function ($bookingQuery) {
                $bookingQuery->whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year);
            });
        }

        return $query->where('total_spent', '>', 0)
                     ->orderBy('total_spent', 'desc')
                     ->orderBy('total_bookings', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Apply filters to client query
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (isset($filters['profile_type'])) {
            $query->withProfile($filters['profile_type']);
        }

        if (isset($filters['loyalty_tier'])) {
            $query->withLoyaltyTier($filters['loyalty_tier']);
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        if (isset($filters['min_spent'])) {
            $query->where('total_spent', '>=', $filters['min_spent']);
        }

        if (isset($filters['max_spent'])) {
            $query->where('total_spent', '<=', $filters['max_spent']);
        }

        if (isset($filters['min_bookings'])) {
            $query->where('total_bookings', '>=', $filters['min_bookings']);
        }

        if (isset($filters['max_bookings'])) {
            $query->where('total_bookings', '<=', $filters['max_bookings']);
        }

        if (isset($filters['has_email'])) {
            if ($filters['has_email']) {
                $query->whereNotNull('email');
            } else {
                $query->whereNull('email');
            }
        }

        if (isset($filters['has_phone'])) {
            if ($filters['has_phone']) {
                $query->whereNotNull('phone');
            } else {
                $query->whereNull('phone');
            }
        }

        if (isset($filters['age_min'])) {
            $maxBirthDate = now()->subYears($filters['age_min'])->toDateString();
            $query->where('date_of_birth', '<=', $maxBirthDate);
        }

        if (isset($filters['age_max'])) {
            $minBirthDate = now()->subYears($filters['age_max'])->toDateString();
            $query->where('date_of_birth', '>=', $minBirthDate);
        }

        if (isset($filters['nationality'])) {
            $query->where('nationality', $filters['nationality']);
        }

        if (isset($filters['preferred_language'])) {
            $query->where('preferred_language', $filters['preferred_language']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['has_season_bookings']) && isset($filters['season_id'])) {
            $query->withSeasonBookings($filters['season_id']);
        }

        if (isset($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }
    }
}