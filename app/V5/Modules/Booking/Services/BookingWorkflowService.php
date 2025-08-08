<?php

namespace App\V5\Modules\Booking\Services;

use App\V5\Modules\Booking\Models\Booking;
use App\V5\Modules\Booking\Repositories\BookingRepository;
use App\V5\Logging\V5Logger;
use App\V5\Exceptions\BookingStatusException;
use Carbon\Carbon;

/**
 * V5 Booking Workflow Service
 * 
 * Manages booking status transitions and workflow automation.
 * Handles the booking lifecycle: pending → confirmed → paid → completed → cancelled
 */
class BookingWorkflowService
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {}

    /**
     * Change booking status with workflow validation
     */
    public function changeStatus(Booking $booking, string $newStatus, ?string $reason = null): Booking
    {
        V5Logger::logBusinessEvent('booking_workflow', 'status_change_requested', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);

        // Validate status transition
        if (!$booking->canTransitionTo($newStatus)) {
            throw new BookingStatusException(
                "Invalid status transition from {$booking->status} to {$newStatus}"
            );
        }

        // Execute pre-transition actions
        $this->executePreTransitionActions($booking, $newStatus, $reason);

        // Update booking status and timestamps
        $updateData = ['status' => $newStatus];
        
        // Set appropriate timestamp based on new status
        switch ($newStatus) {
            case Booking::STATUS_CONFIRMED:
                $updateData['confirmed_at'] = now();
                break;
            case Booking::STATUS_PAID:
                $updateData['paid_at'] = now();
                break;
            case Booking::STATUS_COMPLETED:
                $updateData['completed_at'] = now();
                break;
            case Booking::STATUS_CANCELLED:
            case Booking::STATUS_NO_SHOW:
                $updateData['cancelled_at'] = now();
                if ($reason) {
                    $updateData['cancellation_reason'] = $reason;
                }
                break;
        }

        // Update the booking
        $booking = $this->bookingRepository->update($booking, $updateData);

        // Execute post-transition actions
        $this->executePostTransitionActions($booking, $newStatus, $reason);

        V5Logger::logBusinessEvent('booking_workflow', 'status_changed', [
            'booking_id' => $booking->id,
            'new_status' => $booking->status,
            'timestamp' => now()->toISOString(),
        ]);

        return $booking;
    }

    /**
     * Confirm booking
     */
    public function confirmBooking(Booking $booking, ?string $reason = null): Booking
    {
        return $this->changeStatus($booking, Booking::STATUS_CONFIRMED, $reason);
    }

    /**
     * Mark booking as paid
     */
    public function markAsPaid(Booking $booking, ?string $reason = null): Booking
    {
        return $this->changeStatus($booking, Booking::STATUS_PAID, $reason);
    }

    /**
     * Complete booking
     */
    public function completeBooking(Booking $booking, ?string $reason = null): Booking
    {
        return $this->changeStatus($booking, Booking::STATUS_COMPLETED, $reason);
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(Booking $booking, string $reason): Booking
    {
        return $this->changeStatus($booking, Booking::STATUS_CANCELLED, $reason);
    }

    /**
     * Mark booking as no-show
     */
    public function markAsNoShow(Booking $booking, ?string $reason = null): Booking
    {
        return $this->changeStatus($booking, Booking::STATUS_NO_SHOW, $reason);
    }

    /**
     * Auto-confirm eligible bookings
     */
    public function autoConfirmEligibleBookings(int $seasonId, int $schoolId): array
    {
        V5Logger::logBusinessEvent('booking_workflow', 'auto_confirm_started', [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
        ]);

        $eligibleBookings = $this->findEligibleForAutoConfirmation($seasonId, $schoolId);
        $confirmedBookings = [];

        foreach ($eligibleBookings as $booking) {
            try {
                $confirmedBooking = $this->confirmBooking($booking, 'Auto-confirmation');
                $confirmedBookings[] = $confirmedBooking;
                
                V5Logger::logBusinessEvent('booking_workflow', 'auto_confirmed', [
                    'booking_id' => $booking->id,
                ]);
            } catch (\Exception $e) {
                V5Logger::logSystemError($e, [
                    'operation' => 'auto_confirm_booking',
                    'booking_id' => $booking->id,
                ]);
            }
        }

        V5Logger::logBusinessEvent('booking_workflow', 'auto_confirm_completed', [
            'confirmed_count' => count($confirmedBookings),
            'eligible_count' => $eligibleBookings->count(),
        ]);

        return $confirmedBookings;
    }

    /**
     * Auto-complete finished bookings
     */
    public function autoCompleteFinishedBookings(int $seasonId, int $schoolId): array
    {
        V5Logger::logBusinessEvent('booking_workflow', 'auto_complete_started', [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
        ]);

        $finishedBookings = $this->findFinishedBookings($seasonId, $schoolId);
        $completedBookings = [];

        foreach ($finishedBookings as $booking) {
            try {
                $completedBooking = $this->completeBooking($booking, 'Auto-completion');
                $completedBookings[] = $completedBooking;
                
                V5Logger::logBusinessEvent('booking_workflow', 'auto_completed', [
                    'booking_id' => $booking->id,
                ]);
            } catch (\Exception $e) {
                V5Logger::logSystemError($e, [
                    'operation' => 'auto_complete_booking',
                    'booking_id' => $booking->id,
                ]);
            }
        }

        V5Logger::logBusinessEvent('booking_workflow', 'auto_complete_completed', [
            'completed_count' => count($completedBookings),
            'finished_count' => $finishedBookings->count(),
        ]);

        return $completedBookings;
    }

    /**
     * Cancel expired pending bookings
     */
    public function cancelExpiredPendingBookings(int $seasonId, int $schoolId, int $expirationHours = 24): array
    {
        V5Logger::logBusinessEvent('booking_workflow', 'cancel_expired_started', [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'expiration_hours' => $expirationHours,
        ]);

        $expiredBookings = $this->findExpiredPendingBookings($seasonId, $schoolId, $expirationHours);
        $cancelledBookings = [];

        foreach ($expiredBookings as $booking) {
            try {
                $cancelledBooking = $this->cancelBooking(
                    $booking, 
                    "Cancelled due to expiration after {$expirationHours} hours"
                );
                $cancelledBookings[] = $cancelledBooking;
                
                V5Logger::logBusinessEvent('booking_workflow', 'expired_cancelled', [
                    'booking_id' => $booking->id,
                ]);
            } catch (\Exception $e) {
                V5Logger::logSystemError($e, [
                    'operation' => 'cancel_expired_booking',
                    'booking_id' => $booking->id,
                ]);
            }
        }

        V5Logger::logBusinessEvent('booking_workflow', 'cancel_expired_completed', [
            'cancelled_count' => count($cancelledBookings),
            'expired_count' => $expiredBookings->count(),
        ]);

        return $cancelledBookings;
    }

    /**
     * Get booking workflow status summary
     */
    public function getWorkflowStatusSummary(int $seasonId, int $schoolId): array
    {
        return [
            'eligible_for_auto_confirm' => $this->findEligibleForAutoConfirmation($seasonId, $schoolId)->count(),
            'finished_bookings' => $this->findFinishedBookings($seasonId, $schoolId)->count(),
            'expired_pending' => $this->findExpiredPendingBookings($seasonId, $schoolId)->count(),
            'no_show_candidates' => $this->findNoShowCandidates($seasonId, $schoolId)->count(),
            'pending_payment' => $this->findPendingPaymentBookings($seasonId, $schoolId)->count(),
        ];
    }

    /**
     * Private helper methods
     */
    private function executePreTransitionActions(Booking $booking, string $newStatus, ?string $reason): void
    {
        switch ($newStatus) {
            case Booking::STATUS_CONFIRMED:
                $this->handleConfirmationPreActions($booking);
                break;
            case Booking::STATUS_PAID:
                $this->handlePaymentPreActions($booking);
                break;
            case Booking::STATUS_CANCELLED:
                $this->handleCancellationPreActions($booking, $reason);
                break;
            case Booking::STATUS_NO_SHOW:
                $this->handleNoShowPreActions($booking);
                break;
        }
    }

    private function executePostTransitionActions(Booking $booking, string $newStatus, ?string $reason): void
    {
        switch ($newStatus) {
            case Booking::STATUS_CONFIRMED:
                $this->handleConfirmationPostActions($booking);
                break;
            case Booking::STATUS_PAID:
                $this->handlePaymentPostActions($booking);
                break;
            case Booking::STATUS_COMPLETED:
                $this->handleCompletionPostActions($booking);
                break;
            case Booking::STATUS_CANCELLED:
                $this->handleCancellationPostActions($booking, $reason);
                break;
            case Booking::STATUS_NO_SHOW:
                $this->handleNoShowPostActions($booking);
                break;
        }
    }

    private function handleConfirmationPreActions(Booking $booking): void
    {
        // Validate availability before confirmation
        // Check equipment allocation
        // Reserve resources
    }

    private function handleConfirmationPostActions(Booking $booking): void
    {
        // Send confirmation email
        // Update calendar/schedule
        // Notify monitor
        // Log activity
    }

    private function handlePaymentPreActions(Booking $booking): void
    {
        // Validate payment records
        // Check payment amount matches booking total
    }

    private function handlePaymentPostActions(Booking $booking): void
    {
        // Send payment confirmation
        // Update financial records
        // Generate invoice/receipt
    }

    private function handleCompletionPostActions(Booking $booking): void
    {
        // Process equipment returns
        // Send feedback request
        // Update client history
        // Process loyalty points
    }

    private function handleCancellationPreActions(Booking $booking, ?string $reason): void
    {
        // Check cancellation policy
        // Calculate refund amount
        // Validate cancellation eligibility
    }

    private function handleCancellationPostActions(Booking $booking, ?string $reason): void
    {
        // Release reserved resources
        // Process refunds if applicable
        // Send cancellation notification
        // Update availability
    }

    private function handleNoShowPreActions(Booking $booking): void
    {
        // Verify no-show status
        // Check if client contacted school
    }

    private function handleNoShowPostActions(Booking $booking): void
    {
        // Apply no-show fees
        // Update client profile
        // Release resources
        // Send no-show notification
    }

    private function findEligibleForAutoConfirmation(int $seasonId, int $schoolId)
    {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->withStatus(Booking::STATUS_PENDING)
                     ->where('created_at', '>', now()->subHours(1)) // Not too old
                     ->whereNotNull('client_id')
                     ->get()
                     ->filter(function ($booking) {
                         // Add custom eligibility criteria
                         return $booking->isFullyPaid() || $this->hasValidPaymentMethod($booking);
                     });
    }

    private function findFinishedBookings(int $seasonId, int $schoolId)
    {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->withStatus(Booking::STATUS_PAID)
                     ->where('end_date', '<', now()->subHours(2)) // Finished 2+ hours ago
                     ->get();
    }

    private function findExpiredPendingBookings(int $seasonId, int $schoolId, int $expirationHours = 24)
    {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->withStatus(Booking::STATUS_PENDING)
                     ->where('created_at', '<', now()->subHours($expirationHours))
                     ->get();
    }

    private function findNoShowCandidates(int $seasonId, int $schoolId)
    {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PAID])
                     ->where('start_date', '<', now()->subHours(1)) // Started over 1 hour ago
                     ->get();
    }

    private function findPendingPaymentBookings(int $seasonId, int $schoolId)
    {
        return Booking::forSeason($seasonId)
                     ->forSchool($schoolId)
                     ->withStatus(Booking::STATUS_CONFIRMED)
                     ->where('start_date', '<=', now()->addDays(3)) // Starting within 3 days
                     ->get()
                     ->filter(function ($booking) {
                         return !$booking->isFullyPaid();
                     });
    }

    private function hasValidPaymentMethod(Booking $booking): bool
    {
        // Check if booking has a valid payment method or payment pending
        return $booking->payments()->where('status', 'pending')->exists();
    }
}