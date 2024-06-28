<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
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

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $totalMonitors = Monitor::whereHas('monitorsSchools', function ($query) use ($request) {
            $query->where('school_id', $request['school_id']);
        })->count();

        $bookingUsersCollective = BookingUser::where('school_id', $schoolId)
            ->where('monitor_id', '!=', null)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)->pluck('monitor_id');

        $nwds = MonitorNwd::where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->where('monitor_id', '!=', null)
            ->where('start_date', '>=', $startDate)
            ->where('start_date', '<=', $endDate)
            ->pluck('monitor_id');

        $activeMonitors = $bookingUsersCollective->merge($nwds)->unique()->count();

        return $this->sendResponse(['total' => $totalMonitors, 'busy' => $activeMonitors],
            'Active monitors of the season retrieved successfully');
    }

    public function getTotalWorkedHoursBySport(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $hoursBySport = $this->calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate);

        return $this->sendResponse($hoursBySport, 'Total worked hours by sport retrieved successfully');
    }

    private function calculateTotalWorkedHoursBySport($schoolId, $startDate, $endDate)
    {
        $bookingUsers = BookingUser::with('course.sport')
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

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

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $totalWorkedHours = $this->calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season);

        return $this->sendResponse($totalWorkedHours, 'Total worked hours retrieved successfully');
    }

    private function calculateTotalWorkedHours($schoolId, $startDate, $endDate, $season)
    {
        $bookingUsers = BookingUser::with('monitor')
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $nwds = MonitorNwd::with('monitor')
            ->where('school_id', $schoolId)
            ->where('user_nwd_subtype_id', 2)
            ->whereNotNull('monitor_id')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $courses = Course::whereHas('courseDates', function ($query) use($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        })
            ->where('school_id', $schoolId)
            ->get();

        $totalBookingHours = 0;
        $totalCourseHours = 0;
        $totalNwdHours = 0;
        $monitorsBySportAndDegree = $this->getGroupedMonitors($schoolId);

        foreach ($courses as $course) {
            $durations = $this->getCourseAvailability($course, $monitorsBySportAndDegree, $startDate, $endDate);
            $totalCourseHours += $durations['total_hours'];
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

        // Calcular el número de monitores disponibles
        $totalMonitors = Monitor::whereHas('monitorsSchools', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->count();

        // Calcular la duración diaria en horas
        $dailyDurationHours = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));

        // Multiplicar por el número de días y el número de monitores
        $totalMonitorHours = $numDays * $dailyDurationHours * $totalMonitors;

        return [
            'totalBookingHours' => $totalBookingHours,
            'totalNwdHours' => $totalNwdHours,
            'totalCourseHours' => $totalCourseHours,
            'totalMonitorHours' => $totalMonitorHours,
            'totalWorkedHours' => $totalBookingHours + $totalNwdHours + $totalCourseHours + $totalMonitorHours
        ];
    }



    /**
     * @OA\Get(
     *      path="/admin/statistics/bookings/collective",
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
    public function getCollectiveBookings(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersCollective = BookingUser::where('school_id', $schoolId)
            ->where('course_group_id', '!=', null)
            ->when($request->has('monitor_id'), function ($query) use($request) {
                return $query->where('monitor_id', $request->monitor_id);
            })
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)->get();

        return $this->sendResponse($bookingUsersCollective, 'Collective booking courses of the season retrieved successfully');
    }

    public function getTotalAvailablePlacesByCourseType(Request $request)
    {
        $schoolId = $this->getSchool($request)->id;
        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

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
            ->get();

        $monitorsGrouped = $this->getGroupedMonitors($schoolId);

        $courseAvailabilityByType = [
            'total_places_type_1' => 0,
            'total_available_places_type_1' => 0,
            'total_hours_type_1' => 0,
            'total_price_type_1' => $totalPricesByType['total_price_type_1'],
            'total_places_type_2' => 0,
            'total_available_places_type_2' => 0,
            'total_hours_type_2' => 0,
            'total_price_type_2' => $totalPricesByType['total_price_type_2'],
            'total_places_type_3' => 0,
            'total_available_places_type_3' => 0,
            'total_hours_type_3' => 0,
            'total_price_type_3' => $totalPricesByType['total_price_type_3'],
        ];

        foreach ($courses as $course) {
            $availability = $this->getCourseAvailability($course, $monitorsGrouped);
            if ($availability) {
                if ($course->course_type == 1) {
                    $courseAvailabilityByType['total_places_type_1'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_1'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_1'] += $availability['total_hours'];
                } elseif ($course->course_type == 2) {
                    $courseAvailabilityByType['total_places_type_2'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_2'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_2'] += $availability['total_hours'];
                } else {
                    $courseAvailabilityByType['total_places_type_3'] += $availability['total_places'];
                    $courseAvailabilityByType['total_available_places_type_3'] += $availability['total_available_places'];
                    $courseAvailabilityByType['total_hours_type_3'] += $availability['total_hours'];
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
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', '!=', null)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $settings = json_decode($this->getSchool($request)->settings);
        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', '!=', null)
            ->where('start_date', '>=', $startDate)
            ->where('start_date', '<=', $endDate)
            ->get();

        $currency = 'CHF'; // Valor por defecto si settings no existe o es null

        // Verificar si settings existe y tiene la propiedad taxes->currency
        if ($settings && isset($settings->taxes->currency)) {
            $currency = $settings->taxes->currency;
        }

        $fullDayDuration = $this->convertDurationToHours($this->calculateDuration($season->hour_start, $season->hour_end));

        // Inicialización de variables para almacenar resultados
        $monitorSummary = [];

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

            // Agregar información al resumen del monitor
            if (!isset($monitorSummary[$monitor->id])) {
                $monitorSummary[$monitor->id] = [
                    'first_name' => $monitor->first_name,
                    'id' => $monitor->id,
                    'sport' => $sport,
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
                ];
            }

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
                if ($degree->school_id == $schoolId) {
                    $salaryLevel = $degree->salary;
                    $sport = $degree->sport;
                    break;
                }
            }

            // Calcular el costo si user_nwd_subtype_id es 2
            $cost = ($nwd->user_nwd_subtype_id == 2 && $salaryLevel) ? ($salaryLevel->pay * $hours) : 0;

            // Agregar información al resumen del monitor
            if (!isset($monitorSummary[$monitor->id])) {
                $monitorSummary[$monitor->id] = [
                    'first_name' => $monitor->first_name,
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
                ];
            }

            // Actualizar las horas y costos solo si user_nwd_subtype_id es 2
            if ($nwd->user_nwd_subtype_id == 2) {
                $monitorSummary[$monitor->id]['hours_nwd_payed'] += $hours;
                $monitorSummary[$monitor->id]['cost_nwd'] += $cost;
            } else {
                $monitorSummary[$monitor->id]['hours_nwd'] += $hours;
            }

            // Actualizar las horas totales y el costo total
            $monitorSummary[$monitor->id]['total_hours'] += $hours;
            $monitorSummary[$monitor->id]['total_cost'] += $cost;
        }

        $monitorSummaryJson = array_values($monitorSummary);
        return $this->sendResponse($monitorSummaryJson, 'Monitor bookings of the season retrieved successfully');
    }

    public function getMonitorDailyBookings(Request $request, $monitorId): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersWithMonitor = BookingUser::with(['monitor.monitorSportsDegrees.salary', 'course.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $settings = json_decode($this->getSchool($request)->settings);
        $nwds = MonitorNwd::with(['monitor.monitorSportsDegrees.salary', 'monitor.monitorSportsDegrees.sport'])
            ->where('school_id', $schoolId)
            ->where('monitor_id', $monitorId)
            ->where('user_nwd_subtype_id', 2)
            ->where('start_date', '>=', $startDate)
            ->where('start_date', '<=', $endDate)
            ->get();

        $currency = 'CHF'; // Valor por defecto si settings no existe o es null

        // Verificar si settings existe y tiene la propiedad taxes->currency
        if ($settings && isset($settings->taxes->currency)) {
            $currency = $settings->taxes->currency;
        }

        $fullDayDuration = $this->convertDurationToHours($this->calculateDuration($season->hour_start,  $season->hour_end));

        // Inicialización de variables para almacenar resultados
        $monitorDailySummary = [];

        // Recorrer cada reserva de usuario con monitor
        foreach ($bookingUsersWithMonitor as $bookingUser) {
            $monitor = $bookingUser->monitor;
            $sport = $bookingUser->course->sport;
            $courseType = $bookingUser->course->course_type;
            $salaryLevel = null;
            $duration = $bookingUser->duration;
            $date = Carbon::parse($bookingUser->date)->format('Y-m-d');

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
            //dd($date);
            // Agregar información al resumen del monitor por día
            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = [
                    'date' => $date,
                    'first_name' => $monitor->first_name,
                    'id' => $monitor->id,
                    'sport' => $sport,
                    'currency' => $currency,
                    'hours_collective' => 0,
                    'hours_nwd' => 0,
                    'hours_private' => 0,
                    'hours_activities' => 0,
                    'cost_collective' => 0,
                    'cost_nwd' => 0,
                    'cost_private' => 0,
                    'cost_activities' => 0,
                    'total_hours' => 0,
                    'total_cost' => 0,
                ];
            }

            // Actualizar horas y costos según el tipo de curso
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

            // Actualizar las horas totales y el costo total
            $monitorDailySummary[$date]['total_hours'] += $hours;
            $monitorDailySummary[$date]['total_cost'] += $cost;
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
                if ($degree->school_id == $schoolId) {
                    $salaryLevel = $degree->salary;
                    $sport = $degree->sport;
                    break;
                }
            }

            // Calcular el costo por tipo de curso
            $cost = $salaryLevel ? ($salaryLevel->pay * $hours) : 0;

            // Agregar información al resumen del monitor por día
            if (!isset($monitorDailySummary[$date])) {
                $monitorDailySummary[$date] = [
                    'date' => $date,
                    'first_name' => $monitor->first_name,
                    'id' => $monitor->id,
                    'sport' => $sport,
                    'currency' => $currency,
                    'hours_collective' => 0,
                    'hours_nwd' => 0,
                    'hours_private' => 0,
                    'hours_activities' => 0,
                    'cost_collective' => 0,
                    'cost_nwd' => 0,
                    'cost_private' => 0,
                    'cost_activities' => 0,
                    'total_hours' => 0,
                    'total_cost' => 0,
                ];
            }

            $monitorDailySummary[$date]['hours_nwd'] += $hours;
            $monitorDailySummary[$date]['cost_nwd'] += $cost;

            // Actualizar las horas totales y el costo total
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
