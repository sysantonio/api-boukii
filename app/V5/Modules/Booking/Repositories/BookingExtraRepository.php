<?php

namespace App\V5\Modules\Booking\Repositories;

use App\V5\Modules\Booking\Models\BookingExtra;
use Illuminate\Database\Eloquent\Collection;

/**
 * V5 Booking Extra Repository
 * 
 * Handles all booking extra data access operations.
 */
class BookingExtraRepository
{
    /**
     * Create a new booking extra
     */
    public function create(array $data): BookingExtra
    {
        return BookingExtra::create($data);
    }

    /**
     * Find booking extra by ID
     */
    public function findById(int $id): ?BookingExtra
    {
        return BookingExtra::find($id);
    }

    /**
     * Update booking extra
     */
    public function update(BookingExtra $extra, array $data): BookingExtra
    {
        $extra->update($data);
        return $extra->fresh();
    }

    /**
     * Delete booking extra
     */
    public function delete(BookingExtra $extra): bool
    {
        return $extra->delete();
    }

    /**
     * Get extras for a booking
     */
    public function getBookingExtras(int $bookingId): Collection
    {
        return BookingExtra::where('booking_id', $bookingId)
                          ->active()
                          ->orderBy('created_at', 'asc')
                          ->get();
    }

    /**
     * Get extras by type for a booking
     */
    public function getBookingExtrasByType(int $bookingId, string $type): Collection
    {
        return BookingExtra::where('booking_id', $bookingId)
                          ->ofType($type)
                          ->active()
                          ->orderBy('created_at', 'asc')
                          ->get();
    }

    /**
     * Get total extras price for a booking
     */
    public function getTotalExtrasPrice(int $bookingId): float
    {
        return BookingExtra::where('booking_id', $bookingId)
                          ->active()
                          ->sum('total_price');
    }

    /**
     * Get required extras for a booking
     */
    public function getRequiredExtras(int $bookingId): Collection
    {
        return BookingExtra::where('booking_id', $bookingId)
                          ->required()
                          ->active()
                          ->get();
    }

    /**
     * Bulk create extras for a booking
     */
    public function bulkCreateForBooking(int $bookingId, array $extrasData): Collection
    {
        $extras = collect();
        
        foreach ($extrasData as $extraData) {
            $extraData['booking_id'] = $bookingId;
            $extras->push($this->create($extraData));
        }
        
        return $extras;
    }

    /**
     * Update or create extras for a booking
     */
    public function syncBookingExtras(int $bookingId, array $extrasData): Collection
    {
        // Get existing extras
        $existingExtras = $this->getBookingExtras($bookingId);
        $existingIds = $existingExtras->pluck('id')->toArray();
        
        $updatedExtras = collect();
        $providedIds = [];
        
        foreach ($extrasData as $extraData) {
            if (isset($extraData['id']) && in_array($extraData['id'], $existingIds)) {
                // Update existing extra
                $extra = $existingExtras->find($extraData['id']);
                $updatedExtras->push($this->update($extra, $extraData));
                $providedIds[] = $extraData['id'];
            } else {
                // Create new extra
                $extraData['booking_id'] = $bookingId;
                $updatedExtras->push($this->create($extraData));
            }
        }
        
        // Delete extras that were not provided
        $extrasToDelete = array_diff($existingIds, $providedIds);
        foreach ($extrasToDelete as $extraId) {
            $extra = $existingExtras->find($extraId);
            if ($extra) {
                $this->delete($extra);
            }
        }
        
        return $updatedExtras;
    }

    /**
     * Deactivate all extras for a booking
     */
    public function deactivateBookingExtras(int $bookingId): int
    {
        return BookingExtra::where('booking_id', $bookingId)
                          ->update(['is_active' => false]);
    }

    /**
     * Get extras statistics
     */
    public function getExtrasStats(int $seasonId, int $schoolId): array
    {
        $baseQuery = BookingExtra::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })->active();

        $stats = [];
        $types = BookingExtra::getValidTypes();
        
        foreach ($types as $type) {
            $typeStats = (clone $baseQuery)->ofType($type);
            $stats[$type] = [
                'count' => $typeStats->count(),
                'total_revenue' => $typeStats->sum('total_price'),
                'average_price' => $typeStats->avg('total_price') ?: 0,
            ];
        }
        
        $stats['overall'] = [
            'total_extras' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->sum('total_price'),
            'average_extras_per_booking' => $this->getAverageExtrasPerBooking($seasonId, $schoolId),
        ];
        
        return $stats;
    }

    /**
     * Get average number of extras per booking
     */
    private function getAverageExtrasPerBooking(int $seasonId, int $schoolId): float
    {
        $totalBookings = \App\V5\Modules\Booking\Models\Booking::forSeason($seasonId)
                                                              ->forSchool($schoolId)
                                                              ->count();
        
        if ($totalBookings === 0) {
            return 0;
        }
        
        $totalExtras = BookingExtra::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })->active()->count();
        
        return $totalExtras / $totalBookings;
    }
}