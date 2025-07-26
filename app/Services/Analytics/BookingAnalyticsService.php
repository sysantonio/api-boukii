<?php

namespace App\Services\Analytics;

use App\Models\Booking;

class BookingAnalyticsService
{
    public function computeMetrics(int $bookingId): array
    {
        $booking = Booking::with(['bookingUsers', 'payments'])->findOrFail($bookingId);

        $participantCount = $booking->bookingUsers->count();
        $revenue = $booking->payments->where('status', 'paid')->sum('amount');
        $estimatedCost = $booking->price_total * 0.8; // Simplified cost estimate
        $profit = $revenue - $estimatedCost;

        return [
            'performance' => [
                'participantCount' => $participantCount,
                'paymentRate' => $booking->price_total > 0
                    ? round(($revenue / $booking->price_total) * 100, 2)
                    : 0,
            ],
            'financial' => [
                'revenue' => $revenue,
                'profit' => $profit,
                'marginPercentage' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                'costBreakdown' => [
                    'estimated' => $estimatedCost,
                ],
            ],
            'satisfaction' => [
                'score' => null,
                'reviews' => [],
                'nps' => null,
            ],
            'operational' => [
                'utilizationRate' => null,
                'efficiencyScore' => null,
                'resourceUsage' => [],
            ],
        ];
    }

    public function computeProfitability(int $bookingId): array
    {
        $metrics = $this->computeMetrics($bookingId);

        return $metrics['financial'];
    }
}
