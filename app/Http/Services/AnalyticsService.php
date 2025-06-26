<?php

namespace App\Http\Services;

use App\Models\BookingUser;
use App\Models\CourseDate;
use App\Models\Payment;
use App\Traits\Utils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    protected int $schoolId;
    protected Carbon $toDate;
    use Utils;

    public function __construct(int $schoolId, ?string $toDate = null)
    {
        $this->schoolId = $schoolId;
        $this->toDate = $toDate ? Carbon::parse($toDate) : now();
    }

    public function getTotalPaid(): float
    {
        return $this->basePayments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getRefunds(): float
    {
        return $this->basePayments()
            ->whereIn('status', ['refund', 'partial_refund'])
            ->sum('amount');
    }

    public function getNetRevenue(): float
    {
        return $this->getTotalPaid() - $this->getRefunds();
    }

    public function getBreakdownByMethod(): array
    {
        $payments = $this->basePayments()->filter(fn($p) => $p->status === 'paid');

        $summary = [
            'cash' => 0,
            'card' => 0,
            'online' => 0,
            'vouchers' => 0,
            'other' => 0,
            'pending' => 0,
        ];

        foreach ($payments as $payment) {
            $method = 'other';

            if ($payment->notes === 'voucher') {
                $method = 'vouchers';
            } elseif (!empty($payment->payrexx_reference)) {
                $method = 'online';
            } elseif ($payment->booking) {
                switch ($payment->booking->payment_method_id) {
                    case 1: $method = 'cash'; break;
                    case 2: $method = 'card'; break; // Boukii terminal
                    case 3: $method = 'online'; break; // Email link
                    case 4: $method = 'other'; break;
                    case 5: $method = 'pending'; break;
                }
            }

            $summary[$method] += $payment->amount;
        }

        return $summary;
    }

    public function getExpectedRevenueFromCourses(array $courseIds, string $from, string $to): float
    {
        $courseDates = CourseDate::whereIn('course_id', $courseIds)
            ->whereBetween('date', [$from, $to])
            ->pluck('date')
            ->unique();

        if ($courseDates->isEmpty()) {
            return 0;
        }

        $bookingUsers = BookingUser::whereHas('booking', function ($q) {
            $q->where('school_id', $this->schoolId)
                ->where('status', '!=', 'cancelled');
        })
            ->whereIn('date', $courseDates)
            ->with(['course', 'booking', 'bookingUserExtras.courseExtra'])
            ->get();

        return $bookingUsers->sum(fn($user) => $this->calculateTotalPrice($user)['totalPrice'] ?? 0);
    }

    public function getCourseIdsInRange(string $from, string $to): array
    {
        return CourseDate::whereBetween('date', [$from, $to])
            ->pluck('course_id')
            ->unique()
            ->toArray();
    }

    public function getActiveBookings(string $from, string $to): int
    {
        return BookingUser::whereHas('booking', function ($q) {
            $q->where('school_id', $this->schoolId)
                ->where('status', '!=', 'cancelled');
        })
            ->whereBetween('date', [$from, $to])
            ->count();
    }

    public function getBookingsWithInsurance(string $from, string $to): int
    {
        return BookingUser::whereHas('booking', function ($q) {
            $q->where('school_id', $this->schoolId)
                ->where('status', '!=', 'cancelled')
                ->where('has_cancellation_insurance', true);
        })
            ->whereBetween('date', [$from, $to])
            ->count();
    }

    public function getBookingsWithVoucher(string $from, string $to): int
    {
        return Payment::whereHas('booking', function ($q) {
            $q->where('school_id', $this->schoolId)
                ->where('status', '!=', 'cancelled');
        })
            ->where('notes', 'voucher')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    protected function basePayments(): Collection
    {
        return Payment::with('booking')
            ->whereHas('booking', function ($q) {
                $q->where('school_id', $this->schoolId)
                    ->where('status', '!=', 'cancelled');
            })
            ->whereDate('created_at', '<=', $this->toDate)
            ->get();
    }
}
