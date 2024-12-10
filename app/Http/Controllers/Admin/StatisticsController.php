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

        $today = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        if (!$season) {
            return response()->json(['error' => 'No se encontró una temporada activa'], 404);
        }

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
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
            ->get();

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

        $currency = 'CHF'; // Valor por defecto si settings no existe o es null

        // Verificar si settings existe y tiene la propiedad taxes->currency
        if ($settings && isset($settings->taxes->currency)) {
            $currency = $settings->taxes->currency;
        }

        $fullDayDuration = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));

        // Obtener todos los monitores de la escuela


        $totalMonitorsQuery = Monitor::whereHas('monitorsSchools', function ($query) use ($request, $schoolId) {
            $query->where('school_id', $schoolId)->where('active_school', 1);
        })->when($request->has('monitor_id'), function ($query) use ($request) {
            return $query->where('id', $request->monitor_id);
        });

        if ($request->has('sport_id')) {
            $totalMonitorsQuery->whereHas('monitorSportsDegrees.monitorSportAuthorizedDegrees.degree', function ($query) use ($request) {
                $query->where('sport_id', $request->sport_id);
            });
        }

        $allMonitors = $totalMonitorsQuery->get();

        // Inicialización de variables para almacenar resultados
        $monitorSummary = [];

        // Recorrer cada monitor para inicializar los valores a 0
        foreach ($allMonitors as $monitor) {
            $monitorSummary[$monitor->id] = [
                'first_name' => $monitor->first_name,
                'language1_id' => $monitor->language1_id,
                'country' => $monitor->country,
                'birth_date' => $monitor->birth_date,
                'image' => $monitor->image,
                'id' => $monitor->id,
                'sport' => null,
                'currency' => $currency,
                'hours_collective' => 0,
                'hours_nwd' => 0,
                'hours_nwd_payed' => 0,
                'hours_private' => 0,
                'hours_activities' => 0,
                'cost_nwd' => 0,
                'cost_collective' => 0,
                'cost_private' => 0,
                'cost_activities' => 0,
                'total_hours' => 0,
                'total_cost' => 0,
                'hour_price' => 0,
            ];
            $sport = null;
            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->school_id == $schoolId && (!$request->has('sport_id') || $degree->sport_id == $request->sport_id)) {
                    $salaryLevel = $degree->salary;
                    $sport = $degree->sport;
                    break;
                }
            }
            $monitorSummary[$monitor->id]['sport'] = $sport;
        }

        // Recorrer cada reserva de usuario con monitor
        foreach ($bookingUsersWithMonitor as $bookingUser) {
            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;
            $salaryLevel = null;
            $duration = $bookingUser->duration;

            // Convertir la duración en horas decimales
            $hours = $this->convertDurationToHours($duration);

            // Buscar el salario y las horas según el tipo de curso
            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->sport_id === $sport->id && $degree->school_id == $schoolId) {
                    $salaryLevel = $degree->salary;
                    break;
                }
            }

            // Calcular el costo por tipo de curso
            $cost = $salaryLevel ? ($salaryLevel->pay * $hours) : 0;

            // Actualizar horas y costos según el tipo de curso
            if ($courseType == 1) {
                $monitorSummary[$monitor->id]['hours_collective'] += $hours;
                $monitorSummary[$monitor->id]['cost_collective'] += $cost;
            } elseif ($courseType == 2) {
                $monitorSummary[$monitor->id]['hours_private'] += $hours;
                $monitorSummary[$monitor->id]['cost_private'] += $cost;
            } else {
                $monitorSummary[$monitor->id]['hours_activities'] += $hours;
                $monitorSummary[$monitor->id]['cost_activities'] += $cost;
            }

            // Actualizar las horas totales y el costo total
            $monitorSummary[$monitor->id]['total_hours'] += $hours;
            $monitorSummary[$monitor->id]['total_cost'] += $cost;
            $monitorSummary[$monitor->id]['hour_price'] = $salaryLevel ? $salaryLevel->pay : 0;
        }

        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $salaryLevel = null;
            $duration = $fullDayDuration;
            if (!$nwd->full_day) {
                $duration = $this->calculateDuration($nwd->start_time, $nwd->end_time);
                // Convertir la duración en horas decimales
                $hours = $this->convertDurationToHours($duration);
            } else {
                $hours = $duration;
            }

            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->school_id == $schoolId && (!$request->has('sport_id') || $degree->sport_id == $request->sport_id)) {
                    $salaryLevel = $degree->salary;
                    $sport = $degree->sport;
                    break;
                }
            }

            // Calcular el costo si user_nwd_subtype_id es 2
            $cost = ($nwd->user_nwd_subtype_id == 2 && $salaryLevel) ? ($salaryLevel->pay * $hours) : 0;

            if ($nwd->user_nwd_subtype_id == 2) {
                // Inicializar claves si no existen
                if (!isset($monitorSummary[$monitor->id])) {
                    $monitorSummary[$monitor->id] = [
                        'first_name' => $monitor->first_name,
                        'language1_id' => $monitor->language1_id,
                        'country' => $monitor->country,
                        'birth_date' => $monitor->birth_date,
                        'image' => $monitor->image,
                        'id' => $monitor->id,
                        'sport' => null,
                        'currency' => $currency,
                        'hours_collective' => 0,
                        'hours_nwd' => 0,
                        'hours_nwd_payed' => 0,
                        'hours_private' => 0,
                        'hours_activities' => 0,
                        'cost_nwd' => 0,
                        'cost_collective' => 0,
                        'cost_private' => 0,
                        'cost_activities' => 0,
                        'total_hours' => 0,
                        'total_cost' => 0,
                        'hour_price' => 0,
                    ];
                }
                $monitorSummary[$monitor->id]['hours_nwd_payed'] += $hours;
                $monitorSummary[$monitor->id]['cost_nwd'] += $cost;
            } else {
                // Inicializar claves si no existen
                if (!isset($monitorSummary[$monitor->id])) {
                    $monitorSummary[$monitor->id] = [
                        'first_name' => $monitor->first_name,
                        'language1_id' => $monitor->language1_id,
                        'country' => $monitor->country,
                        'birth_date' => $monitor->birth_date,
                        'image' => $monitor->image,
                        'id' => $monitor->id,
                        'sport' => null,
                        'currency' => $currency,
                        'hours_collective' => 0,
                        'hours_nwd' => 0,
                        'hours_nwd_payed' => 0,
                        'hours_private' => 0,
                        'hours_activities' => 0,
                        'cost_nwd' => 0,
                        'cost_collective' => 0,
                        'cost_private' => 0,
                        'cost_activities' => 0,
                        'total_hours' => 0,
                        'total_cost' => 0,
                        'hour_price' => 0,
                    ];
                }
                $monitorSummary[$monitor->id]['hours_nwd'] += $hours;
            }

// Actualizar las horas totales y el costo total
// Inicializar claves si no existen
            if (!isset($monitorSummary[$monitor->id])) {
                $monitorSummary[$monitor->id] = [
                    'first_name' => $monitor->first_name,
                    'language1_id' => $monitor->language1_id,
                    'country' => $monitor->country,
                    'birth_date' => $monitor->birth_date,
                    'image' => $monitor->image,
                    'id' => $monitor->id,
                    'sport' => null,
                    'currency' => $currency,
                    'hours_collective' => 0,
                    'hours_nwd' => 0,
                    'hours_nwd_payed' => 0,
                    'hours_private' => 0,
                    'hours_activities' => 0,
                    'cost_nwd' => 0,
                    'cost_collective' => 0,
                    'cost_private' => 0,
                    'cost_activities' => 0,
                    'total_hours' => 0,
                    'total_cost' => 0,
                    'hour_price' => 0,
                ];
            }
            $monitorSummary[$monitor->id]['total_hours'] += $hours;
            $monitorSummary[$monitor->id]['total_cost'] += $cost;
            $monitorSummary[$monitor->id]['hour_price'] = $salaryLevel ? $salaryLevel->pay : 0;
        }

        $monitorSummaryJson = array_values($monitorSummary);
        return $this->sendResponse($monitorSummaryJson, 'Monitor bookings of the season retrieved successfully');
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
        $sportId = $request->sport_id; // Obtener el sport_id de la request

        // Obtener reservas de usuario con monitor filtradas por sport_id
        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->whereHas('course.sport', function($query) use ($sportId) {
                if ($sportId) {
                    $query->where('id', $sportId);
                }
            })
            ->get();

        $settings = json_decode($this->getSchool($request)->settings);
        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->where('user_nwd_subtype_id', 2)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereHas('monitor.monitorSportsDegrees.sport', function($query) use ($sportId) {
                if ($sportId) {
                    $query->where('id', $sportId);
                }
            })
            ->get();

        $currency = 'CHF';
        if ($settings && isset($settings->taxes->currency)) {
            $currency = $settings->taxes->currency;
        }

        $fullDayDuration = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));
        $monitorDailySummary = [];

        foreach ($bookingUsersWithMonitor as $bookingUser) {
            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;
            $salaryLevel = null;
            $duration = $bookingUser->duration;
            $date = Carbon::parse($bookingUser->date)->format('Y-m-d');
            $hours = $this->convertDurationToHours($duration);

            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->sport_id === $sport->id && $degree->school_id == $schoolId) {
                    $salaryLevel = $degree->salary;
                    break;
                }
            }

            $cost = $salaryLevel ? ($salaryLevel->pay * $hours) : 0;
            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = [
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
                    'total_hours' => 0,
                    'total_cost' => 0,
                    'hour_price' => $salaryLevel ? $salaryLevel->pay : 0,
                ];
            }

            if ($courseType == 1) {
                $monitorDailySummary[$date]['hours_collective'] += $hours;
                $monitorDailySummary[$date]['cost_collective'] += $cost;
            } elseif ($courseType == 2) {
                $monitorDailySummary[$date]['hours_private'] += $hours;
                $monitorDailySummary[$date]['cost_private'] += $cost;
            } else {
                $monitorDailySummary[$date]['hours_activities'] += $hours;
                $monitorDailySummary[$date]['cost_activities'] += $cost;
            }

            $monitorDailySummary[$date]['total_hours'] += $hours;
            $monitorDailySummary[$date]['total_cost'] += $cost;
        }

        foreach ($nwds as $nwd) {
            $monitor = $nwd->monitor;
            $salaryLevel = null;
            $duration = $fullDayDuration;
            $date = Carbon::parse($nwd->start_date)->format('Y-m-d');
            if (!$nwd->full_day) {
                $duration = $this->calculateDuration($nwd->start_time, $nwd->end_time);
                $hours = $this->convertDurationToHours($duration);
            } else {
                $hours = $duration;
            }

            foreach ($monitor->monitorSportsDegrees as $degree) {
                if ($degree->school_id == $schoolId) {
                    $salaryLevel = $degree->salary;
                    $sport = $degree->sport;
                    break;
                }
            }

            $cost = $salaryLevel ? ($salaryLevel->pay * $hours) : 0;
            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = [
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
                    'total_hours' => 0,
                    'total_cost' => 0,
                    'hour_price' => $salaryLevel ? $salaryLevel->pay : 0,
                ];
            }

            if ($nwd->user_nwd_subtype_id == 2) {
                $monitorDailySummary[$date]['hours_nwd_payed'] += $hours;
                $monitorDailySummary[$date]['cost_nwd'] += $cost;
            } else {
                $monitorDailySummary[$date]['hours_nwd'] += $hours;
            }
            $monitorDailySummary[$date]['total_hours'] += $hours;
            $monitorDailySummary[$date]['total_cost'] += $cost;
        }

        $monitorDailySummaryJson = array_values($monitorDailySummary);
        return $this->sendResponse($monitorDailySummaryJson, 'Monitor daily bookings retrieved successfully');
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
