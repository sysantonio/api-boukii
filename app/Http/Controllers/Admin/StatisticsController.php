<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Course;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\Season;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->pluck('monitor_id');

        // Obtener los monitores no disponibles y filtrarlos por los IDs de los monitores totales
        $nwds = MonitorNwd::where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->when($request->has('monitor_id'), function ($query) use ($request) {
                return $query->where('monitor_id', $request->monitor_id);
            }, function ($query) {
                return $query->where('monitor_id', '!=', null);
            })
            ->where('start_date', '>=', $startDate)
            ->where('start_date', '<=', $endDate)
            ->whereIn('monitor_id', $totalMonitors) // Filtrar por los IDs de los monitores totales
            ->pluck('monitor_id');

        $activeMonitors = $bookingUsersCollective->merge($nwds)->unique()->count();

        return $this->sendResponse(['total' => $totalMonitors->count(), 'busy' => $activeMonitors],
            'Active monitors of the season retrieved successfully');
    }

    public function getCoursesWithDetails(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        // Obtén los filtros de la request
        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;
        $sportId = $request->input('sport_id');
        $courseType = $request->input('course_type');

        // Construye la consulta base para los cursos
        $coursesQuery = Course::with(['bookingUsers.booking.payments'])
            ->where('school_id', $schoolId) // Filtrar por school_id
            ->whereHas('bookingUsers.booking')
            ->when($startDate, function ($query, $startDate) {
                return $query->whereHas('courseDates', function ($query) use ($startDate) {
                    $query->where('date', '>=', $startDate);
                });
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereHas('courseDates', function ($query) use ($endDate) {
                    $query->where('date', '<=', $endDate);
                });
            })
            ->when($sportId, function ($query, $sportId) {
                return $query->where('sport_id', $sportId);
            })
            ->when($courseType, function ($query, $courseType) {
                return $query->where('course_type', $courseType);
            });

        // Obtén los cursos con los filtros aplicados
        $courses = $coursesQuery->get();

        // Estructura de respuesta
        $result = [];
        $monitorsGrouped = $this->getGroupedMonitors($schoolId, $request->monitor_id, $request->sport_id);
        foreach ($courses as $course) {
            // Calcular la disponibilidad
            $availability = $this->getCourseAvailability($course, $monitorsGrouped, $startDate, $endDate);

            // Agrupar pagos por tipo
            $payments = [
                'cash' => 0,
                'other' => 0,
                'boukii' => 0,
                'online' => 0,
                'voucher_gift' => 0,
                'sell_voucher' => 0,
                'web' => 0,
                'admin' => 0,
            ];

            foreach ($course->bookings as $booking) {
                // Iterar sobre los pagos de la reserva
                foreach ($booking->payments as $payment) {
                    $paymentType = $booking->payment_method_id;
                    $amount = $payment->status === 'paid' ? $payment->amount : ($payment->status === 'refund' ? -$payment->amount : 0);
                    // Sumar o restar según el método de pago
                    switch ($paymentType) {
                        case Booking::ID_CASH:
                            $payments['cash'] += $amount;
                            break;
                        case Booking::ID_BOUKIIPAY:

                            $payments['boukii'] += $amount;
                            break;
                        case Booking::ID_ONLINE:
                            $payments['online'] += $amount;
                            break;
                        case Booking::ID_OTHER:
                            $payments['other'] += $amount;
                            break;
                    }
                }

                // Contabilizar el origen del booking
                if (array_key_exists($booking->source, $payments)) {
                    $payments[$booking->source] += 1; // Incrementar el contador de origen
                }
            }

            $totalCost = array_sum($payments);

            $result[] = [
                'course_id' => $course->id,
                'icon' => $course->icon,
                'name' => $course->name,
                'total_places' => $availability['total_places'],
                'booked_places' => $availability['total_reservations_places'],
                'available_places' => $availability['total_available_places'],
                'cash' => $payments['cash'],
                'other' => $payments['other'],
                'boukii' => $payments['boukii'],
                'online' => $payments['online'],
                'voucher_gift' => $payments['voucher_gift'],
                'sell_voucher' => $payments['sell_voucher'],
                'web' => $payments['web'],
                'admin' => $payments['admin'],
                'total_cost' => $totalCost
            ];
        }

        return $this->sendResponse($result, 'Total worked hours by sport retrieved successfully');
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

        // Obtener monitor_id si está presente en la request
        $monitorId = $request->monitor_id;
        $sportId = $request->sport_id; // Obtener sport_id si está presente en la request

        $hoursBySport = $this->calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate, $monitorId, $sportId);

        return $this->sendResponse($hoursBySport, 'Total worked hours by sport retrieved successfully');
    }

    private function calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate, $monitorId = null, $sportId = null)
    {
        $bookingUsersQuery = BookingUser::with('course.sport')
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->where('status', 1)
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$startDate, $endDate]);

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

        $totalWorkedHours = $this->calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season,
            $request->monitor_id, $request->sport_id);

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


    private function calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season, $monitor, $sportId = null)
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
            ->get();

        $nwds = MonitorNwd::with('monitor')
            ->where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->when($monitor, function ($query) use ($monitor) {
                return $query->where('monitor_id', $monitor);
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($sportId, function ($query) use ($sportId) {
                return $query->whereHas('monitor.monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($sportId) {
                    $query->where('sport_id', $sportId);
                });
            })
            ->get();

        $courses = Course::whereHas('courseDates', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
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

        if (!$startDate || !$endDate) {
            return $this->sendError('Start date and end date are required.');
        }

        $bookingUsersTotalPrice = BookingUser::where('school_id', $schoolId)
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
            if ($bookingUser->course->course_type == 1) {
                $totalPricesByType['total_price_type_1'] += $bookingUser->price;
            } elseif ($bookingUser->course->course_type == 2) {
                $totalPricesByType['total_price_type_2'] += $bookingUser->price;
            } else {
                $totalPricesByType['total_price_type_3'] += $bookingUser->price;
            }
        }

        // Obtener todos los cursos dentro del rango de fechas
        $courses = Course::whereHas('courseDates', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
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
                'total_places' => $courseAvailabilityByType['total_places_type_' . $courseType],
                'total_available_places' => $courseAvailabilityByType['total_available_places_type_' . $courseType],
                'total_price' => $courseAvailabilityByType['total_price_type_' . $courseType],
                'total_hours' => $courseAvailabilityByType['total_hours_type_' . $courseType],
                'total_available_hours' => $courseAvailabilityByType['total_available_hours_type_' . $courseType],
                'total_reservations_places' => $courseAvailabilityByType['total_reservations_places_type_' . $courseType],
                'total_reservations_hours' => $courseAvailabilityByType['total_reservations_hours_type_' . $courseType],
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



        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
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

        $currency = $settings->taxes->currency ?? 'CHF';

        $monitorSummary = $this->initializeMonitorSummary($schoolId, $request);

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

            $hourlyRate = $this->getHourlyRate($monitor, null, $schoolId);
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
                'first_name' => $monitor->first_name,
                'language1_id' => $monitor->language1_id,
                'country' => $monitor->country,
                'birth_date' => $monitor->birth_date,
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


    private function getHourlyRate($monitor, $sportId, $schoolId)
    {
        foreach ($monitor->monitorSportsDegrees as $degree) {
           // Log::debug('$degree: ', $degree->toArray());
            if($sportId) {
                if ($degree->sport_id == $sportId && $degree->school_id == $schoolId) {
                    return $degree->salary ? $degree->salary->pay : 0;
                }
            } else {
                if ($degree->school_id == $schoolId) {
                    return $degree->salary ? $degree->salary->pay : 0;
                }
            }

        }
        return 0; // Devuelve 0 si no se encuentra un salario válido
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
            ->whereHas('monitor.monitorSportsDegrees.sport', function ($query) use ($sportId) {
                if ($sportId) {
                    $query->where('id', $sportId);
                }
            })
            ->get();

        $currency = $settings->taxes->currency ?? 'CHF';

        $monitorDailySummary = [];

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


            $this->updateDailySummary($monitorDailySummary[$date], $courseType, $formattedData, $bookingUser->duration, $hourlyRate);
        }

        // Procesar NWDs
        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $duration = $this->calculateDuration($nwd->start_time, $nwd->end_time);
            $durationInMinutes = $nwd->full_day
                ? $this->calculateDurationInMinutes($season->hour_start, $season->hour_end)
                : $this->calculateDurationInMinutes($nwd->start_time, $nwd->end_time);

            $hourlyRate = $this->getHourlyRate($monitor, null, $schoolId);
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
            'first_name' => $monitor->first_name,
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
