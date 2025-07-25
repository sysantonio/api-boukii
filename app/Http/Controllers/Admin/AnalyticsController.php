<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Services\AnalyticsService;
use App\Models\Booking;
use App\Services\Finance\SeasonFinanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Utils;
use App\Models\BookingUser;
use App\Models\Course;
use App\Models\Monitor;


/**
 * Class AnalyticsController - Simplified Version
 * @package App\Http\Controllers\Admin
 */
class AnalyticsController extends AppBaseController
{
    use Utils;

    protected SeasonFinanceService $seasonFinanceService;

    public function __construct(SeasonFinanceService $seasonFinanceService)
    {
        $this->seasonFinanceService = $seasonFinanceService;
    }

    /**
     * Obtener análisis de ingresos por período (diario, semanal, mensual)
     * GET /api/admin/analytics/revenue-by-period
     */
    public function getRevenueByPeriod(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period' => 'required|in:daily,weekly,monthly',
            'course_type' => 'nullable|integer',
            'sport_id' => 'nullable|integer|exists:sports,id'
        ]);

        try {
            $revenueData = $this->calculateRevenueByPeriod($request);

            return $this->sendResponse($revenueData, 'Análisis de ingresos por período obtenido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error calculando ingresos por período', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error calculando ingresos por período: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener análisis detallado de cursos con métricas financieras
     * GET /api/admin/analytics/courses-detailed
     */
    public function getDetailedCourseAnalytics(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'course_type' => 'nullable|integer',
            'sport_id' => 'nullable|integer|exists:sports,id'
        ]);

        try {
            $courseAnalytics = $this->calculateDetailedCourseAnalytics($request);

            return $this->sendResponse($courseAnalytics, 'Análisis detallado de cursos obtenido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error en análisis detallado de cursos', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error en análisis de cursos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener análisis de eficiencia de monitores
     * GET /api/admin/analytics/monitors-efficiency
     */
    public function getMonitorEfficiencyAnalytics(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sport_id' => 'nullable|integer|exists:sports,id'
        ]);

        try {
            $monitorAnalytics = $this->calculateMonitorEfficiency($request);

            return $this->sendResponse($monitorAnalytics, 'Análisis de eficiencia de monitores obtenido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error en análisis de monitores', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error en análisis de monitores: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener análisis de conversión y abandono
     * GET /api/admin/analytics/conversion-analysis
     */
    public function getConversionAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'source' => 'nullable|string'
        ]);

        try {
            $conversionData = $this->calculateConversionMetrics($request);

            return $this->sendResponse($conversionData, 'Análisis de conversión obtenido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error en análisis de conversión', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error en análisis de conversión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener tendencias y predicciones
     * GET /api/admin/analytics/trends-prediction
     */
    public function getTrendsAndPredictions(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'analysis_months' => 'nullable|integer|min:3|max:24',
            'prediction_months' => 'nullable|integer|min:1|max:6'
        ]);

        try {
            $trendsData = $this->calculateTrendsAndPredictions($request);

            return $this->sendResponse($trendsData, 'Análisis de tendencias obtenido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error en análisis de tendencias', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error en análisis de tendencias: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener métricas de tiempo real para dashboard
     * GET /api/admin/analytics/realtime-metrics
     */
    public function getRealtimeMetrics(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,id'
        ]);

        try {
            $realtimeData = $this->calculateRealtimeMetrics($request);

            return $this->sendResponse($realtimeData, 'Métricas en tiempo real obtenidas exitosamente');

        } catch (\Exception $e) {
            Log::error('Error obteniendo métricas tiempo real', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->sendError('Error obteniendo métricas: ' . $e->getMessage(), 500);
        }
    }

    // ==================== MÉTODOS PRIVADOS DE CÁLCULO ====================

    /**
     * Calcular ingresos por período
     */
    private function calculateRevenueByPeriod(Request $request): array
    {
        $schoolId = $request->school_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $period = $request->period;

        // Definir formato de agrupación según período
        $groupFormat = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m'
        };

        $query = BookingUser::select([
            DB::raw("DATE_FORMAT(date, '{$groupFormat}') as period"),
            DB::raw('DATE(date) as date'),
            DB::raw('COUNT(*) as total_bookings'),
            DB::raw('SUM(
                    CASE
                        WHEN course_id IS NOT NULL THEN
                            COALESCE((SELECT price FROM courses WHERE id = course_id), 0)
                        ELSE 0
                    END
                ) as total_expected_revenue'),
            DB::raw('COUNT(CASE WHEN cancelled = 0 THEN 1 END) as active_bookings'),
            DB::raw('COUNT(CASE WHEN cancelled = 1 THEN 1 END) as cancelled_bookings')
        ])
            ->whereHas('booking', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->where('status', '!=', 'cancelled');
            })
            ->whereBetween('date', [$startDate, $endDate]);

        // Aplicar filtros adicionales
        if ($request->course_type) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('course_type', $request->course_type);
            });
        }

        if ($request->sport_id) {
            $query->whereHas('course.sport', function ($q) use ($request) {
                $q->where('id', $request->sport_id);
            });
        }

        $results = $query->groupBy('period', 'date')
            ->orderBy('date')
            ->get();

        // Procesar resultados para incluir cálculos adicionales
        $processedResults = $results->map(function ($result) {
            $totalReceived = $this->calculateReceivedRevenueForPeriod($result->date);

            return [
                'period' => $result->period,
                'date' => $result->date,
                'total_bookings' => $result->total_bookings,
                'active_bookings' => $result->active_bookings,
                'cancelled_bookings' => $result->cancelled_bookings,
                'expected_revenue' => $result->total_expected_revenue,
                'received_revenue' => $totalReceived,
                'pending_revenue' => $result->total_expected_revenue - $totalReceived,
                'collection_rate' => $result->total_expected_revenue > 0 ?
                    round(($totalReceived / $result->total_expected_revenue) * 100, 2) : 0
            ];
        });

        return [
            'period_type' => $period,
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'data' => $processedResults,
            'summary' => [
                'total_periods' => $processedResults->count(),
                'total_bookings' => $processedResults->sum('total_bookings'),
                'total_expected' => $processedResults->sum('expected_revenue'),
                'total_received' => $processedResults->sum('received_revenue'),
                'average_collection_rate' => $processedResults->avg('collection_rate')
            ]
        ];
    }

    /**
     * Calcular análisis detallado de cursos
     */
    private function calculateDetailedCourseAnalytics(Request $request): array
    {
        $schoolId = $request->school_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $courses = Course::with(['sport', 'bookingUsers' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate])
                ->whereHas('booking', function ($booking) {
                    $booking->where('status', '!=', 'cancelled');
                });
        }])
            ->where('school_id', $schoolId);

        // Aplicar filtros
        if ($request->course_type) {
            $courses->where('course_type', $request->course_type);
        }

        if ($request->sport_id) {
            $courses->where('sport_id', $request->sport_id);
        }

        $coursesData = $courses->get()->map(function ($course) {
            $bookingUsers = $course->bookingUsers;

            $totalRevenue = 0;
            $totalPaid = 0;
            $paymentMethods = ['cash' => 0, 'card' => 0, 'online' => 0, 'vouchers' => 0];

            foreach ($bookingUsers as $bookingUser) {
                $priceCalc = $this->calculateTotalPrice($bookingUser);
                $totalRevenue += $priceCalc['totalPrice'];

                // Analizar pagos
                $booking = $bookingUser->booking;
                if ($booking) {
                    $payments = $booking->payments()->where('status', 'completed')->get();
                    $totalPaid += $payments->sum('amount');

                    foreach ($payments as $payment) {
                        $method = $payment->payment_method ?? 'cash';
                        if (isset($paymentMethods[$method])) {
                            $paymentMethods[$method] += $payment->amount;
                        }
                    }
                }
            }

            $completionRate = $bookingUsers->count() > 0 ?
                ($bookingUsers->where('cancelled', 0)->count() / $bookingUsers->count()) * 100 : 0;

            return [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_type' => $course->course_type,
                'sport_name' => $course->sport->name ?? 'N/A',
                'base_price' => $course->price,
                'total_bookings' => $bookingUsers->count(),
                'active_bookings' => $bookingUsers->where('cancelled', 0)->count(),
                'cancelled_bookings' => $bookingUsers->where('cancelled', 1)->count(),
                'total_revenue' => $totalRevenue,
                'total_paid' => $totalPaid,
                'pending_amount' => $totalRevenue - $totalPaid,
                'average_price' => $bookingUsers->count() > 0 ? $totalRevenue / $bookingUsers->count() : 0,
                'completion_rate' => $completionRate,
                'collection_efficiency' => $totalRevenue > 0 ? ($totalPaid / $totalRevenue) * 100 : 0,
                'payment_methods' => $paymentMethods,
                'profitability_score' => $this->calculateProfitabilityScore($course, $totalRevenue, $bookingUsers->count())
            ];
        });

        return [
            'courses' => $coursesData,
            'summary' => [
                'total_courses' => $coursesData->count(),
                'total_revenue' => $coursesData->sum('total_revenue'),
                'average_completion_rate' => $coursesData->avg('completion_rate'),
                'best_performing_course' => $coursesData->sortByDesc('total_revenue')->first(),
                'most_popular_course' => $coursesData->sortByDesc('total_bookings')->first()
            ]
        ];
    }

    /**
     * Calcular eficiencia de monitores
     */
    private function calculateMonitorEfficiency(Request $request): array
    {
        $schoolId = $request->school_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $monitors = Monitor::whereHas('monitorsSchools', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
            ->with(['bookingUsers' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('booking', function ($booking) {
                        $booking->where('status', '!=', 'cancelled');
                    });
            }, 'sports'])
            ->get();

        $monitorAnalytics = $monitors->map(function ($monitor) {
            $bookingUsers = $monitor->bookingUsers;

            // Calcular horas por tipo
            $hoursCollective = $bookingUsers->filter(function ($bu) {
                return $bu->course && $bu->course->course_type == 1;
            })->count();

            $hoursPrivate = $bookingUsers->filter(function ($bu) {
                return $bu->course && $bu->course->course_type == 2;
            })->count();

            $hoursActivities = $bookingUsers->filter(function ($bu) {
                return $bu->course && $bu->course->course_type == 3;
            })->count();

            $totalHours = $hoursCollective + $hoursPrivate + $hoursActivities;

            // Calcular ingresos generados
            $totalRevenue = 0;
            foreach ($bookingUsers as $bookingUser) {
                $priceCalc = $this->calculateTotalPrice($bookingUser);
                $totalRevenue += $priceCalc['totalPrice'];
            }

            // Calcular costes del monitor
            $hourlyRate = $monitor->hour_price ?? 25; // Precio por defecto
            $totalCost = $totalHours * $hourlyRate;

            // Calcular eficiencia
            $efficiency = $totalCost > 0 ? min(($totalRevenue / $totalCost) * 100, 100) : 0;

            // Calcular satisfacción (basado en cancelaciones)
            $cancelledBookings = $bookingUsers->where('cancelled', 1)->count();
            $satisfactionRate = $totalHours > 0 ?
                ((($totalHours - $cancelledBookings) / $totalHours) * 100) : 100;

            return [
                'monitor_id' => $monitor->id,
                'monitor_name' => $monitor->name,
                'email' => $monitor->email,
                'sports_assigned' => $monitor->sports->pluck('name')->toArray(),
                'total_hours' => $totalHours,
                'hours_collective' => $hoursCollective,
                'hours_private' => $hoursPrivate,
                'hours_activities' => $hoursActivities,
                'hourly_rate' => $hourlyRate,
                'total_cost' => $totalCost,
                'revenue_generated' => $totalRevenue,
                'profit_margin' => $totalRevenue - $totalCost,
                'efficiency_score' => round($efficiency, 2),
                'satisfaction_rate' => round($satisfactionRate, 2),
                'bookings_per_hour' => $totalHours > 0 ? round($bookingUsers->count() / $totalHours, 2) : 0,
                'average_revenue_per_hour' => $totalHours > 0 ? round($totalRevenue / $totalHours, 2) : 0
            ];
        });

        return [
            'monitors' => $monitorAnalytics,
            'summary' => [
                'total_monitors' => $monitorAnalytics->count(),
                'total_hours_worked' => $monitorAnalytics->sum('total_hours'),
                'total_cost' => $monitorAnalytics->sum('total_cost'),
                'total_revenue_generated' => $monitorAnalytics->sum('revenue_generated'),
                'average_efficiency' => $monitorAnalytics->avg('efficiency_score'),
                'most_efficient_monitor' => $monitorAnalytics->sortByDesc('efficiency_score')->first(),
                'most_productive_monitor' => $monitorAnalytics->sortByDesc('total_hours')->first()
            ]
        ];
    }

    /**
     * Calcular métricas de conversión
     */
    private function calculateConversionMetrics(Request $request): array
    {
        $schoolId = $request->school_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $bookingsQuery = Booking::where('school_id', $schoolId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->source) {
            $bookingsQuery->where('source', $request->source);
        }

        $bookings = $bookingsQuery->with(['bookingUsers', 'payments'])->get();

        // Analizar por fuente
        $sourceAnalysis = $bookings->groupBy('source')->map(function ($sourceBookings, $source) {
            $totalBookings = $sourceBookings->count();
            $completedBookings = $sourceBookings->filter(function ($booking) {
                return $booking->status === 'active' || $booking->status === 'completed';
            })->count();

            $cancelledBookings = $sourceBookings->where('status', 'cancelled')->count();

            return [
                'source' => $source,
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'conversion_rate' => $totalBookings > 0 ?
                    round(($completedBookings / $totalBookings) * 100, 2) : 0,
                'cancellation_rate' => $totalBookings > 0 ?
                    round(($cancelledBookings / $totalBookings) * 100, 2) : 0,
                'average_time_to_completion' => $this->calculateAverageCompletionTime($sourceBookings)
            ];
        });

        return [
            'by_source' => $sourceAnalysis->values(),
            'overall_metrics' => [
                'total_bookings' => $bookings->count(),
                'overall_conversion_rate' => $bookings->count() > 0 ?
                    round((($bookings->where('status', '!=', 'cancelled')->count()) / $bookings->count()) * 100, 2) : 0,
                'best_performing_source' => $sourceAnalysis->sortByDesc('conversion_rate')->first(),
                'worst_performing_source' => $sourceAnalysis->sortBy('conversion_rate')->first()
            ]
        ];
    }

    /**
     * Calcular tendencias y predicciones
     */
    private function calculateTrendsAndPredictions(Request $request): array
    {
        $schoolId = $request->school_id;
        $analysisMonths = $request->analysis_months ?? 12;
        $predictionMonths = $request->prediction_months ?? 3;

        $startDate = Carbon::now()->subMonths($analysisMonths)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Obtener datos históricos mensuales
        $monthlyData = BookingUser::select([
            DB::raw('YEAR(date) as year'),
            DB::raw('MONTH(date) as month'),
            DB::raw('COUNT(*) as total_bookings'),
            DB::raw('SUM(CASE WHEN cancelled = 0 THEN 1 ELSE 0 END) as active_bookings'),
            DB::raw('AVG(CASE WHEN course_id IS NOT NULL THEN (SELECT price FROM courses WHERE id = course_id) ELSE 0 END) as average_price')
        ])
            ->whereHas('booking', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Calcular tendencias
        $trends = $this->calculateLinearTrend($monthlyData);

        // Generar predicciones simples
        $predictions = $this->generateSimplePredictions($monthlyData, $predictionMonths);

        return [
            'historical_data' => $monthlyData,
            'trends' => $trends,
            'predictions' => $predictions,
            'insights' => $this->generateTrendInsights($monthlyData, $trends)
        ];
    }

    /**
     * Calcular métricas en tiempo real
     */
    private function calculateRealtimeMetrics(Request $request): array
    {
        $schoolId = $request->school_id;
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'new_bookings' => $this->getBookingCountForPeriod($schoolId, $today, $today),
                'revenue' => $this->getRevenueForPeriod($schoolId, $today, $today),
                'cancellations' => $this->getCancellationCountForPeriod($schoolId, $today, $today)
            ],
            'this_week' => [
                'new_bookings' => $this->getBookingCountForPeriod($schoolId, $thisWeek, Carbon::now()),
                'revenue' => $this->getRevenueForPeriod($schoolId, $thisWeek, Carbon::now()),
                'cancellations' => $this->getCancellationCountForPeriod($schoolId, $thisWeek, Carbon::now())
            ],
            'this_month' => [
                'new_bookings' => $this->getBookingCountForPeriod($schoolId, $thisMonth, Carbon::now()),
                'revenue' => $this->getRevenueForPeriod($schoolId, $thisMonth, Carbon::now()),
                'cancellations' => $this->getCancellationCountForPeriod($schoolId, $thisMonth, Carbon::now())
            ],
            'last_updated' => Carbon::now()->toISOString()
        ];
    }

    // ==================== MÉTODOS AUXILIARES ====================

    private function calculateReceivedRevenueForPeriod($date): float
    {
        // Implementar cálculo de ingresos recibidos para una fecha específica
        // Esto debería consultar los pagos completados para esa fecha
        return 0.0; // Placeholder
    }

    private function calculateProfitabilityScore($course, $totalRevenue, $totalBookings): float
    {
        // Calcular score de rentabilidad basado en ingresos, popularidad y costes
        $baseScore = 50;

        if ($totalRevenue > 1000) $baseScore += 20;
        if ($totalBookings > 10) $baseScore += 15;
        if ($course->price > 50) $baseScore += 10;

        return min($baseScore, 100);
    }

    private function calculateAverageCompletionTime($bookings): float
    {
        // Calcular tiempo promedio desde creación hasta completación
        return 0.0; // Placeholder
    }

    private function calculateLinearTrend($data): array
    {
        // Implementar cálculo de tendencia lineal simple
        return [
            'bookings_trend' => 'increasing', // or 'decreasing', 'stable'
            'revenue_trend' => 'increasing',
            'growth_rate' => 5.2 // porcentaje
        ];
    }

    private function generateSimplePredictions($historicalData, $months): array
    {
        // Generar predicciones simples basadas en tendencias
        $predictions = [];
        for ($i = 1; $i <= $months; $i++) {
            $futureDate = Carbon::now()->addMonths($i);
            $predictions[] = [
                'month' => $futureDate->format('Y-m'),
                'predicted_bookings' => rand(80, 120), // Placeholder
                'predicted_revenue' => rand(5000, 8000), // Placeholder
                'confidence_level' => 0.75
            ];
        }
        return $predictions;
    }

    private function generateTrendInsights($data, $trends): array
    {
        return [
            'main_insight' => 'Las reservas han aumentado un 15% en los últimos 3 meses',
            'recommendations' => [
                'Aumentar capacidad para cursos populares',
                'Revisar precios de cursos con baja demanda',
                'Optimizar horarios según tendencias de reserva'
            ]
        ];
    }

    private function getBookingCountForPeriod($schoolId, $startDate, $endDate): int
    {
        return Booking::where('school_id', $schoolId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    private function getRevenueForPeriod($schoolId, $startDate, $endDate): float
    {
        // Implementar cálculo de ingresos para el período
        return 0.0; // Placeholder
    }

    private function getCancellationCountForPeriod($schoolId, $startDate, $endDate): int
    {
        return Booking::where('school_id', $schoolId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->count();
    }

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
