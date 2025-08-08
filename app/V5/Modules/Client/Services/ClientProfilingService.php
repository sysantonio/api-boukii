<?php

namespace App\V5\Modules\Client\Services;

use App\V5\Modules\Client\Models\Client;
use App\V5\Modules\Client\Repositories\ClientRepository;
use App\V5\Logging\V5Logger;
use Carbon\Carbon;

/**
 * V5 Client Profiling Service
 * 
 * Handles automatic client profiling and categorization based on behavior analysis.
 * Provides advanced algorithms for client segmentation and risk assessment.
 */
class ClientProfilingService
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {}

    /**
     * Analyze and update client profile
     */
    public function analyzeClientProfile(Client $client): string
    {
        V5Logger::debug('Analyzing client profile', [
            'client_id' => $client->id,
            'current_profile' => $client->profile_type,
            'total_bookings' => $client->total_bookings,
            'total_spent' => $client->total_spent,
        ]);

        $profile = $this->calculateProfile($client);

        if ($client->profile_type !== $profile) {
            V5Logger::info('Client profile changed', [
                'client_id' => $client->id,
                'old_profile' => $client->profile_type,
                'new_profile' => $profile,
            ]);
        }

        return $profile;
    }

    /**
     * Calculate client profile based on comprehensive behavior analysis
     */
    private function calculateProfile(Client $client): string
    {
        // Risk profile check (highest priority)
        if ($this->isRiskClient($client)) {
            return Client::PROFILE_RISK;
        }

        // VIP profile check
        if ($this->isVipClient($client)) {
            return Client::PROFILE_VIP;
        }

        // Frequent profile check
        if ($this->isFrequentClient($client)) {
            return Client::PROFILE_FREQUENT;
        }

        // Seasonal profile check
        if ($this->isSeasonalClient($client)) {
            return Client::PROFILE_SEASONAL;
        }

        // Regular profile check
        if ($this->isRegularClient($client)) {
            return Client::PROFILE_REGULAR;
        }

        // Default to new
        return Client::PROFILE_NEW;
    }

    /**
     * Check if client is at risk
     */
    private function isRiskClient(Client $client): bool
    {
        // High cancellation rate
        $cancellationRate = $this->calculateCancellationRate($client);
        if ($cancellationRate > 0.3) { // More than 30%
            return true;
        }

        // Multiple payment failures
        $paymentFailures = $this->getPaymentFailures($client);
        if ($paymentFailures > 2) {
            return true;
        }

        // Blocked status
        if ($client->status === Client::STATUS_BLOCKED) {
            return true;
        }

        // Multiple complaints or negative feedback
        if ($client->average_rating < 2.0 && $client->total_bookings >= 3) {
            return true;
        }

        // Long period of inactivity with previous bookings
        if ($client->total_bookings > 0 && 
            $client->last_activity_at && 
            $client->last_activity_at->isBefore(now()->subMonths(12))) {
            return true;
        }

        return false;
    }

    /**
     * Check if client is VIP
     */
    private function isVipClient(Client $client): bool
    {
        // High spending threshold
        if ($client->total_spent >= 2000) {
            return true;
        }

        // Platinum loyalty tier
        if ($client->loyalty_tier === Client::TIER_PLATINUM) {
            return true;
        }

        // High-value recent bookings
        $recentHighValueBookings = $this->getRecentHighValueBookings($client);
        if ($recentHighValueBookings >= 3) {
            return true;
        }

        // Excellent rating with significant bookings
        if ($client->average_rating >= 4.8 && $client->total_bookings >= 5 && $client->total_spent >= 1000) {
            return true;
        }

        return false;
    }

    /**
     * Check if client is frequent
     */
    private function isFrequentClient(Client $client): bool
    {
        // High booking count with reasonable tenure
        if ($client->total_bookings >= 10 && 
            $client->created_at->isBefore(now()->subMonths(6))) {
            return true;
        }

        // Regular booking pattern (multiple bookings in recent months)
        $recentBookings = $this->getRecentBookingsCount($client, 3); // Last 3 months
        if ($recentBookings >= 3 && $client->total_bookings >= 8) {
            return true;
        }

        // High booking frequency relative to tenure
        $monthsSinceCreation = $client->created_at->diffInMonths(now());
        if ($monthsSinceCreation > 0) {
            $bookingsPerMonth = $client->total_bookings / $monthsSinceCreation;
            if ($bookingsPerMonth >= 1.5 && $client->total_bookings >= 6) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if client is seasonal
     */
    private function isSeasonalClient(Client $client): bool
    {
        // This would require more complex analysis of booking patterns across seasons
        // For now, simplified logic based on booking concentration
        
        if ($client->total_bookings < 3) {
            return false;
        }

        // Check if bookings are concentrated in specific periods
        $seasonalPattern = $this->analyzeSeasonalPattern($client);
        
        return $seasonalPattern['is_seasonal'] ?? false;
    }

    /**
     * Check if client is regular
     */
    private function isRegularClient(Client $client): bool
    {
        // Minimum booking count with reasonable tenure
        if ($client->total_bookings >= 3 && 
            $client->created_at->isBefore(now()->subMonths(2))) {
            return true;
        }

        // Steady spending pattern
        if ($client->total_spent >= 300 && 
            $client->total_bookings >= 2 && 
            $client->created_at->isBefore(now()->subMonths(1))) {
            return true;
        }

        return false;
    }

    /**
     * Calculate client cancellation rate
     */
    private function calculateCancellationRate(Client $client): float
    {
        if ($client->total_bookings === 0) {
            return 0;
        }

        $cancelledBookings = $client->bookings()
                                   ->where('status', 'cancelled')
                                   ->count();

        return $cancelledBookings / $client->total_bookings;
    }

    /**
     * Get payment failures count
     */
    private function getPaymentFailures(Client $client): int
    {
        return $client->bookings()
                     ->whereHas('payments', function ($query) {
                         $query->where('status', 'failed');
                     })
                     ->count();
    }

    /**
     * Get recent high-value bookings count
     */
    private function getRecentHighValueBookings(Client $client, int $months = 6): int
    {
        return $client->bookings()
                     ->where('created_at', '>=', now()->subMonths($months))
                     ->where('total_price', '>=', 300) // High-value threshold
                     ->count();
    }

    /**
     * Get recent bookings count
     */
    private function getRecentBookingsCount(Client $client, int $months = 3): int
    {
        return $client->bookings()
                     ->where('created_at', '>=', now()->subMonths($months))
                     ->count();
    }

    /**
     * Analyze seasonal booking pattern
     */
    private function analyzeSeasonalPattern(Client $client): array
    {
        $bookings = $client->bookings()
                          ->selectRaw('MONTH(start_date) as month, COUNT(*) as count')
                          ->groupBy('month')
                          ->get()
                          ->keyBy('month');

        if ($bookings->isEmpty()) {
            return ['is_seasonal' => false];
        }

        // Calculate concentration - if more than 70% of bookings are in 3 or fewer months
        $totalBookings = $bookings->sum('count');
        $monthsWithBookings = $bookings->count();
        
        if ($monthsWithBookings <= 3) {
            return [
                'is_seasonal' => true,
                'peak_months' => $bookings->keys()->toArray(),
                'concentration' => 1.0,
            ];
        }

        // Sort by booking count and check top 3 months
        $topMonths = $bookings->sortByDesc('count')->take(3);
        $topMonthsBookings = $topMonths->sum('count');
        $concentration = $topMonthsBookings / $totalBookings;

        return [
            'is_seasonal' => $concentration >= 0.7,
            'peak_months' => $topMonths->keys()->toArray(),
            'concentration' => $concentration,
        ];
    }

    /**
     * Update loyalty tier based on spending
     */
    public function calculateLoyaltyTier(Client $client): string
    {
        V5Logger::debug('Calculating loyalty tier', [
            'client_id' => $client->id,
            'current_tier' => $client->loyalty_tier,
            'total_spent' => $client->total_spent,
        ]);

        $tier = match (true) {
            $client->total_spent >= 5000 => Client::TIER_PLATINUM,
            $client->total_spent >= 2000 => Client::TIER_GOLD,
            $client->total_spent >= 500 => Client::TIER_SILVER,
            default => Client::TIER_BRONZE
        };

        if ($client->loyalty_tier !== $tier) {
            V5Logger::info('Client loyalty tier changed', [
                'client_id' => $client->id,
                'old_tier' => $client->loyalty_tier,
                'new_tier' => $tier,
                'total_spent' => $client->total_spent,
            ]);
        }

        return $tier;
    }

    /**
     * Generate client insights for dashboard
     */
    public function generateClientInsights(Client $client): array
    {
        $insights = [];

        // Profile analysis
        $insights['profile_analysis'] = [
            'current_profile' => $client->profile_type,
            'profile_confidence' => $this->calculateProfileConfidence($client),
            'profile_factors' => $this->getProfileFactors($client),
        ];

        // Risk assessment
        $insights['risk_assessment'] = [
            'risk_score' => $this->calculateRiskScore($client),
            'risk_factors' => $this->getRiskFactors($client),
            'cancellation_rate' => $this->calculateCancellationRate($client),
        ];

        // Value assessment
        $insights['value_assessment'] = [
            'lifetime_value' => $client->total_spent,
            'average_booking_value' => $client->total_bookings > 0 ? ($client->total_spent / $client->total_bookings) : 0,
            'value_trend' => $this->calculateValueTrend($client),
            'potential_value' => $this->calculatePotentialValue($client),
        ];

        // Behavioral insights
        $insights['behavioral_insights'] = [
            'booking_frequency' => $this->calculateBookingFrequency($client),
            'seasonal_pattern' => $this->analyzeSeasonalPattern($client),
            'preferred_services' => $this->getPreferredServices($client),
            'communication_preferences' => $this->getCommunicationPreferences($client),
        ];

        // Recommendations
        $insights['recommendations'] = $this->generateRecommendations($client);

        return $insights;
    }

    /**
     * Calculate profile confidence score
     */
    private function calculateProfileConfidence(Client $client): float
    {
        $factors = 0;
        $confidence = 0;

        // More bookings = higher confidence
        if ($client->total_bookings >= 5) {
            $confidence += 0.3;
            $factors++;
        } elseif ($client->total_bookings >= 2) {
            $confidence += 0.2;
            $factors++;
        }

        // Longer tenure = higher confidence
        $monthsSinceCreation = $client->created_at->diffInMonths(now());
        if ($monthsSinceCreation >= 6) {
            $confidence += 0.2;
            $factors++;
        } elseif ($monthsSinceCreation >= 2) {
            $confidence += 0.1;
            $factors++;
        }

        // Recent activity = higher confidence
        if ($client->last_activity_at && $client->last_activity_at->isAfter(now()->subMonths(3))) {
            $confidence += 0.2;
            $factors++;
        }

        // Spending data = higher confidence
        if ($client->total_spent > 0) {
            $confidence += 0.3;
            $factors++;
        }

        return $factors > 0 ? min(1.0, $confidence) : 0.1;
    }

    /**
     * Get factors that influenced profile classification
     */
    private function getProfileFactors(Client $client): array
    {
        $factors = [];

        if ($client->profile_type === Client::PROFILE_VIP) {
            if ($client->total_spent >= 2000) $factors[] = 'High lifetime spending';
            if ($client->loyalty_tier === Client::TIER_PLATINUM) $factors[] = 'Platinum loyalty tier';
            if ($client->average_rating >= 4.8) $factors[] = 'Excellent ratings';
        } elseif ($client->profile_type === Client::PROFILE_RISK) {
            if ($this->calculateCancellationRate($client) > 0.3) $factors[] = 'High cancellation rate';
            if ($client->status === Client::STATUS_BLOCKED) $factors[] = 'Blocked account';
            if ($client->average_rating < 2.0) $factors[] = 'Poor ratings';
        } elseif ($client->profile_type === Client::PROFILE_FREQUENT) {
            if ($client->total_bookings >= 10) $factors[] = 'High booking count';
            $recentBookings = $this->getRecentBookingsCount($client, 3);
            if ($recentBookings >= 3) $factors[] = 'Regular recent activity';
        }

        return $factors;
    }

    /**
     * Calculate overall risk score (0-100)
     */
    private function calculateRiskScore(Client $client): int
    {
        $score = 0;

        // Cancellation rate (0-30 points)
        $cancellationRate = $this->calculateCancellationRate($client);
        $score += min(30, $cancellationRate * 100);

        // Payment failures (0-20 points)
        $paymentFailures = $this->getPaymentFailures($client);
        $score += min(20, $paymentFailures * 7);

        // Account status (0-25 points)
        if ($client->status === Client::STATUS_BLOCKED) $score += 25;
        elseif ($client->status === Client::STATUS_INACTIVE) $score += 15;

        // Rating (0-15 points)
        if ($client->average_rating < 2.0 && $client->total_bookings >= 3) $score += 15;
        elseif ($client->average_rating < 3.0 && $client->total_bookings >= 3) $score += 10;

        // Inactivity (0-10 points)
        if ($client->last_activity_at && $client->last_activity_at->isBefore(now()->subMonths(12))) {
            $score += 10;
        } elseif ($client->last_activity_at && $client->last_activity_at->isBefore(now()->subMonths(6))) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * Get risk factors
     */
    private function getRiskFactors(Client $client): array
    {
        $factors = [];

        $cancellationRate = $this->calculateCancellationRate($client);
        if ($cancellationRate > 0.3) {
            $factors[] = "High cancellation rate ({$cancellationRate}%)";
        }

        $paymentFailures = $this->getPaymentFailures($client);
        if ($paymentFailures > 2) {
            $factors[] = "{$paymentFailures} payment failures";
        }

        if ($client->status === Client::STATUS_BLOCKED) {
            $factors[] = 'Account blocked';
        }

        if ($client->average_rating < 2.0 && $client->total_bookings >= 3) {
            $factors[] = 'Poor average rating';
        }

        if ($client->last_activity_at && $client->last_activity_at->isBefore(now()->subMonths(6))) {
            $factors[] = 'Long period of inactivity';
        }

        return $factors;
    }

    /**
     * Calculate value trend
     */
    private function calculateValueTrend(Client $client): string
    {
        // This would require comparing recent spending to historical spending
        // Simplified implementation
        $recentBookings = $client->bookings()
                                ->where('created_at', '>=', now()->subMonths(3))
                                ->sum('total_price');

        $previousBookings = $client->bookings()
                                  ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])
                                  ->sum('total_price');

        if ($previousBookings == 0) {
            return $recentBookings > 0 ? 'increasing' : 'stable';
        }

        $change = ($recentBookings - $previousBookings) / $previousBookings;

        if ($change > 0.2) return 'increasing';
        if ($change < -0.2) return 'decreasing';
        return 'stable';
    }

    /**
     * Calculate potential value
     */
    private function calculatePotentialValue(Client $client): float
    {
        // Simple algorithm based on profile and current spending
        $baseValue = $client->total_spent;
        $multiplier = match ($client->profile_type) {
            Client::PROFILE_VIP => 1.5,
            Client::PROFILE_FREQUENT => 1.3,
            Client::PROFILE_REGULAR => 1.2,
            Client::PROFILE_NEW => 1.1,
            Client::PROFILE_SEASONAL => 1.0,
            Client::PROFILE_RISK => 0.5,
            default => 1.0
        };

        return $baseValue * $multiplier;
    }

    /**
     * Calculate booking frequency
     */
    private function calculateBookingFrequency(Client $client): float
    {
        if ($client->total_bookings === 0) return 0;

        $monthsSinceCreation = max(1, $client->created_at->diffInMonths(now()));
        return $client->total_bookings / $monthsSinceCreation;
    }

    /**
     * Get preferred services
     */
    private function getPreferredServices(Client $client): array
    {
        // This would analyze booking patterns to identify preferred services
        // Simplified implementation
        return $client->bookings()
                     ->join('courses', 'v5_bookings.course_id', '=', 'courses.id')
                     ->selectRaw('courses.name, COUNT(*) as count')
                     ->groupBy('courses.name')
                     ->orderByDesc('count')
                     ->limit(5)
                     ->pluck('count', 'name')
                     ->toArray();
    }

    /**
     * Get communication preferences
     */
    private function getCommunicationPreferences(Client $client): array
    {
        return [
            'preferred_language' => $client->preferred_language ?? 'es',
            'has_email' => !empty($client->email),
            'has_phone' => !empty($client->phone),
            'prefers_digital' => !empty($client->email) && empty($client->phone),
        ];
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(Client $client): array
    {
        $recommendations = [];

        if ($client->profile_type === Client::PROFILE_RISK) {
            $recommendations[] = 'Consider reaching out to address any concerns';
            $recommendations[] = 'Review recent booking history for issues';
            $recommendations[] = 'Implement retention strategy';
        } elseif ($client->profile_type === Client::PROFILE_VIP) {
            $recommendations[] = 'Offer premium services and exclusive deals';
            $recommendations[] = 'Prioritize customer service';
            $recommendations[] = 'Consider loyalty rewards';
        } elseif ($client->profile_type === Client::PROFILE_NEW) {
            $recommendations[] = 'Send welcome communications';
            $recommendations[] = 'Offer beginner-friendly services';
            $recommendations[] = 'Follow up after first booking';
        }

        if (empty($client->email)) {
            $recommendations[] = 'Collect email address for better communication';
        }

        if (empty($client->phone)) {
            $recommendations[] = 'Collect phone number for emergencies';
        }

        return $recommendations;
    }
}