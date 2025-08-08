<?php

namespace App\V5\Modules\Booking\Services;

use App\V5\Modules\Booking\Models\Booking;
use App\V5\Modules\Booking\Repositories\BookingRepository;
use App\V5\Modules\Booking\Repositories\BookingExtraRepository;
use App\V5\Modules\Booking\Repositories\BookingEquipmentRepository;
use App\V5\Modules\Booking\Repositories\BookingPaymentRepository;
use App\V5\Logging\V5Logger;
use App\V5\Exceptions\BookingValidationException;
use App\V5\Exceptions\BookingNotFoundException;
use App\V5\Exceptions\BookingStatusException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

/**
 * V5 Booking Service
 * 
 * Main business logic service for booking operations.
 * Coordinates between repositories and handles complex booking workflows.
 */
class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private BookingExtraRepository $extraRepository,
        private BookingEquipmentRepository $equipmentRepository,
        private BookingPaymentRepository $paymentRepository,
        private BookingPriceCalculatorService $priceCalculator,
        private BookingAvailabilityService $availabilityService,
        private BookingWorkflowService $workflowService
    ) {}

    /**
     * Create a new booking
     */
    public function createBooking(array $data, int $seasonId, int $schoolId): Booking
    {
        V5Logger::logBusinessEvent('booking', 'creation_started', [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'type' => $data['type'] ?? null,
        ]);

        try {
            // Validate season context
            $this->validateSeasonContext($seasonId, $schoolId);

            // Validate booking data
            $this->validateBookingData($data);

            // Check availability if required
            if ($this->requiresAvailabilityCheck($data)) {
                $availability = $this->availabilityService->checkAvailability(
                    $seasonId,
                    $schoolId,
                    $data
                );

                if (!$availability['available']) {
                    throw new BookingValidationException('Selected time slot is not available');
                }
            }

            // Calculate pricing
            $pricingData = $this->priceCalculator->calculateBookingPrice($data, $seasonId, $schoolId);

            // Merge pricing data
            $bookingData = array_merge($data, [
                'season_id' => $seasonId,
                'school_id' => $schoolId,
                'status' => Booking::STATUS_PENDING,
                'base_price' => $pricingData['base_price'],
                'extras_price' => $pricingData['extras_price'],
                'equipment_price' => $pricingData['equipment_price'],
                'insurance_price' => $pricingData['insurance_price'],
                'tax_amount' => $pricingData['tax_amount'],
                'discount_amount' => $pricingData['discount_amount'],
                'total_price' => $pricingData['total_price'],
                'currency' => $pricingData['currency'],
                'has_insurance' => !empty($data['insurance']),
                'has_equipment' => !empty($data['equipment']),
            ]);

            // Create booking
            $booking = $this->bookingRepository->create($bookingData);

            // Create related records
            if (!empty($data['extras'])) {
                $this->extraRepository->bulkCreateForBooking($booking->id, $data['extras']);
            }

            if (!empty($data['equipment'])) {
                $this->equipmentRepository->bulkCreateForBooking($booking->id, $data['equipment']);
            }

            // Load relationships
            $booking = $booking->load(['client', 'course', 'monitor', 'extras', 'equipment']);

            V5Logger::logBusinessEvent('booking', 'created', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'client_id' => $booking->client_id,
                'total_price' => $booking->total_price,
            ]);

            return $booking;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'booking_creation',
                'season_id' => $seasonId,
                'school_id' => $schoolId,
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing booking
     */
    public function updateBooking(int $bookingId, array $data, int $seasonId, int $schoolId): Booking
    {
        V5Logger::logBusinessEvent('booking', 'update_started', [
            'booking_id' => $bookingId,
            'season_id' => $seasonId,
            'school_id' => $schoolId,
        ]);

        try {
            $booking = $this->findBookingById($bookingId, $seasonId, $schoolId);

            // Check if booking can be updated
            if (!$this->canUpdateBooking($booking)) {
                throw new BookingStatusException('Booking cannot be updated in current status');
            }

            // Validate update data
            $this->validateBookingUpdateData($data, $booking);

            // Check availability if schedule changed
            if ($this->hasScheduleChanged($data, $booking) && $this->requiresAvailabilityCheck($data)) {
                $availability = $this->availabilityService->checkAvailability(
                    $seasonId,
                    $schoolId,
                    $data,
                    $bookingId
                );

                if (!$availability['available']) {
                    throw new BookingValidationException('Updated time slot is not available');
                }
            }

            // Recalculate pricing if necessary
            if ($this->requiresPriceRecalculation($data, $booking)) {
                $pricingData = $this->priceCalculator->calculateBookingPrice(
                    array_merge($booking->toArray(), $data),
                    $seasonId,
                    $schoolId
                );

                $data = array_merge($data, [
                    'base_price' => $pricingData['base_price'],
                    'extras_price' => $pricingData['extras_price'],
                    'equipment_price' => $pricingData['equipment_price'],
                    'insurance_price' => $pricingData['insurance_price'],
                    'tax_amount' => $pricingData['tax_amount'],
                    'discount_amount' => $pricingData['discount_amount'],
                    'total_price' => $pricingData['total_price'],
                ]);
            }

            // Update booking
            $booking = $this->bookingRepository->update($booking, $data);

            // Update related records if provided
            if (isset($data['extras'])) {
                $this->extraRepository->syncBookingExtras($booking->id, $data['extras']);
            }

            if (isset($data['equipment'])) {
                $this->equipmentRepository->syncBookingEquipment($booking->id, $data['equipment']);
            }

            // Reload relationships
            $booking = $booking->fresh(['client', 'course', 'monitor', 'extras', 'equipment']);

            V5Logger::logBusinessEvent('booking', 'updated', [
                'booking_id' => $booking->id,
                'changes' => array_keys($data),
                'total_price' => $booking->total_price,
            ]);

            return $booking;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'booking_update',
                'booking_id' => $bookingId,
                'season_id' => $seasonId,
                'school_id' => $schoolId,
            ]);
            throw $e;
        }
    }

    /**
     * Find booking by ID
     */
    public function findBookingById(int $bookingId, int $seasonId, int $schoolId): Booking
    {
        $booking = $this->bookingRepository->findById($bookingId, $seasonId, $schoolId);

        if (!$booking) {
            throw new BookingNotFoundException("Booking not found with ID: {$bookingId}");
        }

        return $booking;
    }

    /**
     * Find booking by reference
     */
    public function findBookingByReference(string $reference, int $seasonId, int $schoolId): Booking
    {
        $booking = $this->bookingRepository->findByReference($reference, $seasonId, $schoolId);

        if (!$booking) {
            throw new BookingNotFoundException("Booking not found with reference: {$reference}");
        }

        return $booking;
    }

    /**
     * Get bookings with filtering
     */
    public function getBookings(
        int $seasonId,
        int $schoolId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): LengthAwarePaginator {
        return $this->bookingRepository->getBookings($seasonId, $schoolId, $filters, $page, $limit);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(
        int $bookingId,
        string $newStatus,
        int $seasonId,
        int $schoolId,
        ?string $reason = null
    ): Booking {
        V5Logger::logBusinessEvent('booking', 'status_change_started', [
            'booking_id' => $bookingId,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);

        try {
            $booking = $this->findBookingById($bookingId, $seasonId, $schoolId);

            // Validate status transition
            if (!$booking->canTransitionTo($newStatus)) {
                throw new BookingStatusException(
                    "Cannot transition from {$booking->status} to {$newStatus}"
                );
            }

            // Execute status change through workflow service
            $booking = $this->workflowService->changeStatus($booking, $newStatus, $reason);

            V5Logger::logBusinessEvent('booking', 'status_changed', [
                'booking_id' => $booking->id,
                'old_status' => $booking->getOriginal('status'),
                'new_status' => $booking->status,
                'reason' => $reason,
            ]);

            return $booking;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'booking_status_update',
                'booking_id' => $bookingId,
                'new_status' => $newStatus,
            ]);
            throw $e;
        }
    }

    /**
     * Delete booking
     */
    public function deleteBooking(int $bookingId, int $seasonId, int $schoolId, ?string $reason = null): bool
    {
        V5Logger::logBusinessEvent('booking', 'deletion_started', [
            'booking_id' => $bookingId,
            'reason' => $reason,
        ]);

        try {
            $booking = $this->findBookingById($bookingId, $seasonId, $schoolId);

            // Check if booking can be deleted
            if (!$this->canDeleteBooking($booking)) {
                throw new BookingStatusException('Booking cannot be deleted in current status');
            }

            // Delete booking (soft delete)
            $result = $this->bookingRepository->delete($booking);

            if ($result) {
                V5Logger::logBusinessEvent('booking', 'deleted', [
                    'booking_id' => $bookingId,
                    'booking_reference' => $booking->booking_reference,
                    'reason' => $reason,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'booking_deletion',
                'booking_id' => $bookingId,
            ]);
            throw $e;
        }
    }

    /**
     * Get booking statistics
     */
    public function getBookingStats(int $seasonId, int $schoolId): array
    {
        return $this->bookingRepository->getBookingStats($seasonId, $schoolId);
    }

    /**
     * Search bookings
     */
    public function searchBookings(string $query, int $seasonId, int $schoolId, int $limit = 20): Collection
    {
        return $this->bookingRepository->searchBookings($query, $seasonId, $schoolId, $limit);
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcomingBookings(int $seasonId, int $schoolId, int $days = 7, int $limit = 20): Collection
    {
        return $this->bookingRepository->getUpcomingBookings($seasonId, $schoolId, $days, $limit);
    }

    /**
     * Get bookings requiring attention
     */
    public function getBookingsRequiringAttention(int $seasonId, int $schoolId): array
    {
        return $this->bookingRepository->getBookingsRequiringAttention($seasonId, $schoolId);
    }

    /**
     * Private helper methods
     */
    private function validateSeasonContext(int $seasonId, int $schoolId): void
    {
        // Validate that season exists and belongs to school
        // This would typically query the Season model
        // Implementation depends on Season model structure
    }

    private function validateBookingData(array $data): void
    {
        $required = ['type', 'client_id', 'start_date'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new BookingValidationException("Field {$field} is required");
            }
        }

        if (!in_array($data['type'], Booking::getValidTypes())) {
            throw new BookingValidationException('Invalid booking type');
        }

        if (isset($data['start_date']) && Carbon::parse($data['start_date'])->isPast()) {
            throw new BookingValidationException('Booking start date cannot be in the past');
        }
    }

    private function validateBookingUpdateData(array $data, Booking $booking): void
    {
        if (isset($data['type']) && !in_array($data['type'], Booking::getValidTypes())) {
            throw new BookingValidationException('Invalid booking type');
        }

        if (isset($data['start_date']) && Carbon::parse($data['start_date'])->isPast()) {
            throw new BookingValidationException('Booking start date cannot be in the past');
        }
    }

    private function requiresAvailabilityCheck(array $data): bool
    {
        return isset($data['course_id']) || isset($data['monitor_id']);
    }

    private function canUpdateBooking(Booking $booking): bool
    {
        return !in_array($booking->status, [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_CANCELLED,
        ]);
    }

    private function canDeleteBooking(Booking $booking): bool
    {
        return in_array($booking->status, [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
        ]);
    }

    private function hasScheduleChanged(array $data, Booking $booking): bool
    {
        $scheduleFields = ['start_date', 'end_date', 'start_time', 'end_time', 'course_id', 'monitor_id'];
        
        foreach ($scheduleFields as $field) {
            if (isset($data[$field]) && $data[$field] != $booking->$field) {
                return true;
            }
        }
        
        return false;
    }

    private function requiresPriceRecalculation(array $data, Booking $booking): bool
    {
        $priceFields = [
            'participants', 'course_id', 'extras', 'equipment', 
            'has_insurance', 'start_date', 'end_date'
        ];
        
        foreach ($priceFields as $field) {
            if (isset($data[$field]) && $data[$field] != $booking->$field) {
                return true;
            }
        }
        
        return false;
    }
}