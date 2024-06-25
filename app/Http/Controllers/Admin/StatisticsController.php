<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\BookingUser;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class HomeController
 * @package App\Http\Controllers\Teach
 */

class StatisticsController extends AppBaseController
{

    public function __construct()
    {

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
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)->get();

        return $this->sendResponse($bookingUsersCollective, 'Collective booking courses of the season retrieved successfully');
    }


    /**
     * @OA\Get(
     *      path="/admin/statistics/bookings/private",
     *      summary="Get private bookings for season",
     *      tags={"Admin"},
     *      description="Get private bookings for the specified season or date range",
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
    public function getPrivateBookings(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersPrivate = BookingUser::where('school_id', $schoolId)
            ->whereHas('course', function ($query) {
                $query->where('course_type', 2);
            })
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        return $this->sendResponse($bookingUsersPrivate, 'Private booking courses of the season retrieved successfully');
    }



    /**
     * @OA\Get(
     *      path="/admin/statistics/bookings/activity",
     *      summary="Get activity bookings for season",
     *      tags={"Admin"},
     *      description="Get activity bookings for the specified season or date range",
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
    public function getActivityBookings(Request $request): JsonResponse
    {
        $schoolId = $this->getSchool($request)->id;

        $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

        $season = Season::whereDate('start_date', '<=', $today) // Fecha de inicio menor o igual a hoy
        ->whereDate('end_date', '>=', $today)   // Fecha de fin mayor o igual a hoy
        ->first();

        // Utiliza start_date y end_date de la request si están presentes, sino usa las fechas de la temporada
        $startDate = $request->start_date ?? $season->start_date;
        $endDate = $request->end_date ?? $season->end_date;

        $bookingUsersActivity = BookingUser::where('school_id', $schoolId)
            ->whereHas('course', function ($query) {
                $query->where('course_type', 3);
            })
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        return $this->sendResponse($bookingUsersActivity, 'Activity booking courses of the season retrieved successfully');
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

        $currency = 'CHF'; // Valor por defecto si settings no existe o es null

        // Verificar si settings existe y tiene la propiedad taxes->currency
        if ($settings && isset($settings->taxes->currency)) {
            $currency = $settings->taxes->currency;
        }

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
                    'hours_private' => 0,
                    'hours_activities' => 0,
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
        $monitorSummaryJson = array_values($monitorSummary);
        return $this->sendResponse($monitorSummaryJson, 'Monitor bookings of the season retrieved successfully');
    }

    // Función para convertir la duración en formato HH:MM:SS a horas decimales
    private function convertDurationToHours($duration)
    {
        $parts = explode(':', $duration);
        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        $seconds = (int) $parts[2];

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }
}
