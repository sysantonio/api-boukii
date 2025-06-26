<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Utils;

/**
 * Class AnalyticsController - Simplified Version
 * @package App\Http\Controllers\Admin
 */
class AnalyticsController extends AppBaseController
{
    use Utils;

    public function summary(Request $request): JsonResponse
    {
        $schoolId = $request->input('school_id');
        $from = $request->input('start_date');
        $to = $request->input('end_date');

        if (!$schoolId || !$from || !$to) {
            return response()->json(['error' => 'Missing required parameters'], 422);
        }

        $analytics = new AnalyticsService($schoolId, $to);

        return response()->json([
            'totalPaid'        => $analytics->getTotalPaid(),
            'totalRefunds'     => $analytics->getRefunds(),
            'netRevenue'       => $analytics->getNetRevenue(),
            'expectedRevenue'  => $analytics->getExpectedRevenueFromCourses($analytics->getCourseIdsInRange($from, $to), $from, $to),
            'activeBookings'   => $analytics->getActiveBookings($from, $to),
            'withInsurance'    => $analytics->getBookingsWithInsurance($from, $to),
            'withVoucher'      => $analytics->getBookingsWithVoucher($from, $to),
        ]);
    }

    /**
     * Get analytics summary with real payment data
     */
    public function getSummary(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            // Calculate total paid (actual payments received)
            $totalPaid = $this->calculateTotalPaid($schoolId, $filters);

            // Calculate total refunds
            $totalRefunds = $this->calculateTotalRefunds($schoolId, $filters);

            // Calculate net revenue
            $netRevenue = $totalPaid - $totalRefunds;

            // Count active bookings
            $activeBookings = $this->countActiveBookings($schoolId, $filters);

            // Count bookings with insurance
            $withInsurance = $this->countBookingsWithInsurance($schoolId, $filters);

            // Count bookings with vouchers
            $withVoucher = $this->countBookingsWithVouchers($schoolId, $filters);

            return $this->sendResponse([
                'totalPaid' => round($totalPaid, 2),
                'activeBookings' => $activeBookings,
                'withInsurance' => $withInsurance,
                'withVoucher' => $withVoucher,
                'totalRefunds' => round($totalRefunds, 2),
                'netRevenue' => round($netRevenue, 2)
            ], 'Analytics summary retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Analytics Summary Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving analytics summary', 500);
        }
    }

    /**
     * Get course analytics with real revenue data
     */
    public function getCourseAnalytics(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            $sql = "
                SELECT
                    c.id as course_id,
                    c.name as course_name,
                    c.course_type,
                    SUM(CASE
                        WHEN b.paid = 1 THEN COALESCE(p.amount, b.paid_total)
                        ELSE 0
                    END) as total_revenue,
                    COUNT(DISTINCT b.id) as total_bookings,
                    AVG(CASE
                        WHEN b.paid = 1 THEN COALESCE(p.amount, b.paid_total)
                    END) as average_price,
                    SUM(CASE WHEN b.payment_method_id = 1 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as cash_amount,
                    SUM(CASE WHEN b.payment_method_id = 2 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as card_amount,
                    SUM(CASE WHEN b.payment_method_id = 3 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as online_amount,
                    SUM(CASE WHEN b.paid = 0 THEN (b.price_total - COALESCE(b.paid_total, 0)) ELSE 0 END) as pending_amount
                FROM booking_users bu
                INNER JOIN bookings b ON bu.booking_id = b.id
                INNER JOIN courses c ON bu.course_id = c.id
                LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
                WHERE bu.school_id = ?
                    AND bu.status = 1
                    AND b.status != 2
                    AND bu.date BETWEEN ? AND ?
            ";

            $params = [$schoolId, $filters['start_date'], $filters['end_date']];

            // Add optional filters
            if ($filters['course_type']) {
                $sql .= " AND c.course_type = ?";
                $params[] = $filters['course_type'];
            }

            if ($filters['source']) {
                $sql .= " AND b.source = ?";
                $params[] = $filters['source'];
            }

            if ($filters['sport_id']) {
                $sql .= " AND c.sport_id = ?";
                $params[] = $filters['sport_id'];
            }

            if ($filters['only_weekends']) {
                $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
            }

            $sql .= " GROUP BY c.id, c.name, c.course_type HAVING total_revenue > 0 ORDER BY total_revenue DESC";

            $courseAnalytics = DB::select($sql, $params);

            // Get voucher amounts separately
            $voucherAmounts = $this->getVoucherAmountsByCourse($schoolId, $filters);

            $formattedData = array_map(function($row) use ($voucherAmounts) {
                $voucherAmount = $voucherAmounts[$row->course_id] ?? 0;

                return [
                    'courseId' => $row->course_id,
                    'courseName' => $row->course_name,
                    'courseType' => $row->course_type,
                    'totalRevenue' => (float) $row->total_revenue,
                    'totalBookings' => (int) $row->total_bookings,
                    'averagePrice' => (float) $row->average_price,
                    'completionRate' => 0.0, // Will calculate separately if needed
                    'paymentMethods' => [
                        'cash' => (float) $row->cash_amount,
                        'card' => (float) $row->card_amount,
                        'online' => (float) $row->online_amount,
                        'vouchers' => (float) $voucherAmount,
                        'pending' => (float) $row->pending_amount
                    ]
                ];
            }, $courseAnalytics);

            return $this->sendResponse($formattedData, 'Course analytics retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Course Analytics Error: ', $e->getTrace());
            return $this->sendError('Error retrieving course analytics', 500);
        }
    }

    /**
     * Get revenue analytics by date range
     */
    public function getRevenueAnalytics(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            // Determine grouping interval based on date range
            $startDate = Carbon::parse($filters['start_date']);
            $endDate = Carbon::parse($filters['end_date']);
            $daysDiff = $endDate->diffInDays($startDate);

            if ($daysDiff <= 31) {
                $groupBy = "DATE(bu.date)";
                $selectPeriod = "DATE(bu.date) as period";
                $selectDate = "DATE_FORMAT(DATE(bu.date), '%Y-%m-%d') as formatted_date";
            } elseif ($daysDiff <= 180) {
                $groupBy = "YEARWEEK(bu.date, 1)";
                $selectPeriod = "YEARWEEK(bu.date, 1) as period";
                $selectDate = "CONCAT(YEAR(bu.date), '-W', LPAD(WEEK(bu.date, 1), 2, '0')) as formatted_date";
            } else {
                $groupBy = "period";
                $selectPeriod = "CONCAT(YEAR(bu.date), '-', LPAD(MONTH(bu.date), 2, '0')) as period";
                $selectDate = "DATE_FORMAT(bu.date, '%Y-%m') as formatted_date";
            }

            $params = [$schoolId, $filters['start_date'], $filters['end_date']];

            $sql = "
SELECT
    sub.period,
    sub.revenue,
    sub.bookings,
    sub.refunds,
    sub.cash_amount,
    sub.card_amount,
    sub.online_amount,
    sub.pending_amount,
    CASE
        WHEN LENGTH(sub.period) = 10 THEN DATE_FORMAT(STR_TO_DATE(sub.period, '%Y-%m-%d'), '%Y-%m-%d')
        WHEN LENGTH(sub.period) = 7 THEN sub.period
        ELSE sub.period
    END AS formatted_date
FROM (
    SELECT
        {$selectPeriod},
        SUM(CASE WHEN b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) AS revenue,
        COUNT(DISTINCT b.id) AS bookings,
        SUM(CASE WHEN p.status = 'refund' THEN p.amount ELSE 0 END) AS refunds,
        SUM(CASE WHEN b.payment_method_id = 1 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) AS cash_amount,
        SUM(CASE WHEN b.payment_method_id = 2 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) AS card_amount,
        SUM(CASE WHEN b.payment_method_id = 3 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) AS online_amount,
        SUM(CASE WHEN b.paid = 0 THEN (b.price_total - COALESCE(b.paid_total, 0)) ELSE 0 END) AS pending_amount
    FROM booking_users bu
    INNER JOIN bookings b ON bu.booking_id = b.id
    INNER JOIN courses c ON bu.course_id = c.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE bu.school_id = ?
        AND bu.status = 1
        AND b.status != 2
        AND bu.date BETWEEN ? AND ?
";

            if ($filters['course_type']) {
                $sql .= " AND c.course_type = ?";
                $params[] = $filters['course_type'];
            }

            if ($filters['source']) {
                $sql .= " AND b.source = ?";
                $params[] = $filters['source'];
            }

            if ($filters['sport_id']) {
                $sql .= " AND c.sport_id = ?";
                $params[] = $filters['sport_id'];
            }

            if ($filters['only_weekends']) {
                $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
            }

            $sql .= " GROUP BY {$groupBy}
) AS sub
ORDER BY sub.period ASC";

            $revenueData = DB::select($sql, $params);

            $vouchersByPeriod = $this->getVoucherAmountsByPeriod($schoolId, $filters, $groupBy);

            $formattedData = array_map(function ($row) use ($vouchersByPeriod) {
                $voucherAmount = $vouchersByPeriod[$row->period] ?? 0;
                $netRevenue = $row->revenue - $row->refunds;

                return [
                    'date' => $row->formatted_date,
                    'revenue' => (float)$row->revenue,
                    'bookings' => (int)$row->bookings,
                    'refunds' => (float)$row->refunds,
                    'netRevenue' => (float)$netRevenue,
                    'paymentMethods' => [
                        'cash' => (float)$row->cash_amount,
                        'card' => (float)$row->card_amount,
                        'online' => (float)$row->online_amount,
                        'vouchers' => (float)$voucherAmount,
                        'pending' => (float)$row->pending_amount
                    ]
                ];
            }, $revenueData);

            return $this->sendResponse($formattedData, 'Revenue analytics retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Revenue Analytics Error: ', ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
            return $this->sendError('Error retrieving revenue analytics', 500);
        }
    }



    /**
     * Get pending payments report
     */
    public function getPendingPayments(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            $sql = "
                SELECT
                    b.id as booking_id,
                    b.created_at as booking_date,
                    bu.date as service_date,
                    c.name as course_name,
                    c.course_type,
                    CONCAT(cl.first_name, ' ', cl.last_name) as client_name,
                    cl.email as client_email,
                    cl.phone as client_phone,
                    b.price_total,
                    COALESCE(b.paid_total, 0) as paid_amount,
                    (b.price_total - COALESCE(b.paid_total, 0)) as pending_amount,
                    b.payment_method_id,
                    DATEDIFF(bu.date, NOW()) as days_until_service,
                    CASE
                        WHEN bu.date < NOW() THEN 'overdue'
                        WHEN DATEDIFF(bu.date, NOW()) <= 2 THEN 'urgent'
                        WHEN DATEDIFF(bu.date, NOW()) <= 7 THEN 'due_soon'
                        ELSE 'normal'
                    END as urgency_level
                FROM booking_users bu
                INNER JOIN bookings b ON bu.booking_id = b.id
                INNER JOIN courses c ON bu.course_id = c.id
                INNER JOIN clients cl ON b.client_main_id = cl.id
                WHERE bu.school_id = ?
                    AND bu.status = 1
                    AND b.status != 2
                    AND b.paid = 0
                    AND (b.price_total - COALESCE(b.paid_total, 0)) > 0
                    AND bu.date BETWEEN ? AND ?
            ";

            $params = [$schoolId, $filters['start_date'], $filters['end_date']];

            // Add optional filters
            if ($filters['course_type']) {
                $sql .= " AND c.course_type = ?";
                $params[] = $filters['course_type'];
            }

            if ($filters['source']) {
                $sql .= " AND b.source = ?";
                $params[] = $filters['source'];
            }

            if ($filters['sport_id']) {
                $sql .= " AND c.sport_id = ?";
                $params[] = $filters['sport_id'];
            }

            if ($filters['only_weekends']) {
                $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
            }

            $sql .= " ORDER BY urgency_level, bu.date ASC";

            $pendingPayments = DB::select($sql, $params);

            $formattedData = array_map(function($row) {
                return [
                    'bookingId' => $row->booking_id,
                    'bookingDate' => $row->booking_date,
                    'serviceDate' => $row->service_date,
                    'courseName' => $row->course_name,
                    'courseType' => $row->course_type,
                    'clientName' => $row->client_name,
                    'clientEmail' => $row->client_email,
                    'clientPhone' => $row->client_phone,
                    'totalPrice' => (float) $row->price_total,
                    'paidAmount' => (float) $row->paid_amount,
                    'pendingAmount' => (float) $row->pending_amount,
                    'paymentMethodId' => $row->payment_method_id,
                    'daysUntilService' => $row->days_until_service,
                    'urgencyLevel' => $row->urgency_level
                ];
            }, $pendingPayments);

            // Group by urgency level
            $groupedByUrgency = [
                'overdue' => [],
                'urgent' => [],
                'due_soon' => [],
                'normal' => []
            ];

            foreach ($formattedData as $payment) {
                $groupedByUrgency[$payment['urgencyLevel']][] = $payment;
            }

            return $this->sendResponse([
                'pending_payments' => $formattedData,
                'grouped_by_urgency' => $groupedByUrgency,
                'summary' => [
                    'total_pending_amount' => array_sum(array_column($formattedData, 'pendingAmount')),
                    'total_pending_count' => count($formattedData),
                    'overdue_count' => count($groupedByUrgency['overdue']),
                    'urgent_count' => count($groupedByUrgency['urgent']),
                    'due_soon_count' => count($groupedByUrgency['due_soon'])
                ]
            ], 'Pending payments retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Pending Payments Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving pending payments', 500);
        }
    }

    /**
     * Helper methods
     */
    private function buildFilters(Request $request, int $schoolId): array
    {
        $today = Carbon::now()->format('Y-m-d');
        $season = \App\Models\Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        return [
            'start_date' => $request->start_date ?? $season->start_date ?? Carbon::now()->startOf('month')->format('Y-m-d'),
            'end_date' => $request->end_date ?? $season->end_date ?? Carbon::now()->endOf('month')->format('Y-m-d'),
            'course_type' => $request->course_type,
            'source' => $request->source,
            'sport_id' => $request->sport_id,
            'only_weekends' => $request->boolean('only_weekends', false)
        ];
    }

    private function calculateTotalPaid(int $schoolId, array $filters): float
    {
        $sql = "
            SELECT SUM(CASE WHEN b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as total
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);
        return (float) ($result[0]->total ?? 0);
    }

    private function calculateTotalRefunds(int $schoolId, array $filters): float
    {
        $sql = "
            SELECT SUM(p.amount) as total
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            INNER JOIN payments p ON b.id = p.booking_id
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND p.status = 'refund'
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);
        return (float) ($result[0]->total ?? 0);
    }

    private function countActiveBookings(int $schoolId, array $filters): int
    {
        $sql = "
            SELECT COUNT(DISTINCT b.id) as total
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);
        return (int) ($result[0]->total ?? 0);
    }

    private function countBookingsWithInsurance(int $schoolId, array $filters): int
    {
        $sql = "
            SELECT COUNT(DISTINCT b.id) as total
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND b.has_cancellation_insurance = 1
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);
        return (int) ($result[0]->total ?? 0);
    }

    private function countBookingsWithVouchers(int $schoolId, array $filters): int
    {
        $sql = "
            SELECT COUNT(DISTINCT b.id) as total
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            INNER JOIN vouchers_log vl ON b.id = vl.booking_id
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);
        return (int) ($result[0]->total ?? 0);
    }

    private function getVoucherAmountsByCourse(int $schoolId, array $filters): array
    {
        $sql = "
            SELECT
                bu.course_id,
                SUM(ABS(vl.amount)) as voucher_amount
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            INNER JOIN vouchers_log vl ON b.id = vl.booking_id
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $sql .= " GROUP BY bu.course_id";

        $results = DB::select($sql, $params);

        $vouchers = [];
        foreach ($results as $result) {
            $vouchers[$result->course_id] = (float) $result->voucher_amount;
        }

        return $vouchers;
    }

    private function getVoucherAmountsByPeriod(int $schoolId, array $filters, string $groupBy): array
    {
        if (strpos($groupBy, 'DATE') !== false) {
            $periodSelect = "DATE(bu.date) as period";
            $periodGroupBy = "period";
        } elseif (strpos($groupBy, 'YEARWEEK') !== false) {
            $periodSelect = "YEARWEEK(bu.date, 1) as period";
            $periodGroupBy = "period";
        } else {
            $periodSelect = "CONCAT(YEAR(bu.date), '-', LPAD(MONTH(bu.date), 2, '0')) as period";
            $periodGroupBy = "period";
        }

        $sql = "
        SELECT
            {$periodSelect},
            SUM(ABS(vl.amount)) as voucher_amount
        FROM booking_users bu
        INNER JOIN bookings b ON bu.booking_id = b.id
        INNER JOIN courses c ON bu.course_id = c.id
        INNER JOIN vouchers_log vl ON b.id = vl.booking_id
        WHERE bu.school_id = ?
            AND bu.status = 1
            AND b.status != 2
            AND bu.date BETWEEN ? AND ?
    ";

        $params = [$schoolId, $filters['start_date'], $filters['end_date']];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $sql .= " GROUP BY {$periodGroupBy} ORDER BY {$periodGroupBy} ASC";

        $results = DB::select($sql, $params);

        $vouchers = [];
        foreach ($results as $result) {
            $vouchers[$result->period] = (float) $result->voucher_amount;
        }

        return $vouchers;
    }

    /**
     * Get financial dashboard data
     */
    public function getFinancialDashboard(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            $totalRevenue = $this->calculateTotalPaid($schoolId, $filters);
            $totalRefunds = $this->calculateTotalRefunds($schoolId, $filters);
            $netRevenue = $totalRevenue - $totalRefunds;
            $activeBookings = $this->countActiveBookings($schoolId, $filters);
            $withInsurance = $this->countBookingsWithInsurance($schoolId, $filters);
            $withVouchers = $this->countBookingsWithVouchers($schoolId, $filters);

            // Get payment method breakdown
            $paymentBreakdown = $this->getPaymentMethodBreakdown($schoolId, $filters);

            return $this->sendResponse([
                'financial_summary' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'net_revenue' => round($netRevenue, 2),
                    'total_refunds' => round($totalRefunds, 2),
                    'average_booking_value' => $activeBookings > 0 ? round($totalRevenue / $activeBookings, 2) : 0
                ],
                'payment_breakdown' => $paymentBreakdown,
                'booking_metrics' => [
                    'total_bookings' => $activeBookings,
                    'with_insurance' => $withInsurance,
                    'with_vouchers' => $withVouchers,
                    'conversion_rate' => 1.0 // Simplified for now
                ]
            ], 'Financial dashboard data retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Financial Dashboard Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving financial dashboard data', 500);
        }
    }

    private function getPaymentMethodBreakdown(int $schoolId, array $filters): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN b.payment_method_id = 1 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as cash,
                SUM(CASE WHEN b.payment_method_id = 2 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as card,
                SUM(CASE WHEN b.payment_method_id = 3 AND b.paid = 1 THEN COALESCE(p.amount, b.paid_total) ELSE 0 END) as online,
                COALESCE(voucher_totals.voucher_amount, 0) as vouchers
            FROM booking_users bu
            INNER JOIN bookings b ON bu.booking_id = b.id
            INNER JOIN courses c ON bu.course_id = c.id
            LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
            LEFT JOIN (
                SELECT SUM(ABS(vl.amount)) as voucher_amount
                FROM booking_users bu2
                INNER JOIN bookings b2 ON bu2.booking_id = b2.id
                INNER JOIN courses c2 ON bu2.course_id = c2.id
                INNER JOIN vouchers_log vl ON b2.id = vl.booking_id
                WHERE bu2.school_id = ?
                    AND bu2.status = 1
                    AND b2.status != 2
                    AND bu2.date BETWEEN ? AND ?
            ) as voucher_totals ON 1=1
            WHERE bu.school_id = ?
                AND bu.status = 1
                AND b.status != 2
                AND bu.date BETWEEN ? AND ?
        ";

        $params = [
            $schoolId, $filters['start_date'], $filters['end_date'], // voucher subquery
            $schoolId, $filters['start_date'], $filters['end_date']  // main query
        ];

        if ($filters['course_type']) {
            $sql .= " AND c.course_type = ?";
            $params[] = $filters['course_type'];
        }

        if ($filters['source']) {
            $sql .= " AND b.source = ?";
            $params[] = $filters['source'];
        }

        if ($filters['sport_id']) {
            $sql .= " AND c.sport_id = ?";
            $params[] = $filters['sport_id'];
        }

        if ($filters['only_weekends']) {
            $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
        }

        $result = DB::select($sql, $params);

        if (empty($result)) {
            return ['cash' => 0, 'card' => 0, 'online' => 0, 'vouchers' => 0];
        }

        return [
            'cash' => round((float) $result[0]->cash, 2),
            'card' => round((float) $result[0]->card, 2),
            'online' => round((float) $result[0]->online, 2),
            'vouchers' => round((float) $result[0]->vouchers, 2)
        ];
    }

    /**
     * Get performance comparison between periods
     */
    public function getPerformanceComparison(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            // Calculate previous period dates
            $currentStart = Carbon::parse($filters['start_date']);
            $currentEnd = Carbon::parse($filters['end_date']);
            $periodDays = $currentEnd->diffInDays($currentStart);

            $previousStart = $currentStart->copy()->subDays($periodDays + 1);
            $previousEnd = $currentStart->copy()->subDay();

            // Get current period data
            $currentRevenue = $this->calculateTotalPaid($schoolId, $filters);
            $currentBookings = $this->countActiveBookings($schoolId, $filters);

            // Get previous period data
            $previousFilters = array_merge($filters, [
                'start_date' => $previousStart->format('Y-m-d'),
                'end_date' => $previousEnd->format('Y-m-d')
            ]);
            $previousRevenue = $this->calculateTotalPaid($schoolId, $previousFilters);
            $previousBookings = $this->countActiveBookings($schoolId, $previousFilters);

            // Calculate percentage changes
            $revenueChange = $previousRevenue > 0 ?
                (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

            $bookingsChange = $previousBookings > 0 ?
                (($currentBookings - $previousBookings) / $previousBookings) * 100 : 0;

            return $this->sendResponse([
                'current_period' => [
                    'start_date' => $filters['start_date'],
                    'end_date' => $filters['end_date'],
                    'revenue' => $currentRevenue,
                    'bookings' => $currentBookings
                ],
                'previous_period' => [
                    'start_date' => $previousStart->format('Y-m-d'),
                    'end_date' => $previousEnd->format('Y-m-d'),
                    'revenue' => $previousRevenue,
                    'bookings' => $previousBookings
                ],
                'comparison' => [
                    'revenue_change_percent' => round($revenueChange, 2),
                    'bookings_change_percent' => round($bookingsChange, 2),
                    'revenue_trend' => $revenueChange >= 0 ? 'up' : 'down',
                    'bookings_trend' => $bookingsChange >= 0 ? 'up' : 'down'
                ]
            ], 'Performance comparison retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Performance Comparison Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving performance comparison', 500);
        }
    }

    /**
     * Export analytics to CSV
     */
    public function exportToCSV(Request $request): JsonResponse
    {
        try {
            $schoolId = $this->getSchool($request)->id;
            $filters = $this->buildFilters($request, $schoolId);
            $exportType = $request->input('export_type', 'summary');

            switch ($exportType) {
                case 'courses':
                    $response = $this->getCourseAnalytics($request);
                    $data = json_decode($response->getContent(), true)['data'];
                    $filename = 'course-analytics-' . date('Y-m-d') . '.csv';
                    break;
                case 'revenue':
                    $response = $this->getRevenueAnalytics($request);
                    $data = json_decode($response->getContent(), true)['data'];
                    $filename = 'revenue-analytics-' . date('Y-m-d') . '.csv';
                    break;
                default:
                    $response = $this->getSummary($request);
                    $data = [json_decode($response->getContent(), true)['data']];
                    $filename = 'analytics-summary-' . date('Y-m-d') . '.csv';
            }

            $csvContent = $this->arrayToCsv($data);

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' , $e->getTrace());
            return $this->sendError('Error exporting to CSV', 500);
        }
    }

    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Flatten nested arrays for CSV
        $flattenedData = [];
        foreach ($data as $row) {
            $flattenedRow = [];
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $flattenedRow[$key . '_' . $subKey] = $subValue;
                    }
                } else {
                    $flattenedRow[$key] = $value;
                }
            }
            $flattenedData[] = $flattenedRow;
        }

        // Write headers
        if (!empty($flattenedData)) {
            fputcsv($output, array_keys($flattenedData[0]));

            // Write data rows
            foreach ($flattenedData as $row) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get real-time dashboard data
     */
    public function getRealtimeDashboard(Request $request): JsonResponse
    {
        try {
            $schoolId = $this->getSchool($request)->id;

            // Get today's data
            $today = Carbon::today();
            $filters = [
                'start_date' => $today->format('Y-m-d'),
                'end_date' => $today->format('Y-m-d'),
                'course_type' => null,
                'source' => null,
                'sport_id' => null,
                'only_weekends' => false
            ];

            $todayRevenue = $this->calculateTotalPaid($schoolId, $filters);
            $todayBookings = $this->countActiveBookings($schoolId, $filters);

            // Get recent bookings (last 24 hours)
            $recentBookings = DB::select("
                SELECT
                    b.id,
                    b.created_at,
                    c.name as course_name,
                    CONCAT(cl.first_name, ' ', cl.last_name) as client_name,
                    b.price_total,
                    b.paid
                FROM bookings b
                INNER JOIN booking_users bu ON b.id = bu.booking_id
                INNER JOIN courses c ON bu.course_id = c.id
                INNER JOIN clients cl ON b.client_main_id = cl.id
                WHERE bu.school_id = ?
                    AND b.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND b.status != 2
                ORDER BY b.created_at DESC
                LIMIT 10
            ", [$schoolId]);

            return $this->sendResponse([
                'today_summary' => [
                    'revenue' => $todayRevenue,
                    'bookings' => $todayBookings
                ],
                'recent_bookings' => $recentBookings,
                'last_updated' => now()->toISOString()
            ], 'Real-time dashboard data retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Real-time Dashboard Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving real-time data', 500);
        }
    }

    /**
     * Get payment details for a specific period
     */
    public function getPaymentDetails(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $filters = $this->buildFilters($request, $schoolId);

        try {
            $sql = "
                SELECT
                    b.id as booking_id,
                    b.created_at as booking_date,
                    bu.date as service_date,
                    c.name as course_name,
                    c.course_type,
                    CONCAT(cl.first_name, ' ', cl.last_name) as client_name,
                    b.price_total,
                    b.paid_total,
                    b.paid as is_paid,
                    b.payment_method_id,
                    p.amount as payment_amount,
                    p.status as payment_status,
                    p.created_at as payment_date,
                    b.has_cancellation_insurance,
                    b.price_cancellation_insurance
                FROM booking_users bu
                INNER JOIN bookings b ON bu.booking_id = b.id
                INNER JOIN courses c ON bu.course_id = c.id
                INNER JOIN clients cl ON b.client_main_id = cl.id
                LEFT JOIN payments p ON b.id = p.booking_id
                WHERE bu.school_id = ?
                    AND bu.status = 1
                    AND b.status != 2
                    AND bu.date BETWEEN ? AND ?
            ";

            $params = [$schoolId, $filters['start_date'], $filters['end_date']];

            // Add optional filters
            if ($filters['course_type']) {
                $sql .= " AND c.course_type = ?";
                $params[] = $filters['course_type'];
            }

            if ($filters['source']) {
                $sql .= " AND b.source = ?";
                $params[] = $filters['source'];
            }

            if ($filters['sport_id']) {
                $sql .= " AND c.sport_id = ?";
                $params[] = $filters['sport_id'];
            }

            if ($filters['only_weekends']) {
                $sql .= " AND WEEKDAY(bu.date) IN (5, 6)";
            }

            $sql .= " ORDER BY b.created_at DESC";

            $paymentDetails = DB::select($sql, $params);

            $formattedData = array_map(function($row) {
                return [
                    'bookingId' => $row->booking_id,
                    'bookingDate' => $row->booking_date,
                    'serviceDate' => $row->service_date,
                    'courseName' => $row->course_name,
                    'courseType' => $row->course_type,
                    'clientName' => $row->client_name,
                    'totalPrice' => (float) $row->price_total,
                    'paidAmount' => (float) $row->paid_total,
                    'isPaid' => (bool) $row->is_paid,
                    'paymentMethodId' => $row->payment_method_id,
                    'paymentAmount' => (float) ($row->payment_amount ?? 0),
                    'paymentStatus' => $row->payment_status,
                    'paymentDate' => $row->payment_date,
                    'hasInsurance' => (bool) $row->has_cancellation_insurance,
                    'insurancePrice' => (float) ($row->price_cancellation_insurance ?? 0)
                ];
            }, $paymentDetails);

            return $this->sendResponse($formattedData, 'Payment details retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Payment Details Error: ' , $e->getTrace());
            return $this->sendError('Error retrieving payment details', 500);
        }
    }
}
