<?php

namespace App\Services\Admin\V3;

use App\Models\BookingUser;
use App\Models\Course;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardSummaryService
{
    public function getSummary(Request $request): array
    {
        $schoolId = (int) $request->input('school_id');
        $date = $request->input('date')
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $privateCourses = $this->countActiveCoursesByType($schoolId, 2);
        $groupCourses = $this->countActiveCoursesByType($schoolId, 1);
        $activeReservations = $this->countActiveReservations($schoolId, $date);
        $salesToday = $this->sumPaymentsForDate($schoolId, $date);

        return [
            'privateCourses' => $privateCourses,
            'groupCourses' => $groupCourses,
            'activeReservationsToday' => $activeReservations,
            'salesToday' => round((float) $salesToday, 2),
        ];
    }

    private function countActiveCoursesByType(int $schoolId, int $type): int
    {
        return Course::where('school_id', $schoolId)
            ->where('course_type', $type)
            ->where('active', 1)
            ->count();
    }

    private function countActiveReservations(int $schoolId, string $date): int
    {
        return BookingUser::whereHas('booking', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->where('status', '!=', 2);
            })
            ->where('status', 1)
            ->whereDate('date', $date)
            ->count();
    }

    private function sumPaymentsForDate(int $schoolId, string $date): float
    {
        return Payment::where('school_id', $schoolId)
            ->where('status', 'paid')
            ->whereDate('created_at', $date)
            ->sum('amount');
    }
}
