<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Course;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\Season;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Response;
use Validator;
use App\Traits\Utils;

/**
 * Class StatisticsController
 * @package App\Http\Controllers\Admin
 */

class StatisticsController extends AppBaseController
{
    use Utils;
    public function __construct()
    {

    }

    /**
     * @OA\Get(
     *      path="/admin/statistics/monitors/active",
     *      summary="Get collective bookings for season",
     *      tags={"Admin"},
     *      description="Get collective bookings for the specified season or date range",
     *      @OA\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="Start date to filter bookings",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="End date to filter bookings",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Booking"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getActiveMonitors(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        // Obtener los monitores totales filtrados por escuela y deporte si se proporciona
        $totalMonitorsQuery = Monitor::whereHas('monitorsSchools', function ($query) use ($request, $schoolId) {
            $query->where('school_id', $schoolId)->where('active_school', 1);
        });

        if ($request->has('sport_id')) {
            $totalMonitorsQuery->whereHas('monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                $query->where('sport_id', $request->sport_id);
            });
        }

        $totalMonitors = $totalMonitorsQuery->pluck('id'); // Obtener solo los IDs de los monitores

        // Obtener los monitores ocupados por las reservas
        $bookingUsersCollective = BookingUser::where('school_id', $schoolId)
            ->where('status', 1)
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->pluck('monitor_id');

        // Obtener los monitores no disponibles y filtrarlos por los IDs de los monitores totales
        $nwds = MonitorNwd::where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereIn('monitor_id', $totalMonitors)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->pluck('monitor_id');

        $activeMonitors = $bookingUsersCollective->merge($nwds)->unique()->count();

        return $this->sendResponse(['total' => $totalMonitors->count(), 'busy' => $activeMonitors],
            'Active monitors of the season retrieved successfully');
    }

    private function calculateGroupedBookingUsersPrice($bookingGroupedUsers): array
    {
        $course = optional($bookingGroupedUsers->first())->course;
        if (!$course) {
            return [
                'basePrice' => 0,
                'extrasPrice' => 0,
                'insurancePrice' => 0,
                'totalPrice' => 0,
            ];
        }

        $basePrice = 0;
        $extrasPrice = 0;
        $insurancePrice = 0;
        $totalPrice = 0;

        if ($course->course_type === 2) {
            // PRIVADOS — agrupar por clase
            $grouped = $bookingGroupedUsers->groupBy(function ($bookingUser) {
                return $bookingUser->date . '|' . $bookingUser->hour_start . '|' . $bookingUser->hour_end . '|' .
                    $bookingUser->monitor_id . '|' . $bookingUser->group_id . '|' . $bookingUser->booking_id;
            });

            foreach ($grouped as $group) {
                $res = $this->calculateTotalPrice($group->first());
                $basePrice += $res['basePrice'];
                $extrasPrice += $res['extrasPrice'];
                $insurancePrice += $res['cancellationInsurancePrice'];
                $totalPrice += $res['totalPrice'];
            }

        } else {
            // COLECTIVOS — agrupar por cliente y calcular por cada uno
            $clientGroups = $bookingGroupedUsers->groupBy('client_id');

            foreach ($clientGroups as $clientBookingUsers) {
                $res = $this->calculateTotalPrice($clientBookingUsers->first(), $clientBookingUsers);
                $basePrice += $res['basePrice'];
                $extrasPrice += $res['extrasPrice'];
                $insurancePrice += $res['cancellationInsurancePrice'];
                $totalPrice += $res['totalPrice'];
            }
        }

        return compact('basePrice', 'extrasPrice', 'insurancePrice', 'totalPrice');
    }


    private function assignVoucherAmount($booking, $bookingGroupedUsers, &$courseSummary, $groupPrice)
    {
        $vouchersLogs = $booking->vouchersLogs ?? collect(); // Asegúrate de eager load

        $totalVoucherAmount = $vouchersLogs->sum('amount');

        if ($totalVoucherAmount <= 0) return;

        // Prorratear según precio del grupo respecto al total de booking
        $totalCalculated = $booking->bookingUsers->sum(fn ($bu) => $this->calculateTotalPrice($bu)['totalPrice']);
        $proportion = $totalCalculated > 0 ? ($groupPrice / $totalCalculated) : 0;

        $voucherAmount = round($totalVoucherAmount * $proportion, 2);

        $courseSummary['vouchers'] += $voucherAmount;
    }

    private function getPaymentMethods($booking, $bookingGroupedUsers, &$courseSummary, $groupPrice)
    {
        if ($booking->payments->isEmpty()) {
            $courseSummary['no_paid'] += 1;
            return;
        }

        // Calcular el total de pagos válidos (no refunds)
        $validPayments = $booking->payments->filter(fn ($p) => !in_array($p->status, ['refund', 'partial_refund']) && !str_contains(strtolower($p->notes ?? ''), 'voucher'));
        $totalPaid = $validPayments->sum('amount');

        if ($totalPaid <= 0) {
            $courseSummary['no_paid'] += 1;
            return;
        }

        // Repartimos el total calculado de este grupo en proporción a los pagos reales
        foreach ($validPayments as $payment) {
            $note = strtolower($payment->notes ?? '');
            $hasPayrexx = !empty($payment->payrexx_reference);

            $proportion = $payment->amount / $totalPaid;
            $amount = round($groupPrice * $proportion, 2); // Este es el valor exacto que sumaremos

            if ($note === 'cash' || str_contains($note, 'efectivo')) {
                $courseSummary['cash'] += $amount;
            } elseif ($hasPayrexx) {
                if ($booking->payment_method_id === Booking::ID_BOUKIIPAY) {
                    if ($booking->created_from === 'web') {
                        $courseSummary['boukii_web'] += $amount;
                    } else {
                        $courseSummary['boukii'] += $amount;
                    }
                } else {
                    $courseSummary['online'] += $amount;
                }
            } elseif ($note === 'transferencia') {
                $courseSummary['transfer'] += $amount;
            } elseif ($note === 'card' || $note === 'tarjeta') {
                $courseSummary['other'] += $amount;
            } else {
                // Fallback
                switch ($booking->payment_method_id) {
                    case Booking::ID_CASH:
                        $courseSummary['cash'] += $amount;
                        break;
                    case Booking::ID_BOUKIIPAY:
                        $courseSummary['boukii'] += $amount;
                        break;
                    case Booking::ID_ONLINE:
                        $courseSummary['online'] += $amount;
                        break;
                    case Booking::ID_OTHER:
                        $courseSummary['other'] += $amount;
                        break;
                    default:
                        $courseSummary['other'] += $amount;
                }
            }
        }
    }


    public function getCoursesWithDetails(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $today = Carbon::now()->format('Y-m-d');

        $season = Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $sportId = $request->input('sport_id');
        $courseType = $request->input('course_type');
        $onlyWeekends = $request->boolean('onlyWeekends', false);

        $bookingusersReserved = BookingUser::whereBetween('date', [$startDate, $endDate])
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2)
                    ->where(function ($q) {
                        $q->whereHas('payments', fn($p) => $p->where('status', 'paid'))
                            ->orWhereHas('vouchersLogs');
                    });
            })
            ->where('status', 1)
            ->where('school_id', $request->school_id)
            ->with('booking.vouchersLogs', 'booking.payments')
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->get();

        $result = [];
        $monitorsGrouped = $this->getGroupedMonitors($schoolId, $request->monitor_id, $request->sport_id);

        foreach ($bookingusersReserved->groupBy('course_id') as $courseId => $bookingCourseUsers) {
            $course = Course::find($courseId);
            if (!$course) continue;

            $payments = [
                'cash' => 0,
                'other' => 0,
                'boukii' => 0,
                'boukii_web' => 0,
                'online' => 0,
                'refunds' => 0,
                'vouchers' => 0,
                'no_paid' => 0,
                'web' => 0,
                'admin' => 0,
            ];

            $courseTotal = 0;
            $extrasByCourse = 0;
            $cancellationInsuranceByCourse = 0;
            $underpaidBookings = [];

            $availability = $this->getCourseAvailability($course, $monitorsGrouped, $startDate, $endDate, $onlyWeekends);

            foreach ($bookingCourseUsers->groupBy('booking_id') as $bookingId => $bookingGroupedUsers) {
                $booking = $bookingGroupedUsers->first()->booking;
                if ($booking->status == 2) continue;

// 1. Calcular pagos reales
                $realPayments = $booking->payments()
                    ->whereNotIn('status', ['refund', 'partial_refund', 'no_refund'])
                    ->sum('amount');

                $refunds = $booking->payments()
                    ->whereIn('status', ['refund', 'partial_refund', 'no_refund'])
                    ->sum('amount');

                $payments['refunds'] += $refunds;

                $voucherLogs = $booking->vouchersLogs ?? collect();

                $totalVoucherAmount = abs($voucherLogs->sum('amount'));

// 2. Calcular precios por bookingUsers agrupados
                $calculated = $this->calculateGroupedBookingUsersPrice($bookingGroupedUsers);


                // Todos los booking_users válidos de esta reserva
                $allValidBookingUsers = $booking->bookingUsers->where('status', 1)->whereBetween('date', [$startDate, $endDate]);

// BookingUsers que estás procesando ahora (solo este curso)
                $currentIds = $bookingGroupedUsers->pluck('id')->toArray();

// BookingUsers que no son de este curso (otros cursos dentro de la misma reserva)
                $otherBookingUsers = $allValidBookingUsers->filter(function ($bu) use ($currentIds) {
                    return !in_array($bu->id, $currentIds);
                });

                $fullBookingTotal = $calculated['totalPrice'];

                if ($otherBookingUsers->count() > 0) {
                    $otherTotal = 0;
                    $otherGrouped = $otherBookingUsers->groupBy('course_id');

                    foreach ($otherGrouped as $group) {
                        $flag = false;
                        if($bookingId == 5053) {
                           $flag = true;
                        }
                        $otherTotal += $this->calculateGroupedBookingUsersPrice($group, $flag)['totalPrice'];

                    }

                    $fullBookingTotal = $calculated['totalPrice'] + $otherTotal;

                }

                // 3. Check discrepancia
                $totalReal = $realPayments + $totalVoucherAmount - $refunds;

                // Ignorar si la diferencia es solo por un reembolso total del mismo valor
                if (round($totalReal, 2) < round($fullBookingTotal, 2)
                    && !(round($realPayments, 2) === round($fullBookingTotal, 2) && round($refunds, 2) === round($realPayments, 2))) {

                    Log::debug('Error en el calculo de pagos', [
                        '❌ Discrepancia en reserva' => $bookingId,
                        'Pagado real' => $realPayments,
                        'VOucher real' => $totalVoucherAmount,
                        'REfund real' => $refunds,
                        'Total real' => $totalReal,
                        'Calculado' => $calculated,
                        'Booking' => $booking->id,
                        'Curso' => $bookingGroupedUsers->first()?->course_id,
                        'Tipo' => $bookingGroupedUsers->first()?->course?->course_type,
                        'Flex' => $bookingGroupedUsers->first()?->course?->is_flexible,
                        'BookingUsers' => $bookingGroupedUsers->pluck('id'),
                        'Fechas' => $bookingGroupedUsers->pluck('date'),
                        'Precios individuales' => $bookingGroupedUsers->mapWithKeys(fn($bu) => [$bu->id => $this->calculateTotalPrice($bu)]),
                    ]);
                }

                // 4. Clasificación de métodos de pago y vouchers
                $this->getPaymentMethods($booking, $bookingGroupedUsers, $payments, $calculated['totalPrice']);
                $this->assignVoucherAmount($booking, $bookingGroupedUsers, $payments, $calculated['totalPrice']);

                // 5. Extras y seguros
                $extrasByCourse += $calculated['extrasPrice'];
                $cancellationInsuranceByCourse += $calculated['insurancePrice'];
                $courseTotal += $calculated['totalPrice'];

                // 6. Underpaid check
                if ($booking->paid) {
                    $amountPaidForCheck = $totalReal;
/*                    if ($booking->vouchersLogs()->exists()) {
                        $amountPaidForCheck += abs($booking->vouchersLogs()->sum('amount'));
                    }*/

                    if ($amountPaidForCheck + 0.5 < $calculated['totalPrice']) {
                        $underpaidBookings[] = [
                            'booking_id' => $booking->id,
                            'client_name' => $booking->client->full_name ?? '',
                            'paxes' => $bookingGroupedUsers->groupBy('client_id')->count(),
                            'should_pay' => $calculated['totalPrice'],
                            'paid' => $amountPaidForCheck,
                            'difference' => round($calculated['totalPrice'] - $amountPaidForCheck, 2),
                        ];
                    }
                }
            }
            // ✅ CONTAR PLAZAS POR SOURCE
            $bookingUsersGrouped = $course->bookingUsersActive->groupBy('client_id');
            foreach ($bookingUsersGrouped as $clientBookingUsers) {
                $booking = $clientBookingUsers->first()->booking;
                $source = $booking->source;
                $bookingUsersCount = $clientBookingUsers->count();
                $bookingUsersCount = !$course->is_flexible ? $bookingUsersCount / max(1, $course->courseDates->count()) : $bookingUsersCount;

                if (isset($payments[$source])) {
                    $payments[$source] += $bookingUsersCount;
                } else {
                    $payments[$source] = $bookingUsersCount;
                }
            }

            // ✅ RESULTADO FINAL
            $settings = json_decode($this->getSchool($request)->settings);
            $currency = $settings->taxes->currency ?? 'CHF';
            $totalCostFromPayments =
                $payments['cash'] +
                $payments['other'] +
                $payments['boukii'] +
                $payments['boukii_web'] +
                $payments['online'] +
                $payments['vouchers'] -
                $payments['refunds'];

            $result[] = [
                'underpaid_bookings' => $underpaidBookings,
                'underpaid_count' => collect($underpaidBookings)->sum('difference'),
                'course_id' => $course->id,
                'icon' => $course->icon,
                'name' => $course->name,
                'total_places' => $course->course_type == 1 ?
                    round($availability['total_places']) : 'NDF',
                'booked_places' => $course->course_type == 1 ?
                    round($availability['total_reservations_places']) : round($payments['web']) + round($payments['admin']),
                'available_places' => $course->course_type == 1 ?
                    round($availability['total_available_places']) : 'NDF',
                'cash' => round($payments['cash'], 2),
                'other' => round($payments['other'], 2),
                'boukii' => round($payments['boukii'], 2),
                'boukii_web' => round($payments['boukii_web'], 2),
                'online' => round($payments['online'], 2),
                'extras' => round($extrasByCourse, 2),
                'insurance' => round($cancellationInsuranceByCourse, 2),
                'refunds' => round($payments['refunds'], 2),
                'vouchers' => round($payments['vouchers'], 2),
                'no_paid' => round($payments['no_paid'], 2),
                'web' => round($payments['web'], 2),
                'admin' => round($payments['admin'],2 ),
                'currency' => $currency,
                'total_cost' => round($totalCostFromPayments, 2),
                'total_cost_expected' => round($courseTotal, 2), // ✅ Ahora debería funcionar para privados
                'difference_vs_expected' => round($courseTotal - $totalCostFromPayments, 2)
            ];
        }

        // ✅ TOTALES
        $totals = [
            'name' => 'TOTAL',
            'cash' => 0,
            'other' => 0,
            'boukii' => 0,
            'boukii_web' => 0,
            'online' => 0,
            'no_paid' => 0,
            'vouchers' => 0,
            'extras' => 0,
            'insurance' => 0,
            'refunds' => 0,
            'underpaid_count' => 0,
            'total_cost' => 0,
            'total_cost_expected' => 0,
            'difference_vs_expected' => 0
        ];


        foreach ($result as $row) {
            foreach ($totals as $key => $val) {
                if ($key === 'name') continue;
                $totals[$key] += round($row[$key] ?? 0, 2);
            }
        }

// 🔒 Aplicar round final por seguridad
        foreach ($totals as $key => $val) {
            if ($key === 'name') continue;
            $totals[$key] = round($val, 2);
        }

        $result[] = $totals;
        return $this->sendResponse($result, 'Total worked hours by sport retrieved successfully');
    }

    public function getCoursesWithDetails2(Request $request)
    {
        $schoolId = $request->user()->schools[0]->id;
        $start = $request->get('start_date') ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $request->get('end_date') ?? Carbon::now()->endOfMonth()->toDateString();

        $bookingUsers = BookingUser::whereHas('courseDate', function ($q) use ($start, $end) {
            $q->whereBetween('date', [$start, $end]);
        })
            ->whereHas('booking', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with(['course', 'booking', 'booking.payments', 'booking.clientMain', 'courseDate'])
            ->get();

        $coursesSummary = [];

        foreach ($bookingUsers as $bu) {
            $course = $bu->course;
            $booking = $bu->booking;

            if (!isset($coursesSummary[$course->id])) {
                $coursesSummary[$course->id] = $this->initCourseSummary($course);
            }

            $summary = &$coursesSummary[$course->id];

            // Calcular precios detallados
            $priceData = $this->calculateTotalPrice($bu);
            $summary['total_cost'] += $priceData['totalPrice'];
            $summary['extras'] += $priceData['extrasPrice'];
            $summary['insurance'] += $priceData['cancellationInsurancePrice'];

            // Origen de la reserva
            if ($booking->created_from === 'admin') $summary['admin']++;
            if ($booking->created_from === 'web') $summary['web']++;

            // Calcular pagos proporcionados
            $totalBuCount = $booking->bookingUsers->count();
            $relevantBuCount = $booking->bookingUsers->where('course_id', $course->id)->count();
            $proportion = $relevantBuCount / $totalBuCount;

            foreach ($booking->payments as $payment) {
                $this->assignToPaymentMethod($summary, $payment, $payment->amount * $proportion);
            }

            // Comprobar pagos insuficientes
            $paid = $booking->payments->sum('amount') * $proportion;
            $shouldPay = $priceData['totalPrice'];
            $difference = round($shouldPay - $paid, 2);

            if ($difference > 0) {
                $summary['underpaid_count'] += $difference;
                $summary['underpaid_bookings'][] = [
                    'booking_id' => $booking->id,
                    'client_name' => $booking->client->full_name ?? '',
                    'paxes' => $booking->bookingUsers->count(),
                    'should_pay' => $shouldPay,
                    'paid' => $paid,
                    'difference' => $difference,
                ];
            }

            $summary['booked_places']++;
        }

        $totals = $this->calculateTotalRow($coursesSummary);
        $coursesSummary[] = $totals;

        return response()->json(array_values($coursesSummary));
    }

    private function initCourseSummary($course)
    {
        return [
            'course_id' => $course->id,
            'name' => $course->name,
            'icon' => $course->icon,
            'currency' => 'CHF',
            'total_places' => $course->capacity ?? 'NDF',
            'booked_places' => 0,
            'available_places' => $course->capacity ?? 'NDF',
            'cash' => 0,
            'card' => 0,
            'transfer' => 0,
            'other' => 0,
            'boukii' => 0,
            'boukii_web' => 0,
            'online' => 0,
            'vouchers' => 0,
            'insurance' => 0,
            'refunds' => 0,
            'extras' => 0,
            'no_paid' => 0,
            'underpaid_count' => 0,
            'underpaid_bookings' => [],
            'admin' => 0,
            'web' => 0,
            'total_cost' => 0,
        ];
    }

    private function assignToPaymentMethod(&$summary, $payment, $amount)
    {
        $method = strtolower($payment->notes ?? 'other');

        if ($payment->status === 'paid') {
            if ($payment->payrexx_reference) {
                if ($payment->booking->payment_method_id == 2) {
                    $summary['boukii'] += $amount;
                } elseif ($payment->booking->payment_method_id == 3) {
                    $summary['boukii_web'] += $amount;
                }
                $summary['online'] += $amount;
            } else {
                $summary[$method] = ($summary[$method] ?? 0) + $amount;
            }
            $summary['total_cost'] += $amount;
        }

        if (in_array($payment->status, ['refund', 'partial_refund'])) {
            $summary['refunds'] += $amount;
            $summary['total_cost'] -= $amount;
        }
    }

    private function calculateTotalRow($coursesSummary)
    {
        $fields = ['cash', 'card', 'transfer', 'other', 'boukii', 'boukii_web', 'online', 'vouchers', 'insurance',
            'refunds', 'extras', 'no_paid', 'underpaid_count', 'total_cost'];

        $total = ['name' => 'TOTAL'];
        foreach ($fields as $field) {
            $total[$field] = array_sum(array_column($coursesSummary, $field));
        }

        return $total;
    }

    private function calculateTotalForCourse($booking, $courseId)
    {
        $result = [
            'total' => 0,
            'extras' => 0,
            'insurance' => 0,
            'base' => 0,
        ];

        $booking->bookingUsers
            ->where('course_id', $courseId)
            ->each(function ($bookingUser) use (&$result) {
                $priceData = $this->calculateTotalPrice($bookingUser);
                $result['total'] += $priceData['totalPrice'];
                $result['extras'] += $priceData['extrasPrice'];
                $result['insurance'] += $priceData['cancellationInsurancePrice'];
                $result['base'] += $priceData['priceWithoutExtras'];
            });

        return $result;
    }

    function calculateTotalPrice($bookingUser)
    {
        $courseType = $bookingUser->course->course_type;
        $isFlexible = $bookingUser->course->is_flexible;
        $totalPrice = 0;


        if ($courseType == 1) { // Colectivo
            if ($isFlexible) {
                // Si es colectivo flexible
                $totalPrice = $this->calculateFlexibleCollectivePrice($bookingUser);
            } else {
                // Si es colectivo fijo
                $totalPrice = $this->calculateFixedCollectivePrice($bookingUser);
            }
        } elseif ($courseType == 2) { // Privado
            if ($isFlexible) {
                // Si es privado flexible, calcular precio por `price_range`
                $totalPrice = $this->calculatePrivatePrice($bookingUser, $bookingUser->course->price_range);
            } else {
                // Si es privado no flexible, usar un precio fijo
                $totalPrice = $bookingUser->course->price; // Asumimos que el curso tiene un campo `fixed_price`
            }
        } else {
            Log::debug("Invalid course type: $courseType");
            return $totalPrice;
        }

        // Calcular extras
        $extrasPrice = $this->calculateExtrasPrice($bookingUser);
        $totalPrice += $extrasPrice;

        // Calcular seguro de cancelación
        $cancellationInsurancesPrice = 0;
        if ($bookingUser->booking->has_cancellation_insurance) {
            $cancellationInsurancesPrice = $totalPrice * 0.10;
            $totalPrice += $cancellationInsurancesPrice;
        }

        return [
            'basePrice' => $totalPrice - $extrasPrice - $cancellationInsurancesPrice,
            'totalPrice' => $totalPrice,
            'extrasPrice' => $extrasPrice,
            'cancellationInsurancePrice' => $cancellationInsurancesPrice,
        ];
    }

    function calculateFixedCollectivePrice($bookingUser)
    {
        $course = $bookingUser->course;

        // Agrupar BookingUsers por participante (course_id, participant_id)
        $participants = BookingUser::select(
            'client_id',
            DB::raw('COUNT(*) as total_bookings'), // Contar cuántos BookingUsers tiene cada participante
            DB::raw('SUM(price) as total_price') // Sumar el precio total por participante
        )
            ->where('course_id', $course->id)
            ->where('client_id', $bookingUser->client_id)
            ->groupBy('client_id')
            ->get();


        // Tomar el precio del curso para cada participante
        return count($participants) ? $course->price : 0;
    }

    function calculateFlexibleCollectivePrice($bookingUser, $bookingGroupedUsers = null)
    {
        $course = $bookingUser->course;

        // Filtrar fechas solo del cliente actual
        $dates = $bookingGroupedUsers
            ?  $bookingGroupedUsers
                ->pluck('date')
                ->unique()
                ->sort()
                ->values()
            : BookingUser::where('course_id', $course->id)
                ->where('status', '!=', 2)
                ->where('client_id', $bookingUser->client_id)
                ->where('booking_id', $bookingUser->booking_id)
                ->pluck('date')
                ->unique()
                ->sort()
                ->values();

        $totalPrice = 0;

        $discounts = is_array($course->discounts) ? $course->discounts : json_decode($course->discounts, true);
        //Log::debug('Dates de la booking cliente: '.$bookingUser->booking_id, [json_encode($dates->all())]);
        foreach ($dates as $index => $date) {
            $price = $course->price;

            if (!empty($discounts)) {
                foreach ($discounts as $discount) {
                    if ($index + 1 == $discount['day']) {
                        $price -= ($price * $discount['reduccion'] / 100);
                        break;
                    }
                }
            }

            $totalPrice += $price ;
        }

        return round($totalPrice, 2);
    }

    function calculatePrivatePrice($bookingUser, $priceRange)
    {
        $course = $bookingUser->course;
        $groupId = $bookingUser->group_id;

        // Agrupar BookingUsers por fecha, hora y monitor
        $groupBookings = BookingUser::where('course_id', $course->id)
            ->where('date', $bookingUser->date)
            ->where('hour_start', $bookingUser->hour_start)
            ->where('hour_end', $bookingUser->hour_end)
            ->where('monitor_id', $bookingUser->monitor_id)
            ->where('group_id', $groupId)
            ->where('booking_id', $bookingUser->booking_id)
            ->where('school_id', $bookingUser->school_id)
            ->where('status', 1)
            ->count();

        $duration = Carbon::parse($bookingUser->hour_end)->diffInMinutes(Carbon::parse($bookingUser->hour_start));
        $interval = $this->getIntervalFromDuration($duration); // Función para mapear duración al intervalo (e.g., "1h 30m").

        // Buscar el precio en el price range
        $priceForInterval = collect($priceRange)->firstWhere('intervalo', $interval);
        $pricePerParticipant = $priceForInterval[$groupBookings] ?? null;

        if (!$pricePerParticipant) {
            Log::debug("Precio no definido curso $course->id para $groupBookings participantes en intervalo $interval");
            return 0;
        }

        // Calcular extras
        $extraPrices = $bookingUser->bookingUserExtras->sum(function ($extra) {
            return $extra->price;
        });

        // Calcular precio total
        $totalPrice = $pricePerParticipant + $extraPrices;

        return $totalPrice;
    }
    function getIntervalFromDuration($duration)
    {
        $mapping = [
            15 => "15m",
            30 => "30m",
            45 => "45m",
            60 => "1h",
            75 => "1h 15m",
            90 => "1h 30m",
            120 => "2h",
            180 => "3h",
            240 => "4h",
        ];

        return $mapping[$duration] ?? null;
    }

    function calculateExtrasPrice($bookingUser)
    {
        $extras = $bookingUser->bookingUserExtras; // Relación con BookingUserExtras

        $totalExtrasPrice = 0;
        foreach ($extras as $extra) {
            //  Log::debug('extra price:'. $extra->courseExtra->price);
            $extraPrice = $extra->courseExtra->price ?? 0;
            $totalExtrasPrice += $extraPrice;
        }

        return $totalExtrasPrice;
    }

    public function getTotalWorkedHoursBySport(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        // Obtener monitor_id si está presente en la request
        $monitorId = $request->monitor_id;
        $sportId = $request->sport_id; // Obtener sport_id si está presente en la request

        $hoursBySport = $this->calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate, $monitorId, $sportId, $onlyWeekends);

        return $this->sendResponse($hoursBySport, 'Total worked hours by sport retrieved successfully');
    }

    private function calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate, $monitorId = null, $sportId = null, $onlyWeekends=false)
    {
        $bookingUsersQuery = BookingUser::with('course.sport')
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->where('status', 1)
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends());

        // Aplicar filtro por monitor_id si está presente
        if ($monitorId) {
            $bookingUsersQuery->where('monitor_id', $monitorId);
        }

        // Aplicar filtro por sport_id si está presente
        if ($sportId) {
            $bookingUsersQuery->whereHas('course', function ($query) use ($sportId) {
                $query->where('sport_id', $sportId);
            });
        }

        $bookingUsers = $bookingUsersQuery->get();

        $hoursBySport = [];

        foreach ($bookingUsers as $bookingUser) {
            if (!$bookingUser->course) {
                \Log::warning("BookingUser sin curso", ['booking_user_id' => $bookingUser->id]);
                continue; // Salta este registro para evitar el error
            }

            $sportId = $bookingUser->course->sport_id;
            $duration = $this->convertDurationToHours($bookingUser->duration);

            if (!isset($hoursBySport[$sportId])) {
                $hoursBySport[$sportId]['hours'] = 0;
                $hoursBySport[$sportId]['sport'] = $bookingUser->course->sport;
            }

            $hoursBySport[$sportId]['hours'] += $duration;
        }

        return $hoursBySport;
    }


    public function getTotalWorkedHours(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        $totalWorkedHours = $this->calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season,
            $request->monitor_id, $request->sport_id, $onlyWeekends);

        return $this->sendResponse($totalWorkedHours, 'Total worked hours retrieved successfully');
    }

    public function getBookingUsersByDateRange(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $monitorId = $request->monitor_id ?? null;

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::parse($season->start_date);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::parse($season->end_date);
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        // Determinar el intervalo de agrupación
        $interval = $this->determineInterval($startDate, $endDate);

        // Generar el rango de fechas completas basado en el intervalo
        $dateRange = $this->generateDateRange($startDate, $endDate, $interval);

        // Obtener y agrupar los datos
        $bookings = BookingUser::with('course')
            ->where('school_id', $schoolId)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->where('monitor_id', $monitorId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->get();

        // Agrupar los datos por el intervalo determinado y luego por el tipo de curso
        $groupedData = $bookings->groupBy(function ($booking) use ($interval) {
            return Carbon::parse($booking->date)->format($interval);
        })->map(function ($group) {
            return $group->groupBy('course.course_type')->map->count();
        });

        // Rellenar el rango de fechas con valores por defecto si no hay datos
        $data = [];
        foreach ($dateRange as $date) {
            $data[$date] = $groupedData->get($date, collect([1 => 0, 2 => 0, 3 => 0]));
        }

        return $this->sendResponse($data, 'Booking users retrieved successfully');
    }

    public function getBookingUsersBySport(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $monitorId = $request->monitor_id ?? null;

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::parse($season->start_date);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::parse($season->end_date);
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        // Determinar el intervalo de agrupación
        $interval = $this->determineInterval($startDate, $endDate);

        // Generar el rango de fechas completas basado en el intervalo
        $dateRange = $this->generateDateRange($startDate, $endDate, $interval);

        // Obtener y agrupar los datos
        $bookings = BookingUser::with('course.sport')
            ->where('school_id', $schoolId)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->where('monitor_id', $monitorId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->get();

        // Agrupar los datos por el intervalo determinado y luego por el deporte
        $groupedData = $bookings->groupBy(function ($booking) use ($interval) {
            return Carbon::parse($booking->date)->format($interval);
        })->map(function ($group) {
            return $group->groupBy('course.sport.name')->map->count();
        });

        // Rellenar el rango de fechas con valores por defecto si no hay datos
        $data = [];
        foreach ($dateRange as $date) {
            $data[$date] = $groupedData->get($date, collect([])); // Suponiendo que los deportes se añaden dinámicamente
        }

        return $this->sendResponse($data, 'Booking users by sport retrieved successfully');
    }


    private function determineInterval(Carbon $startDate, Carbon $endDate)
    {
        $daysDiff = $endDate->diffInDays($startDate);

        if ($daysDiff <= 30) {
            return 'Y-m-d'; // Agrupar por día
        } elseif ($daysDiff <= 180) {
            return 'Y-W'; // Agrupar por semana
        } else {
            return 'Y-m'; // Agrupar por mes
        }
    }

    private function generateDateRange(Carbon $startDate, Carbon $endDate, $interval)
    {
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateRange[] = $currentDate->format($interval);
            switch ($interval) {
                case 'Y-m-d':
                    $currentDate->addDay();
                    break;
                case 'Y-W':
                    $currentDate->addWeek();
                    break;
                case 'Y-m':
                    $currentDate->addMonth();
                    break;
            }
        }

        return $dateRange;
    }


    private function calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season, $monitor, $sportId = null, $onlyWeekends = false)
    {
        $bookingUsers = BookingUser::with('monitor')
            ->where('school_id', $schoolId)
            ->when($monitor, function ($query) use ($monitor) {
                return $query->where('monitor_id', $monitor);
            })
            ->when($sportId, function ($query) use ($sportId) {
                return $query->whereHas('course', function ($query) use ($sportId) {
                    $query->where('sport_id', $sportId);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->get();

        $nwds = MonitorNwd::with('monitor')
            ->where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->when($monitor, function ($query) use ($monitor) {
                return $query->where('monitor_id', $monitor);
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->when($sportId, function ($query) use ($sportId) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($sportId) {
                    $query->where('sport_id', $sportId);
                });
            })
            ->get();

            $courses = Course::whereHas('courseDates', function ($query) use ($startDate, $endDate, $onlyWeekends) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->when($onlyWeekends, fn($q) => $q->onlyWeekends());
            })
            ->where('school_id', $schoolId)
            ->when($sportId, function ($query) use ($sportId) {
                return $query->where('sport_id', $sportId);
            })
            ->get();

        $totalBookingHours = 0;
        $totalCourseHours = 0;
        $totalNwdHours = 0;
        $totalCourseAvailableHours = 0;
        $monitorsBySportAndDegree = $this->getGroupedMonitors($schoolId);

        foreach ($courses as $course) {
            $durations = $this->getCourseAvailability($course, $monitorsBySportAndDegree, $startDate, $endDate);
            $totalCourseHours += $durations['total_hours'];
            $totalCourseAvailableHours += $durations['total_available_hours'];
        }

        foreach ($bookingUsers as $bookingUser) {
            $duration = $this->convertDurationToHours($bookingUser->duration);
            $totalBookingHours += $duration;
        }

        $fullDayDuration = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));

        foreach ($nwds as $nwd) {
            $duration = $nwd->full_day ? $fullDayDuration : $this->convertDurationToHours($this->calculateDuration($nwd->start_time, $nwd->end_time));
            $totalNwdHours += $duration;
        }

        // Calcular el número de días entre startDate y endDate
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $numDays = $interval->days + 1; // Incluir ambos extremos

        // Calcular el número de monitores disponibles, filtrados por deporte si se proporciona
        $totalMonitorsQuery = Monitor::whereHas('monitorsSchools', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        });

        if ($sportId) {
            $totalMonitorsQuery->whereHas('monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($sportId) {
                $query->where('sport_id', $sportId);
            });
        }

        $totalMonitors = $monitor ? 1 : $totalMonitorsQuery->count();



        // Calcular la duración diaria en horas
        $dailyDurationHours = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));

        // Multiplicar por el número de días y el número de monitores
        $totalMonitorHours = $numDays * $dailyDurationHours * $totalMonitors;

        return [
            'totalBookingHours' => $totalBookingHours,
            'totalNwdHours' => $totalNwdHours,
            'totalCourseHours' => $totalCourseHours,
            'totalAvailableHours' => $totalCourseAvailableHours,
            'totalMonitorHours' => $totalMonitorHours,
            'totalWorkedHours' => $totalBookingHours + $totalNwdHours
        ];
    }

    public function getTotalPrice(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $today = Carbon::now()->format('Y-m-d');

        $season = Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false);

        if (!$startDate || !$endDate) {
            return $this->sendError('Start date and end date are required.');
        }

        $bookingUsersReserved = BookingUser::whereBetween('date', [$startDate, $endDate])
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2)
                    ->where(function ($q) {
                        $q->whereHas('payments', fn($p) => $p->where('status', 'paid'))
                            ->orWhereHas('vouchersLogs');
                    });
            })
            ->where('status', 1)
            ->where('school_id', $schoolId)
            ->with('booking.vouchersLogs', 'booking.payments', 'course')
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->when($request->has('course_type'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('course_type', $request->course_type);
                });
            })
            ->get();

        $totalPrice = 0;
        $processedBookings = [];

        foreach ($bookingUsersReserved->groupBy('course_id') as $courseId => $bookingCourseUsers) {
            $course = Course::find($courseId);
            if (!$course) continue;

            foreach ($bookingCourseUsers->groupBy('booking_id') as $bookingId => $bookingGroupedUsers) {
                if (in_array($bookingId, $processedBookings)) continue;
                $processedBookings[] = $bookingId;

                $booking = $bookingGroupedUsers->first()->booking;
                if ($booking->status == 2) continue;

                // Usar exactamente la misma lógica de cálculo
                $calculated = $this->calculateGroupedBookingUsersPrice($bookingGroupedUsers);

                // Manejar multi-curso
                $allValidBookingUsers = $booking->bookingUsers
                    ->where('status', 1)
                    ->whereBetween('date', [$startDate, $endDate]);

                $currentIds = $bookingGroupedUsers->pluck('id')->toArray();
                $otherBookingUsers = $allValidBookingUsers->filter(function ($bu) use ($currentIds) {
                    return !in_array($bu->id, $currentIds);
                });

                $fullBookingTotal = $calculated['totalPrice'];

                if ($otherBookingUsers->count() > 0) {
                    $otherTotal = 0;
                    $otherGrouped = $otherBookingUsers->groupBy('course_id');

                    foreach ($otherGrouped as $group) {
                        $otherTotal += $this->calculateGroupedBookingUsersPrice($group)['totalPrice'];
                    }

                    $fullBookingTotal = $calculated['totalPrice'] + $otherTotal;
                }

                $totalPrice += $fullBookingTotal;
            }
        }

        return $this->sendResponse(round($totalPrice, 2), 'Total price retrieved successfully');
    }


    public function getTotalAvailablePlacesByCourseType(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        if (!$startDate || !$endDate) {
            return $this->sendError('Start date and end date are required.');
        }

        $bookingUsersTotalPrice = BookingUser::with('course.sport') // <--- Agregado aquí
        ->where('school_id', $schoolId)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();


        $totalPricesByType = [
            'total_price_type_1' => 0,
            'total_price_type_2' => 0,
            'total_price_type_3' => 0,
        ];

        foreach ($bookingUsersTotalPrice as $bookingUser) {
            if (!$bookingUser->course) {
                \Log::warning("BookingUser sin curso", ['booking_user_id' => $bookingUser->id]);
                continue; // Salta este registro para evitar el error
            }

            if ($bookingUser->course->course_type == 1) {
                $totalPricesByType['total_price_type_1'] += $bookingUser->price;
            } elseif ($bookingUser->course->course_type == 2) {
                $totalPricesByType['total_price_type_2'] += $bookingUser->price;
            } else {
                $totalPricesByType['total_price_type_3'] += $bookingUser->price;
            }
        }

        // Obtener todos los cursos dentro del rango de fechas
        $courses = Course::whereHas('courseDates', function ($query) use ($startDate, $endDate, $onlyWeekends) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->when($onlyWeekends, fn($q) => $q->onlyWeekends());
        })->where('school_id', $schoolId)
            ->when($request->has('type'), function ($query) use ($request) {
                return $query->where('course_type', $request->type);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->where('sport_id', $request->sport_id);
            })
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                $query->whereHas('bookingUsers', function ($q) use($request) {
                    return $q->where('monitor_id', $request->monitor_id);
                });
            })
            ->get();

        $monitorsGrouped = $this->getGroupedMonitors($schoolId, $request->monitor_id, $request->sport_id);

        $courseAvailabilityByType = [
            'total_places_type_1' => 0,
            'total_available_places_type_1' => 0,
            'total_hours_type_1' => 0,
            'total_available_hours_type_1' => 0,
            'total_reservations_places_type_1' => 0,
            'total_reservations_hours_type_1' => 0,
            'total_price_type_1' => $totalPricesByType['total_price_type_1'],
            'total_places_type_2' => 0,
            'total_available_places_type_2' => 0,
            'total_hours_type_2' => 0,
            'total_available_hours_type_2' => 0,
            'total_reservations_places_type_2' => 0,
            'total_reservations_hours_type_2' => 0,
            'total_price_type_2' => $totalPricesByType['total_price_type_2'],
            'total_places_type_3' => 0,
            'total_available_places_type_3' => 0,
            'total_hours_type_3' => 0,
            'total_available_hours_type_3' => 0,
            'total_reservations_places_type_3' => 0,
            'total_reservations_hours_type_3' => 0,
            'total_price_type_3' => $totalPricesByType['total_price_type_3'],
        ];

        foreach ($courses as $course) {
            $availability = $this->getCourseAvailability($course, $monitorsGrouped, $startDate, $endDate);
            if ($availability) {
                if ($course->course_type == 1) {
                    $courseAvailabilityByType['total_places_type_1'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_1'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_1'] += $availability['total_hours'];
                    $courseAvailabilityByType['total_available_hours_type_1'] += $availability['total_available_hours'];
                    $courseAvailabilityByType['total_reservations_places_type_1'] += $availability['total_reservations_places'];
                    $courseAvailabilityByType['total_reservations_hours_type_1'] += $availability['total_reservations_hours'];
                } elseif ($course->course_type == 2) {
                    $courseAvailabilityByType['total_places_type_2'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_2'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_2'] += $availability['total_hours'];
                    $courseAvailabilityByType['total_available_hours_type_2'] += $availability['total_available_hours'];
                    $courseAvailabilityByType['total_reservations_places_type_2'] += $availability['total_reservations_places'];
                    $courseAvailabilityByType['total_reservations_hours_type_2'] += $availability['total_reservations_hours'];
                } else {
                    $courseAvailabilityByType['total_places_type_3'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_3'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_3'] += $availability['total_hours'];
                    $courseAvailabilityByType['total_available_hours_type_3'] += $availability['total_available_hours'];
                    $courseAvailabilityByType['total_reservations_places_type_3'] += $availability['total_reservations_places'];
                    $courseAvailabilityByType['total_reservations_hours_type_3'] += $availability['total_reservations_hours'];
                }
            }
        }

        // Filtrar la respuesta por tipo de curso si se proporciona
        if ($request->has('type')) {
            $courseType = $request->type;
            return $this->sendResponse([
                'total_places' => round($courseAvailabilityByType['total_places_type_' . $courseType]),
                'total_available_places' =>  round($courseAvailabilityByType['total_available_places_type_' . $courseType]),
                'total_price' =>  round($courseAvailabilityByType['total_price_type_' . $courseType]),
                'total_hours' => round( $courseAvailabilityByType['total_hours_type_' . $courseType]),
                'total_available_hours' =>  round($courseAvailabilityByType['total_available_hours_type_' . $courseType]),
                'total_reservations_places' =>  round($courseAvailabilityByType['total_reservations_places_type_' . $courseType]),
                'total_reservations_hours' =>  round($courseAvailabilityByType['total_reservations_hours_type_' . $courseType]),
            ], 'Total available places and prices for the specified course type retrieved successfully');
        }


        return $this->sendResponse($courseAvailabilityByType, 'Total available places by course type retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/admin/statistics/bookings/monitors",
     *      summary="Get monitors bookings for season",
     *      tags={"Admin"},
     *      description="Get monitors bookings for the specified season or date range",
     *      @OA\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="Start date to filter bookings",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="End date to filter bookings",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Booking"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getMonitorsBookings(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'monitor_id' => 'integer|exists:monitors,id|nullable',
            'sport_id' => 'integer|exists:sports,id|nullable',
        ]);

        $schoolId = $this->getSchool($request)->id;

        $today = Carbon::now()->format('Y-m-d');

        $season = Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if (!$season) {
            return response()->json(['error' => 'No se encontró una temporada activa'], 404);
        }

        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2);
            })
            ->where('status', 1)
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get()->unique(function ($item) {
                return $item->hour_start . $item->hour_end . $item->date . $item->monitor_id;
            });

        // dd($bookingUsersWithMonitor->pluck('duration'));


        $settings = json_decode($this->getSchool($request)->settings);

        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $subgroupsWithoutBookings = CourseSubgroup::with('monitor.monitorSportsDegrees.salary',
            'monitor.monitorSportsDegrees.sport', 'courseDate')
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })->whereHas('courseDate', function($query) use ($startDate, $endDate, $onlyWeekends) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->when($onlyWeekends, fn($q) => $q->onlyWeekends());
            })->whereDoesntHave('bookingUsers')
            ->get();


        $currency = $settings->taxes->currency ?? 'CHF';

        $monitorSummary = $this->initializeMonitorSummary($schoolId, $request);

        foreach ($subgroupsWithoutBookings as $subgroupsWithoutBooking) {
            $monitor = $subgroupsWithoutBooking->monitor;
            $sport = $subgroupsWithoutBooking->course->sport;
            $courseType = $subgroupsWithoutBooking->course->course_type;
            $duration = $this->calculateDuration($subgroupsWithoutBooking->courseDate->hour_start,
                $subgroupsWithoutBooking->courseDate->hour_end);

            $durationInMinutes = $this->parseDurationToMinutes($duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);


            $this->updateMonitorSummary($monitorSummary, $monitor->id, $courseType, $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate);
        }

        // Procesar reservas con monitor
        foreach ($bookingUsersWithMonitor as $bookingUser) {
            if (!$bookingUser->course) {
                \Log::warning("BookingUser sin curso", ['booking_user_id' => $bookingUser->id]);
                continue; // Salta este registro para evitar el error
            }

            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;

            $durationInMinutes = $this->parseDurationToMinutes($bookingUser->duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $this->updateMonitorSummary($monitorSummary, $monitor->id, $courseType, $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate);
        }

        // Procesar NWDs
        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $durationInMinutes = $nwd->full_day
                ? $this->calculateDurationInMinutes($season->hour_start, $season->hour_end)
                : $this->calculateDurationInMinutes($nwd->start_time, $nwd->end_time);

            $hourlyRate = $this->getHourlyRate($monitor, null, $schoolId, $nwd);
            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $this->updateMonitorSummary($monitorSummary, $monitor->id, 'nwd', $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate, $nwd->user_nwd_subtype_id == 2);
        }

        // Convertir las duraciones totales a formato "Xh Ym"
        foreach ($monitorSummary as &$summary) {
            $summary['hours_collective'] = $this->formatMinutesToHourMinute($summary['hours_collective'] ?? 0);
            $summary['hours_private'] = $this->formatMinutesToHourMinute($summary['hours_private'] ?? 0);
            $summary['hours_activities'] = $this->formatMinutesToHourMinute($summary['hours_activities'] ?? 0);
            $summary['hours_nwd'] = $this->formatMinutesToHourMinute($summary['hours_nwd'] ?? 0);
            $summary['hours_nwd_payed'] = $this->formatMinutesToHourMinute($summary['hours_nwd_payed'] ?? 0);
            $summary['total_hours'] = $this->formatMinutesToHourMinute($summary['total_minutes'] ?? 0);
            unset($summary['total_minutes']); // Eliminar minutos brutos si no se necesitan
        }

        return $this->sendResponse(array_values($monitorSummary), 'Monitor bookings of the season retrieved successfully');
    }

    public function getTotalMonitorPrice(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'monitor_id' => 'integer|exists:monitors,id|nullable',
            'sport_id' => 'integer|exists:sports,id|nullable',
        ]);

        $schoolId = $this->getSchool($request)->id;

        $today = Carbon::now()->format('Y-m-d');

        $season = Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if (!$season) {
            return response()->json(['error' => 'No se encontró una temporada activa'], 404);
        }

        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2);
            })
            ->where('status', 1)
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('course', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get()->unique(function ($item) {
                return $item->hour_start . $item->hour_end . $item->date . $item->monitor_id;
            });

        // dd($bookingUsersWithMonitor->pluck('duration'));


        $settings = json_decode($this->getSchool($request)->settings);

        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $subgroupsWithoutBookings = CourseSubgroup::with('monitor.monitorSportsDegrees.salary',
            'monitor.monitorSportsDegrees.sport', 'courseDate')
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })->whereHas('courseDate', function($query) use ($startDate, $endDate, $onlyWeekends) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->when($onlyWeekends, fn($q) => $q->onlyWeekends());
            })->whereDoesntHave('bookingUsers')
            ->get();


        $currency = $settings->taxes->currency ?? 'CHF';

        $monitorSummary = $this->initializeMonitorSummary($schoolId, $request);

        foreach ($subgroupsWithoutBookings as $subgroupsWithoutBooking) {
            $monitor = $subgroupsWithoutBooking->monitor;
            $sport = $subgroupsWithoutBooking->course->sport;
            $courseType = $subgroupsWithoutBooking->course->course_type;
            $duration = $this->calculateDuration($subgroupsWithoutBooking->courseDate->hour_start,
                $subgroupsWithoutBooking->courseDate->hour_end);

            $durationInMinutes = $this->parseDurationToMinutes($duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);


            $this->updateMonitorSummary($monitorSummary, $monitor->id, $courseType, $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate);
        }

        // Procesar reservas con monitor
        foreach ($bookingUsersWithMonitor as $bookingUser) {
            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;

            $durationInMinutes = $this->parseDurationToMinutes($bookingUser->duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $this->updateMonitorSummary($monitorSummary, $monitor->id, $courseType, $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate);
        }

        // Procesar NWDs
        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $durationInMinutes = $nwd->full_day
                ? $this->calculateDurationInMinutes($season->hour_start, $season->hour_end)
                : $this->calculateDurationInMinutes($nwd->start_time, $nwd->end_time);

            $hourlyRate = $this->getHourlyRate($monitor, null, $schoolId, $nwd);
            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $this->updateMonitorSummary($monitorSummary, $monitor->id, 'nwd', $durationInMinutes,
                $formattedData['totalCost'], $hourlyRate, $nwd->user_nwd_subtype_id == 2);
        }
        $totalPrice = 0;
        // Convertir las duraciones totales a formato "Xh Ym"
        foreach ($monitorSummary as &$summary) {
            $summary['hours_collective'] = $this->formatMinutesToHourMinute($summary['hours_collective'] ?? 0);
            $summary['hours_private'] = $this->formatMinutesToHourMinute($summary['hours_private'] ?? 0);
            $summary['hours_activities'] = $this->formatMinutesToHourMinute($summary['hours_activities'] ?? 0);
            $summary['hours_nwd'] = $this->formatMinutesToHourMinute($summary['hours_nwd'] ?? 0);
            $summary['hours_nwd_payed'] = $this->formatMinutesToHourMinute($summary['hours_nwd_payed'] ?? 0);
            $summary['total_hours'] = $this->formatMinutesToHourMinute($summary['total_minutes'] ?? 0);
            $totalPrice +=  $summary['total_cost'];
            unset($summary['total_minutes']); // Eliminar minutos brutos si no se necesitan
        }

        return $this->sendResponse(round($totalPrice, 2), 'Monitor bookings of the season retrieved successfully');

    }


    private function calculateDurationInMinutes($startTime, $endTime)
    {
        // Asegúrate de que ambos tiempos sean válidos
        if (!$startTime || !$endTime) {
            return 0; // Si alguno de los valores no es válido, devuelve 0
        }

        try {
            // Convierte los tiempos a instancias de Carbon
            $start = Carbon::createFromFormat('H:i:s', $startTime);
            $end = Carbon::createFromFormat('H:i:s', $endTime);

            // Si el tiempo de fin es menor que el de inicio, asumimos que pasa al día siguiente
            if ($end->lt($start)) {
                $end->addDay();
            }

            // Calcula la diferencia en minutos
            return $start->diffInMinutes($end);
        } catch (\Exception $e) {
            // Si ocurre un error en el formato, registra el error y devuelve 0
            Log::error('Error calculating duration in minutes: ' . $e->getMessage());
            Log::error('Startime: ' .$startTime);
            Log::error('Endtime: ' .$endTime);
            return 0;
        }
    }


    private function updateMonitorSummary(
        &$monitorSummary,
        $monitorId,
        $courseType,
        $durationInMinutes,
        $totalCost,
        $hourlyRate,
        $isPaid = false
    ) {
        if (!isset($monitorSummary[$monitorId])) {
            return;
        }

        // Redondear el costo total a 2 decimales
        $totalCost = round($totalCost, 2);

        // Actualizar el precio por hora
        $monitorSummary[$monitorId]['hour_price'] = round($hourlyRate, 2);

        // Acumular horas y costos según el tipo de curso o bloque
        switch ($courseType) {
            case 1: // Collective
                $monitorSummary[$monitorId]['hours_collective'] += $durationInMinutes;
                $monitorSummary[$monitorId]['cost_collective'] = round(($monitorSummary[$monitorId]['cost_collective'] ?? 0) + $totalCost, 2);
                break;
            case 2: // Private
                $monitorSummary[$monitorId]['hours_private'] += $durationInMinutes;
                $monitorSummary[$monitorId]['cost_private'] = round(($monitorSummary[$monitorId]['cost_private'] ?? 0) + $totalCost, 2);
                break;
            case 'nwd': // Bloques NWD
                if ($isPaid) {
                    // NWD pagado
                    $monitorSummary[$monitorId]['hours_nwd_payed'] += $durationInMinutes;
                    $monitorSummary[$monitorId]['cost_nwd'] = round(($monitorSummary[$monitorId]['cost_nwd'] ?? 0) + $totalCost, 2);
                    /*   $monitorSummary[$monitorId]['total_minutes'] = ($monitorSummary[$monitorId]['total_minutes'] ?? 0) + $durationInMinutes;
                       $monitorSummary[$monitorId]['total_cost'] = round(($monitorSummary[$monitorId]['total_cost'] ?? 0) + $totalCost, 2);*/
                } /*else {
                    // NWD no pagado (solo acumula horas)
                    $monitorSummary[$monitorId]['hours_nwd'] += $durationInMinutes;
                    $monitorSummary[$monitorId]['total_minutes'] = ($monitorSummary[$monitorId]['total_minutes'] ?? 0) + $durationInMinutes;
                }*/
                break;
            default: // Activities
                $monitorSummary[$monitorId]['hours_activities'] += $durationInMinutes;
                $monitorSummary[$monitorId]['cost_activities'] = round(($monitorSummary[$monitorId]['cost_activities'] ?? 0) + $totalCost, 2);
                break;
        }

        // Actualizar totales generales (solo para Collective, Private y Activities, o NWD pagados)
        if ($courseType != 'nwd' || $isPaid) {
            $monitorSummary[$monitorId]['total_minutes'] = ($monitorSummary[$monitorId]['total_minutes'] ?? 0) + $durationInMinutes;
            $monitorSummary[$monitorId]['total_cost'] = round(($monitorSummary[$monitorId]['total_cost'] ?? 0) + $totalCost, 2);
        }
    }



    private function formatDurationAndCost($durationInMinutes, $hourlyRate)
    {
        $totalCost = round(($durationInMinutes / 60) * $hourlyRate, 2);
        return [
            'formattedDuration' => $this->formatMinutesToHourMinute($durationInMinutes),
            'totalCost' => $totalCost,
        ];
    }

    private function parseDurationToMinutes($duration)
    {
        if (strpos($duration, ':') !== false) {
            [$hours, $minutes] = explode(':', $duration);
            return ((int) $hours * 60) + (int) $minutes;
        }
        return 0;
    }

    private function formatMinutesToHourMinute($minutes)
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        return sprintf('%dh %02dm', $hours, $remainingMinutes);
    }


    private function initializeMonitorSummary($schoolId, $request)
    {
        $totalMonitorsQuery = Monitor::whereHas('monitorsSchools', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId)->where('active_school', 1);
        });

        if ($request->has('sport_id')) {
            $totalMonitorsQuery->whereHas('monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                $query->where('sport_id', $request->sport_id);
            });
        }

        $monitors = $totalMonitorsQuery->get();
        $monitorSummary = [];

        foreach ($monitors as $monitor) {
            $monitorSummary[$monitor->id] = [
                'id' => $monitor->id,
                'first_name' => $monitor->first_name . ' ' . $monitor->last_name,
                'address' => $monitor->address,
                'language1_id' => $monitor->language1_id,
                'country' => $monitor->country,
                'birth_date' => $monitor->birth_date,
                'work_license' => $monitor->work_license,
                'bank_details' => $monitor->bank_details,
                'image' => $monitor->image,
                'sport' => null, // Este se actualiza más adelante
                'currency' => 'CHF', // Moneda por defecto, se puede ajustar según settings
                'hours_collective' => 0,
                'hours_private' => 0,
                'hours_activities' => 0,
                'hours_nwd' => 0,
                'hours_nwd_payed' => 0,
                'cost_collective' => 0,
                'cost_private' => 0,
                'cost_activities' => 0,
                'cost_nwd' => 0,
                'total_hours' => 0,
                'total_cost' => 0,
                'hour_price' => 0, // Precio por hora se actualizará dinámicamente
            ];

            // Asignar el deporte relacionado si corresponde
            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->school_id == $schoolId && (!$request->has('sport_id') || $degree->sport_id == $request->sport_id)) {
                    $monitorSummary[$monitor->id]['sport'] = $degree->sport;
                    break;
                }
            }
        }

        return $monitorSummary;
    }

    //TODO: Monitor new field for nwd
    private function getHourlyRate($monitor, $sportId, $schoolId, $nwd=null)
    {
        if ($nwd) {
            // Buscar el block_price del monitor para esa escuela
            $monitorSchool = $monitor->monitorsSchools
                ->firstWhere('school_id', $schoolId);

            if ($monitorSchool && $monitorSchool->block_price > 0) {
                return $monitorSchool->block_price;
            }

            // Si no hay block_price, usar el precio del bloqueo
            if (isset($nwd->price) && $nwd->price > 0) {
                return $nwd->price;
            }
        }

        // 2. Si no aplica lo anterior, buscar salario por degree
        foreach ($monitor->monitorSportsDegrees as $degree) {
            if ($sportId) {
                if ($degree->sport_id == $sportId && $degree->school_id == $schoolId) {
                    return $degree->salary ? $degree->salary->pay : 0;
                }
            } else {
                if ($degree->school_id == $schoolId) {
                    return $degree->salary ? $degree->salary->pay : 0;
                }
            }
        }

        return 0; // Si no se encuentra nada
    }
    public function getMonitorDailyBookings(Request $request, $monitorId): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;
        $today = Carbon::now()->format('Y-m-d');
        $season = Season::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $onlyWeekends = $request->boolean('onlyWeekends', false); // default false
        $sportId = $request->sport_id;

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2);
            })
            ->where('status', 1)
            ->whereHas('course.sport', function ($query) use ($sportId) {
                if ($sportId) {
                    $query->where('id', $sportId);
                }
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->get()
            ->unique(function ($item) {
                return $item->hour_start . $item->hour_end . $item->date;
            });

        $settings = json_decode($this->getSchool($request)->settings);

        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->where('user_nwd_subtype_id', 2)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($onlyWeekends, fn($q) => $q->onlyWeekends())
            ->whereHas('monitor.monitorSportsDegrees.sport', function ($query) use ($sportId) {
                if ($sportId) {
                    $query->where('id', $sportId);
                }
            })
            ->get();

        $subgroupsWithoutBookings = CourseSubgroup::with('monitor.monitorSportsDegrees.salary',
            'monitor.monitorSportsDegrees.sport', 'courseDate')
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })->when($request->has('sport_id'), function ($query) use ($request) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                    $query->where('sport_id', $request->sport_id);
                });
            })->whereHas('courseDate', function($query) use ($startDate, $endDate, $onlyWeekends) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->when($onlyWeekends, fn($q) => $q->onlyWeekends());
            })->whereDoesntHave('bookingUsers')
            ->get();

        $currency = $settings->taxes->currency ?? 'CHF';

        $monitorDailySummary = [];

        foreach ($subgroupsWithoutBookings as $subgroupsWithoutBooking) {
            $monitor = $subgroupsWithoutBooking->monitor;
            $sport = $subgroupsWithoutBooking->course->sport;
            $courseType = $subgroupsWithoutBooking->course->course_type;
            $duration = $this->calculateDuration($subgroupsWithoutBooking->courseDate->hour_start,
                $subgroupsWithoutBooking->courseDate->hour_end);

            $durationInMinutes = $this->parseDurationToMinutes($duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $date = Carbon::parse($subgroupsWithoutBooking->courseDate->date)->format('Y-m-d');

            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = $this->initializeDailyMonitorSummary
                ($monitor, $sport, $currency, $date);
            }

            $this->updateDailySummary($monitorDailySummary[$date],
                $courseType, $formattedData, $duration, $hourlyRate);

        }

        // Procesar reservas con monitor
        foreach ($bookingUsersWithMonitor as $bookingUser) {
            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;
            $durationInMinutes = $this->parseDurationToMinutes($bookingUser->duration);
            $hourlyRate = $this->getHourlyRate($monitor, $sport->id, $schoolId);

            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $date = Carbon::parse($bookingUser->date)->format('Y-m-d');

            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = $this->initializeDailyMonitorSummary
                ($monitor, $sport, $currency, $date);
            }


            $this->updateDailySummary($monitorDailySummary[$date], $courseType, $formattedData,
                $bookingUser->duration, $hourlyRate);
        }

        // Procesar NWDs
        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $duration = $this->calculateDuration($nwd->start_time, $nwd->end_time);
            $durationInMinutes = $nwd->full_day
                ? $this->calculateDurationInMinutes($season->hour_start, $season->hour_end)
                : $this->calculateDurationInMinutes($nwd->start_time, $nwd->end_time);

            $hourlyRate = $this->getHourlyRate($monitor, null, $schoolId, $nwd);
            $formattedData = $this->formatDurationAndCost($durationInMinutes, $hourlyRate);

            $date = Carbon::parse($nwd->start_date)->format('Y-m-d');

            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = $this->initializeDailyMonitorSummary($monitor, null, $currency, $date);
            }



            $this->updateDailySummary($monitorDailySummary[$date], 'nwd', $formattedData, $duration, $hourlyRate, $nwd->user_nwd_subtype_id == 2);

        }

        foreach ($monitorDailySummary as &$summary) {
            $summary['hours_collective'] = $this->formatMinutesToHourMinute($summary['hours_collective'] ?? 0);
            $summary['total_hours'] = $this->formatMinutesToHourMinute($summary['total_minutes']);
            $summary['hours_private'] = $this->formatMinutesToHourMinute($summary['hours_private'] ?? 0);
            $summary['hours_activities'] = $this->formatMinutesToHourMinute($summary['hours_activities'] ?? 0);
            $summary['hours_nwd'] = $this->formatMinutesToHourMinute($summary['hours_nwd'] ?? 0);
            $summary['hours_nwd_payed'] = $this->formatMinutesToHourMinute($summary['hours_nwd_payed'] ?? 0);
            $summary['total_hours'] = $this->formatMinutesToHourMinute($summary['total_minutes'] ?? 0);
            unset($summary['total_minutes']);
        }

        $monitorDailySummaryJson = array_values($monitorDailySummary);
        return $this->sendResponse($monitorDailySummaryJson, 'Monitor daily bookings retrieved successfully');
    }

    private function updateDailySummary(&$summary, $courseType, $formattedData, $duration, $hourlyRate, $isPaid = false)
    {
        $summary['hour_price'] = $hourlyRate;

        $durationInMinutes = $this->parseDurationToMinutes($duration);
        $totalCost = round($formattedData['totalCost'], 2);

        // dd($durationInMinutes);
        switch ($courseType) {
            case 1: // Collective
                $summary['hours_collective'] += $durationInMinutes;
                $summary['cost_collective'] += $totalCost;
                break;
            case 2: // Private
                $summary['hours_private'] += $durationInMinutes;
                $summary['cost_private'] += $totalCost;
                break;
            case 'nwd': // Bloques NWD
                if ($isPaid) {
                    $summary['hours_nwd_payed'] += $durationInMinutes;
                    $summary['cost_nwd'] += $totalCost;
                }
                break;
            default: // Activities
                $summary['hours_activities'] += $durationInMinutes;
                $summary['cost_activities'] += $totalCost;
                break;
        }

        if ($courseType != 'nwd' || $isPaid) {
            $summary['total_minutes'] += $durationInMinutes;
            $summary['total_cost'] += $totalCost;
        }
    }

    private function initializeDailyMonitorSummary($monitor, $sport, $currency, $date)
    {
        return [
            'date' => $date,
            'first_name' => $monitor->first_name . ' ' . $monitor->last_name,
            'language1_id' => $monitor->language1_id,
            'country' => $monitor->country,
            'birth_date' => $monitor->birth_date,
            'image' => $monitor->image,
            'id' => $monitor->id,
            'sport' => $sport,
            'currency' => $currency,
            'hours_collective' => 0,
            'hours_nwd' => 0,
            'hours_nwd_payed' => 0,
            'hours_private' => 0,
            'hours_activities' => 0,
            'cost_collective' => 0,
            'cost_nwd' => 0,
            'cost_private' => 0,
            'cost_activities' => 0,
            'total_minutes' => 0,
            'total_cost' => 0,
            'hour_price' => 0,
        ];

    }



    // Función para convertir la duración en formato HH:MM:SS a horas decimales
    private function convertDurationToHours($duration): float|int
    {
        $parts = explode(':', $duration);
        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        $seconds = (int) $parts[2];

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }
}
