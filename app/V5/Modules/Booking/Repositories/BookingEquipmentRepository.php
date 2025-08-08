<?php

namespace App\V5\Modules\Booking\Repositories;

use App\V5\Modules\Booking\Models\BookingEquipment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * V5 Booking Equipment Repository
 * 
 * Handles all booking equipment data access operations.
 */
class BookingEquipmentRepository
{
    /**
     * Create a new booking equipment
     */
    public function create(array $data): BookingEquipment
    {
        return BookingEquipment::create($data);
    }

    /**
     * Find booking equipment by ID
     */
    public function findById(int $id): ?BookingEquipment
    {
        return BookingEquipment::find($id);
    }

    /**
     * Update booking equipment
     */
    public function update(BookingEquipment $equipment, array $data): BookingEquipment
    {
        $equipment->update($data);
        return $equipment->fresh();
    }

    /**
     * Delete booking equipment
     */
    public function delete(BookingEquipment $equipment): bool
    {
        return $equipment->delete();
    }

    /**
     * Get equipment for a booking
     */
    public function getBookingEquipment(int $bookingId): Collection
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->orderBy('participant_name', 'asc')
                              ->orderBy('equipment_type', 'asc')
                              ->get();
    }

    /**
     * Get equipment by type for a booking
     */
    public function getBookingEquipmentByType(int $bookingId, string $type): Collection
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->ofType($type)
                              ->orderBy('participant_name', 'asc')
                              ->get();
    }

    /**
     * Get equipment for a specific participant
     */
    public function getParticipantEquipment(int $bookingId, string $participantName): Collection
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->forParticipant($participantName)
                              ->orderBy('equipment_type', 'asc')
                              ->get();
    }

    /**
     * Get total equipment price for a booking
     */
    public function getTotalEquipmentPrice(int $bookingId): float
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->sum('total_price');
    }

    /**
     * Get rented equipment
     */
    public function getRentedEquipment(int $bookingId): Collection
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->rented()
                              ->get();
    }

    /**
     * Get outstanding equipment (rented but not returned)
     */
    public function getOutstandingEquipment(int $bookingId): Collection
    {
        return BookingEquipment::where('booking_id', $bookingId)
                              ->outstanding()
                              ->get();
    }

    /**
     * Get overdue equipment across all bookings
     */
    public function getOverdueEquipment(int $seasonId, int $schoolId): Collection
    {
        return BookingEquipment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId)
                  ->where('end_date', '<', now()->subDay());
        })
        ->outstanding()
        ->with(['booking.client'])
        ->get()
        ->filter(function ($equipment) {
            return $equipment->isOverdue();
        });
    }

    /**
     * Bulk create equipment for a booking
     */
    public function bulkCreateForBooking(int $bookingId, array $equipmentData): Collection
    {
        $equipment = collect();
        
        foreach ($equipmentData as $itemData) {
            $itemData['booking_id'] = $bookingId;
            $equipment->push($this->create($itemData));
        }
        
        return $equipment;
    }

    /**
     * Update or create equipment for a booking
     */
    public function syncBookingEquipment(int $bookingId, array $equipmentData): Collection
    {
        // Get existing equipment
        $existingEquipment = $this->getBookingEquipment($bookingId);
        $existingIds = $existingEquipment->pluck('id')->toArray();
        
        $updatedEquipment = collect();
        $providedIds = [];
        
        foreach ($equipmentData as $itemData) {
            if (isset($itemData['id']) && in_array($itemData['id'], $existingIds)) {
                // Update existing equipment
                $equipment = $existingEquipment->find($itemData['id']);
                $updatedEquipment->push($this->update($equipment, $itemData));
                $providedIds[] = $itemData['id'];
            } else {
                // Create new equipment
                $itemData['booking_id'] = $bookingId;
                $updatedEquipment->push($this->create($itemData));
            }
        }
        
        // Delete equipment that was not provided
        $equipmentToDelete = array_diff($existingIds, $providedIds);
        foreach ($equipmentToDelete as $equipmentId) {
            $equipment = $existingEquipment->find($equipmentId);
            if ($equipment) {
                $this->delete($equipment);
            }
        }
        
        return $updatedEquipment;
    }

    /**
     * Mark equipment as rented
     */
    public function markAsRented(
        int $equipmentId,
        string $condition = BookingEquipment::CONDITION_GOOD
    ): ?BookingEquipment {
        $equipment = $this->findById($equipmentId);
        
        if ($equipment && !$equipment->isRented()) {
            return $equipment->markAsRented($condition);
        }
        
        return null;
    }

    /**
     * Mark equipment as returned
     */
    public function markAsReturned(
        int $equipmentId,
        string $condition = BookingEquipment::CONDITION_GOOD,
        ?string $notes = null
    ): ?BookingEquipment {
        $equipment = $this->findById($equipmentId);
        
        if ($equipment && $equipment->isRented()) {
            return $equipment->markAsReturned($condition, $notes);
        }
        
        return null;
    }

    /**
     * Bulk mark equipment as rented for a booking
     */
    public function bulkMarkAsRented(
        int $bookingId,
        array $equipmentConditions = []
    ): Collection {
        $equipment = $this->getBookingEquipment($bookingId);
        $rentedEquipment = collect();
        
        foreach ($equipment as $item) {
            if (!$item->isRented()) {
                $condition = $equipmentConditions[$item->id] ?? BookingEquipment::CONDITION_GOOD;
                $rentedEquipment->push($item->markAsRented($condition));
            }
        }
        
        return $rentedEquipment;
    }

    /**
     * Bulk mark equipment as returned for a booking
     */
    public function bulkMarkAsReturned(
        int $bookingId,
        array $equipmentConditions = [],
        array $equipmentNotes = []
    ): Collection {
        $equipment = $this->getBookingEquipment($bookingId);
        $returnedEquipment = collect();
        
        foreach ($equipment as $item) {
            if ($item->isRented()) {
                $condition = $equipmentConditions[$item->id] ?? BookingEquipment::CONDITION_GOOD;
                $notes = $equipmentNotes[$item->id] ?? null;
                $returnedEquipment->push($item->markAsReturned($condition, $notes));
            }
        }
        
        return $returnedEquipment;
    }

    /**
     * Get equipment statistics
     */
    public function getEquipmentStats(int $seasonId, int $schoolId): array
    {
        $baseQuery = BookingEquipment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        });

        $stats = [];
        $types = BookingEquipment::getValidTypes();
        
        foreach ($types as $type) {
            $typeStats = (clone $baseQuery)->ofType($type);
            $stats[$type] = [
                'count' => $typeStats->count(),
                'total_revenue' => $typeStats->sum('total_price'),
                'rented' => $typeStats->rented()->count(),
                'returned' => $typeStats->returned()->count(),
                'outstanding' => $typeStats->outstanding()->count(),
            ];
        }
        
        $stats['overall'] = [
            'total_equipment' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->sum('total_price'),
            'total_rented' => (clone $baseQuery)->rented()->count(),
            'total_returned' => (clone $baseQuery)->returned()->count(),
            'total_outstanding' => (clone $baseQuery)->outstanding()->count(),
            'average_rental_days' => (clone $baseQuery)->avg('rental_days') ?: 0,
        ];
        
        return $stats;
    }

    /**
     * Get equipment utilization report
     */
    public function getEquipmentUtilization(
        int $seasonId,
        int $schoolId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = BookingEquipment::whereHas('booking', function ($bookingQuery) use ($seasonId, $schoolId, $startDate, $endDate) {
            $bookingQuery->where('season_id', $seasonId)
                        ->where('school_id', $schoolId);
            
            if ($startDate && $endDate) {
                $bookingQuery->whereBetween('start_date', [$startDate, $endDate]);
            }
        });

        $equipment = $query->get();
        
        $utilization = [];
        $types = BookingEquipment::getValidTypes();
        
        foreach ($types as $type) {
            $typeEquipment = $equipment->where('equipment_type', $type);
            $totalItems = $typeEquipment->count();
            $totalRentalDays = $typeEquipment->sum('rental_days');
            
            $utilization[$type] = [
                'total_items' => $totalItems,
                'total_rental_days' => $totalRentalDays,
                'average_rental_days' => $totalItems > 0 ? $totalRentalDays / $totalItems : 0,
                'total_revenue' => $typeEquipment->sum('total_price'),
                'average_daily_rate' => $typeEquipment->avg('daily_rate') ?: 0,
            ];
        }
        
        return $utilization;
    }

    /**
     * Get damage report
     */
    public function getDamageReport(int $seasonId, int $schoolId): array
    {
        $equipment = BookingEquipment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->returned()
        ->get();

        $damageReport = [];
        $totalDamageFees = 0;
        
        foreach ($equipment as $item) {
            $damageFee = $item->calculateDamageFee();
            
            if ($damageFee > 0) {
                $damageReport[] = [
                    'id' => $item->id,
                    'booking_reference' => $item->booking->booking_reference,
                    'equipment_type' => $item->equipment_type,
                    'name' => $item->name,
                    'participant_name' => $item->participant_name,
                    'condition_out' => $item->condition_out,
                    'condition_in' => $item->condition_in,
                    'damage_fee' => $damageFee,
                    'rental_price' => $item->total_price,
                    'returned_at' => $item->returned_at,
                ];
                
                $totalDamageFees += $damageFee;
            }
        }
        
        return [
            'damaged_items' => $damageReport,
            'total_damage_fees' => $totalDamageFees,
            'damage_rate' => $equipment->count() > 0 ? (count($damageReport) / $equipment->count()) * 100 : 0,
        ];
    }
}