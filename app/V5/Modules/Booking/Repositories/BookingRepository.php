<?php

namespace App\V5\Modules\Booking\Repositories;

use App\V5\Modules\Booking\Models\Booking;
use App\V5\Modules\Booking\Models\BookingExtra;
use App\V5\Modules\Booking\Models\BookingEquipment;
use App\V5\Modules\Booking\Models\BookingPayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

/**
 * V5 Booking Repository
 * 
 * Handles all booking data access operations with season context enforcement.
 * Provides methods for CRUD operations, filtering, searching, and statistics.
 */
class BookingRepository
{
    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        // Ensure season context is provided
        if (!isset($data['season_id'])) {
            throw new \InvalidArgumentException('Season context is required for V5 bookings');
        }

        // Generate booking reference if not provided
        if (!isset($data['booking_reference'])) {
            $data['booking_reference'] = Booking::generateBookingReference(
                $data['season_id'],
                $data['school_id']
            );
        }

        return Booking::create($data);
    }

    /**
     * Find booking by ID with season context
     */
    public function findById(int $id, int $seasonId, int $schoolId): ?Booking
    {
        return Booking::where('id', $id)
                     ->forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->first();
    }

    /**
     * Find booking by reference with season context
     */
    public function findByReference(string $reference, int $seasonId, int $schoolId): ?Booking
    {
        return Booking::where('booking_reference', $reference)
                     ->forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->first();
    }

    /**
     * Update booking
     */
    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);
        return $booking->fresh();
    }

    /**
     * Delete booking (soft delete)
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Get bookings with filtering and pagination
     */
    public function getBookings(
        int $seasonId,
        int $schoolId,
        array $filters = [],
        int $page = 1,
        int $limit = 20,
        array $with = ['client', 'course', 'monitor', 'season', 'school']
    ): LengthAwarePaginator {
        $query = Booking::forSeason($seasonId)
                       ->forSchool($schoolId)
                       ->with($with);

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
                    ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get bookings by type
     */
    public function getBookingsByType(
        int $seasonId,
        int $schoolId,
        string $type,
        int $limit = 20
    ): Collection {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->ofType($type)
                     ->with(['client', 'course', 'monitor'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get bookings by status
     */
    public function getBookingsByStatus(
        int $seasonId,
        int $schoolId,
        string $status,
        int $limit = 20
    ): Collection {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->withStatus($status)
                     ->with(['client', 'course', 'monitor'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcomingBookings(
        int $seasonId,
        int $schoolId,
        int $days = 7,
        int $limit = 20
    ): Collection {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->upcoming()
                     ->betweenDates(now(), now()->addDays($days))
                     ->with(['client', 'course', 'monitor'])
                     ->orderBy('start_date', 'asc')
                     ->orderBy('start_time', 'asc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get bookings for a specific client
     */
    public function getClientBookings(
        int $clientId,
        int $seasonId,
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Booking::where('client_id', $clientId)
                     ->forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->with(['course', 'monitor', 'extras', 'equipment', 'payments'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get bookings for a specific monitor
     */
    public function getMonitorBookings(
        int $monitorId,
        int $seasonId,
        int $schoolId,
        ?Carbon $date = null,
        int $limit = 20
    ): Collection {
        $query = Booking::where('monitor_id', $monitorId)
                       ->forSeason($seasonId)
                       ->forSchool($schoolId)
                       ->with(['client', 'course']);

        if ($date) {
            $query->whereDate('start_date', $date);
        }

        return $query->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get bookings for a specific course
     */
    public function getCourseBookings(
        int $courseId,
        int $seasonId,
        int $schoolId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $query = Booking::where('course_id', $courseId)
                       ->forSeason($seasonId)
                       ->forSchool($schoolId)
                       ->with(['client', 'monitor']);

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        return $query->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->get();
    }

    /**
     * Search bookings by query
     */
    public function searchBookings(
        string $query,
        int $seasonId,
        int $schoolId,
        int $limit = 20
    ): Collection {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->where(function ($q) use ($query) {
                         $q->where('booking_reference', 'LIKE', "%{$query}%")
                           ->orWhere('notes', 'LIKE', "%{$query}%")
                           ->orWhere('special_requests', 'LIKE', "%{$query}%")
                           ->orWhereHas('client', function ($clientQuery) use ($query) {
                               $clientQuery->where('first_name', 'LIKE', "%{$query}%")
                                          ->orWhere('last_name', 'LIKE', "%{$query}%")
                                          ->orWhere('email', 'LIKE', "%{$query}%");
                           });
                     })
                     ->with(['client', 'course', 'monitor'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get booking statistics for dashboard
     */
    public function getBookingStats(int $seasonId, int $schoolId): array
    {
        $baseQuery = Booking::forSeason($seasonId)->forSchool($schoolId);

        return [
            'total_bookings' => (clone $baseQuery)->count(),
            'pending_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_PENDING)->count(),
            'confirmed_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_CONFIRMED)->count(),
            'paid_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_PAID)->count(),
            'completed_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_COMPLETED)->count(),
            'cancelled_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_CANCELLED)->count(),
            'no_show_bookings' => (clone $baseQuery)->withStatus(Booking::STATUS_NO_SHOW)->count(),
            'course_bookings' => (clone $baseQuery)->ofType(Booking::TYPE_COURSE)->count(),
            'activity_bookings' => (clone $baseQuery)->ofType(Booking::TYPE_ACTIVITY)->count(),
            'material_bookings' => (clone $baseQuery)->ofType(Booking::TYPE_MATERIAL)->count(),
            'total_revenue' => (clone $baseQuery)->withStatus(Booking::STATUS_PAID)->sum('total_price'),
            'pending_revenue' => (clone $baseQuery)->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAID
            ])->sum('total_price'),
            'upcoming_bookings' => (clone $baseQuery)->upcoming()->count(),
            'today_bookings' => (clone $baseQuery)->whereDate('start_date', now())->count(),
            'this_week_bookings' => (clone $baseQuery)->whereBetween('start_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_bookings' => (clone $baseQuery)->whereBetween('start_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
        ];
    }

    /**
     * Get revenue statistics
     */
    public function getRevenueStats(
        int $seasonId,
        int $schoolId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = Booking::forSeason($seasonId)
                       ->forSchool($schoolId)
                       ->whereIn('status', [
                           Booking::STATUS_PAID,
                           Booking::STATUS_COMPLETED
                       ]);

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        $bookings = $query->get();

        return [
            'total_revenue' => $bookings->sum('total_price'),
            'base_revenue' => $bookings->sum('base_price'),
            'extras_revenue' => $bookings->sum('extras_price'),
            'equipment_revenue' => $bookings->sum('equipment_price'),
            'insurance_revenue' => $bookings->sum('insurance_price'),
            'tax_amount' => $bookings->sum('tax_amount'),
            'discount_amount' => $bookings->sum('discount_amount'),
            'average_booking_value' => $bookings->isNotEmpty() ? $bookings->avg('total_price') : 0,
            'booking_count' => $bookings->count(),
        ];
    }

    /**
     * Check availability for course/monitor/date/time
     */
    public function checkAvailability(
        int $seasonId,
        int $schoolId,
        ?int $courseId,
        ?int $monitorId,
        Carbon $startDate,
        ?string $startTime = null,
        ?string $endTime = null,
        ?int $excludeBookingId = null
    ): array {
        $query = Booking::forSeason($seasonId)
                       ->forSchool($schoolId)
                       ->whereDate('start_date', $startDate)
                       ->confirmed();

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($monitorId) {
            $query->where('monitor_id', $monitorId);
        }

        if ($startTime && $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });
        }

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        $conflictingBookings = $query->get();

        return [
            'available' => $conflictingBookings->isEmpty(),
            'conflicts' => $conflictingBookings->count(),
            'conflicting_bookings' => $conflictingBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'client' => $booking->client?->first_name . ' ' . $booking->client?->last_name,
                ];
            })->toArray(),
        ];
    }

    /**
     * Apply filters to booking query
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['monitor_id'])) {
            $query->where('monitor_id', $filters['monitor_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('start_date', '<=', $filters['end_date']);
        }

        if (isset($filters['date_range']) && is_array($filters['date_range'])) {
            $query->betweenDates(
                Carbon::parse($filters['date_range']['start']),
                Carbon::parse($filters['date_range']['end'])
            );
        }

        if (isset($filters['has_insurance'])) {
            $query->where('has_insurance', $filters['has_insurance']);
        }

        if (isset($filters['has_equipment'])) {
            $query->where('has_equipment', $filters['has_equipment']);
        }

        if (isset($filters['min_price'])) {
            $query->where('total_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('total_price', '<=', $filters['max_price']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'LIKE', "%{$search}%")
                                 ->orWhere('last_name', 'LIKE', "%{$search}%")
                                 ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Get bookings requiring attention (expired, overdue, etc.)
     */
    public function getBookingsRequiringAttention(int $seasonId, int $schoolId): array
    {
        $now = now();
        
        return [
            'expired_pending' => Booking::forSeason($seasonId)
                                       ->forSchool($schoolId)
                                       ->withStatus(Booking::STATUS_PENDING)
                                       ->where('created_at', '<', $now->subHours(24))
                                       ->with(['client'])
                                       ->get(),
            
            'unpaid_confirmed' => Booking::forSeason($seasonId)
                                        ->forSchool($schoolId)
                                        ->withStatus(Booking::STATUS_CONFIRMED)
                                        ->where('start_date', '<=', $now->addDays(3))
                                        ->with(['client'])
                                        ->get(),
            
            'starting_soon' => Booking::forSeason($seasonId)
                                     ->forSchool($schoolId)
                                     ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PAID])
                                     ->where('start_date', $now->toDateString())
                                     ->where('start_time', '<=', $now->addHours(2)->format('H:i:s'))
                                     ->with(['client', 'monitor'])
                                     ->get(),
        ];
    }
}