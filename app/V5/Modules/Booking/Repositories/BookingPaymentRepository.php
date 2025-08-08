<?php

namespace App\V5\Modules\Booking\Repositories;

use App\V5\Modules\Booking\Models\BookingPayment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * V5 Booking Payment Repository
 * 
 * Handles all booking payment data access operations.
 */
class BookingPaymentRepository
{
    /**
     * Create a new booking payment
     */
    public function create(array $data): BookingPayment
    {
        return BookingPayment::create($data);
    }

    /**
     * Find booking payment by ID
     */
    public function findById(int $id): ?BookingPayment
    {
        return BookingPayment::find($id);
    }

    /**
     * Find payment by reference
     */
    public function findByReference(string $reference): ?BookingPayment
    {
        return BookingPayment::where('payment_reference', $reference)->first();
    }

    /**
     * Find payment by gateway transaction ID
     */
    public function findByGatewayTransactionId(string $transactionId, ?string $gateway = null): ?BookingPayment
    {
        $query = BookingPayment::where('gateway_transaction_id', $transactionId);
        
        if ($gateway) {
            $query->where('gateway', $gateway);
        }
        
        return $query->first();
    }

    /**
     * Update booking payment
     */
    public function update(BookingPayment $payment, array $data): BookingPayment
    {
        $payment->update($data);
        return $payment->fresh();
    }

    /**
     * Delete booking payment
     */
    public function delete(BookingPayment $payment): bool
    {
        return $payment->delete();
    }

    /**
     * Get payments for a booking
     */
    public function getBookingPayments(int $bookingId): Collection
    {
        return BookingPayment::where('booking_id', $bookingId)
                            ->orderBy('created_at', 'asc')
                            ->get();
    }

    /**
     * Get completed payments for a booking
     */
    public function getCompletedBookingPayments(int $bookingId): Collection
    {
        return BookingPayment::where('booking_id', $bookingId)
                            ->completed()
                            ->orderBy('processed_at', 'asc')
                            ->get();
    }

    /**
     * Get total paid amount for a booking
     */
    public function getTotalPaidAmount(int $bookingId): float
    {
        return BookingPayment::where('booking_id', $bookingId)
                            ->completed()
                            ->sum('amount');
    }

    /**
     * Get pending payments for a booking
     */
    public function getPendingBookingPayments(int $bookingId): Collection
    {
        return BookingPayment::where('booking_id', $bookingId)
                            ->pending()
                            ->orderBy('created_at', 'asc')
                            ->get();
    }

    /**
     * Get failed payments for a booking
     */
    public function getFailedBookingPayments(int $bookingId): Collection
    {
        return BookingPayment::where('booking_id', $bookingId)
                            ->failed()
                            ->orderBy('created_at', 'desc')
                            ->get();
    }

    /**
     * Get payments by type
     */
    public function getPaymentsByType(string $type, int $seasonId, int $schoolId): Collection
    {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->ofType($type)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get payments by method
     */
    public function getPaymentsByMethod(string $method, int $seasonId, int $schoolId): Collection
    {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->byMethod($method)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get payments by status
     */
    public function getPaymentsByStatus(string $status, int $seasonId, int $schoolId): Collection
    {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->withStatus($status)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get payments within date range
     */
    public function getPaymentsByDateRange(
        Carbon $startDate,
        Carbon $endDate,
        int $seasonId,
        int $schoolId
    ): Collection {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(
        int $paymentId,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null
    ): ?BookingPayment {
        $payment = $this->findById($paymentId);
        
        if ($payment && $payment->isPending()) {
            return $payment->markAsCompleted($gatewayTransactionId, $gatewayResponse);
        }
        
        return null;
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(
        int $paymentId,
        ?string $reason = null,
        ?array $gatewayResponse = null
    ): ?BookingPayment {
        $payment = $this->findById($paymentId);
        
        if ($payment && ($payment->isPending() || $payment->status === BookingPayment::STATUS_PROCESSING)) {
            return $payment->markAsFailed($reason, $gatewayResponse);
        }
        
        return null;
    }

    /**
     * Process refund
     */
    public function processRefund(
        int $paymentId,
        float $amount,
        string $reason,
        bool $isFullRefund = false
    ): ?BookingPayment {
        $payment = $this->findById($paymentId);
        
        if ($payment && $payment->canBeRefunded()) {
            if ($isFullRefund) {
                return $payment->processFullRefund($reason);
            } else {
                return $payment->processPartialRefund($amount, $reason);
            }
        }
        
        return null;
    }

    /**
     * Create refund payment record
     */
    public function createRefund(array $data): BookingPayment
    {
        $data['payment_type'] = BookingPayment::TYPE_REFUND;
        $data['status'] = BookingPayment::STATUS_COMPLETED;
        $data['processed_at'] = now();
        
        return $this->create($data);
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats(int $seasonId, int $schoolId): array
    {
        $baseQuery = BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        });

        $stats = [];
        
        // Stats by status
        $statuses = BookingPayment::getValidStatuses();
        foreach ($statuses as $status) {
            $statusStats = (clone $baseQuery)->withStatus($status);
            $stats['by_status'][$status] = [
                'count' => $statusStats->count(),
                'total_amount' => $statusStats->sum('amount'),
            ];
        }
        
        // Stats by method
        $methods = BookingPayment::getValidMethods();
        foreach ($methods as $method) {
            $methodStats = (clone $baseQuery)->byMethod($method)->completed();
            $stats['by_method'][$method] = [
                'count' => $methodStats->count(),
                'total_amount' => $methodStats->sum('amount'),
            ];
        }
        
        // Stats by type
        $types = BookingPayment::getValidTypes();
        foreach ($types as $type) {
            $typeStats = (clone $baseQuery)->ofType($type)->completed();
            $stats['by_type'][$type] = [
                'count' => $typeStats->count(),
                'total_amount' => $typeStats->sum('amount'),
            ];
        }
        
        // Overall stats
        $completedPayments = (clone $baseQuery)->completed();
        $stats['overall'] = [
            'total_payments' => (clone $baseQuery)->count(),
            'completed_payments' => $completedPayments->count(),
            'pending_payments' => (clone $baseQuery)->pending()->count(),
            'failed_payments' => (clone $baseQuery)->failed()->count(),
            'total_revenue' => $completedPayments->sum('amount'),
            'total_fees' => $completedPayments->sum('fee_amount'),
            'net_revenue' => $completedPayments->sum('amount') - $completedPayments->sum('fee_amount'),
            'average_payment_amount' => $completedPayments->avg('amount') ?: 0,
            'total_refunded' => (clone $baseQuery)->refunded()->sum('refunded_amount'),
        ];
        
        return $stats;
    }

    /**
     * Get revenue report
     */
    public function getRevenueReport(
        int $seasonId,
        int $schoolId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        string $groupBy = 'day'
    ): array {
        $query = BookingPayment::whereHas('booking', function ($bookingQuery) use ($seasonId, $schoolId) {
            $bookingQuery->where('season_id', $seasonId)
                        ->where('school_id', $schoolId);
        })->completed();

        if ($startDate && $endDate) {
            $query->whereBetween('processed_at', [$startDate, $endDate]);
        }

        $payments = $query->get();
        
        $report = [];
        
        foreach ($payments as $payment) {
            $dateKey = $this->getDateKey($payment->processed_at, $groupBy);
            
            if (!isset($report[$dateKey])) {
                $report[$dateKey] = [
                    'date' => $dateKey,
                    'total_amount' => 0,
                    'total_fees' => 0,
                    'net_amount' => 0,
                    'payment_count' => 0,
                    'by_method' => [],
                    'by_type' => [],
                ];
            }
            
            $report[$dateKey]['total_amount'] += $payment->amount;
            $report[$dateKey]['total_fees'] += $payment->fee_amount ?: 0;
            $report[$dateKey]['net_amount'] += $payment->getNetAmountAttribute();
            $report[$dateKey]['payment_count']++;
            
            // Group by method
            $method = $payment->payment_method;
            if (!isset($report[$dateKey]['by_method'][$method])) {
                $report[$dateKey]['by_method'][$method] = ['count' => 0, 'amount' => 0];
            }
            $report[$dateKey]['by_method'][$method]['count']++;
            $report[$dateKey]['by_method'][$method]['amount'] += $payment->amount;
            
            // Group by type
            $type = $payment->payment_type;
            if (!isset($report[$dateKey]['by_type'][$type])) {
                $report[$dateKey]['by_type'][$type] = ['count' => 0, 'amount' => 0];
            }
            $report[$dateKey]['by_type'][$type]['count']++;
            $report[$dateKey]['by_type'][$type]['amount'] += $payment->amount;
        }
        
        return array_values($report);
    }

    /**
     * Get failed payments requiring attention
     */
    public function getFailedPaymentsRequiringAttention(
        int $seasonId,
        int $schoolId,
        int $hoursOld = 1
    ): Collection {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->failed()
        ->where('created_at', '>', now()->subHours($hoursOld))
        ->with(['booking.client'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get pending payments that might be stuck
     */
    public function getStuckPendingPayments(
        int $seasonId,
        int $schoolId,
        int $hoursOld = 6
    ): Collection {
        return BookingPayment::whereHas('booking', function ($query) use ($seasonId, $schoolId) {
            $query->where('season_id', $seasonId)
                  ->where('school_id', $schoolId);
        })
        ->pending()
        ->where('created_at', '<', now()->subHours($hoursOld))
        ->with(['booking.client'])
        ->orderBy('created_at', 'asc')
        ->get();
    }

    /**
     * Get date key for grouping
     */
    private function getDateKey(Carbon $date, string $groupBy): string
    {
        switch ($groupBy) {
            case 'hour':
                return $date->format('Y-m-d H:00');
            case 'day':
                return $date->format('Y-m-d');
            case 'week':
                return $date->startOfWeek()->format('Y-m-d');
            case 'month':
                return $date->format('Y-m');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('Y-m-d');
        }
    }
}