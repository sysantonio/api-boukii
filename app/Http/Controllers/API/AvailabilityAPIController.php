<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBookingAPIRequest;
use App\Http\Requests\API\UpdateBookingAPIRequest;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class BookingController
 */

class AvailabilityAPIController extends AppBaseController
{

    public function __construct()
    {

    }

    /**
     * @OA\Post(
     *      path="/availability",
     *      summary="Get Course Availability",
     *      tags={"Availability"},
     *      description="Get availability of courses based on type, dates, sport, client, and degree",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"start_date", "end_date"},
     *              @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *              @OA\Property(property="end_date", type="string", format="date", example="2023-01-31"),
     *              @OA\Property(property="course_type", type="integer", example=1),
     *              @OA\Property(property="sport_id", type="integer", example=1),
     *              @OA\Property(property="client_id", type="integer", example=1),
     *              @OA\Property(property="school_id", type="integer", example=1),
     *              @OA\Property(property="degree_id", type="integer", example=1),
     *              @OA\Property(property="get_lower_degrees", type="boolean", example=false)
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
     *                  @OA\Items(ref="#/components/schemas/Course")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getCourseAvailability(Request $request): JsonResponse
    {
        // Validación de las fechas
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        if (!$startDate || !$endDate || $startDate->gt($endDate)) {
            return $this->sendError('Invalid date range', 422);
        }

        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');

        $type = $request->input('course_type') ?? 1;
        $sportId = $request->input('sport_id');
        $clientId = $request->input('client_id');
        $degreeId = $request->input('degree_id');
        $getLowerDegrees = $request->input('get_lower_degrees');

        // Check if 'school_id' is provided in the request, if not, set it to null.
        $schoolId = $request->input('school_id', null);

        try {
            // Build the query based on the presence of 'school_id'
            $query = Course::with('station', 'sport', 'courseDates.courseGroups.courseSubgroups.monitor',
                'courseExtras', 'courseDates.courseGroups.degree')
                ->withAvailableDates($type, $startDate, $endDate, $sportId, $clientId, $degreeId, $getLowerDegrees)
                ->where('active', 1);

            if ($schoolId !== null) {
                $query->where('school_id', $schoolId);
            }

            $courses = $query->get();

            return $this->sendResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendError('Error retrieving courses', 500);
        }
    }

    public function getAvailableHours(Request $request)
    {
        // 1. Validar y obtener los parámetros desde el Request
        $courseId = $request->input('course_id');
        $date = $request->input('date');
        $utilizers = $request->input('utilizers', []); // Lista de utilizadores, por defecto vacío

        // Buscar el curso en la base de datos
        $course = Course::find($courseId);

        if (!$course) {
            return response()->json(['error' => 'Invalid course ID'], 400);
        }

        // 2. Obtener el rango de horas del curso
        $startHour = Carbon::createFromFormat('H:i', $course->hour_min);
        $endHour = Carbon::createFromFormat('H:i', $course->hour_max);
        $allHours = [];

        // Generar todas las horas en intervalos de 30 minutos (ajustable)
        while ($startHour->lessThan($endHour)) {
            $allHours[] = $startHour->format('H:i');
            $startHour->addMinutes(30);
        }

        // 3. Verificar la disponibilidad de los monitores
        $busyMonitors = $this->getBusyMonitors($date, $course->id);

        $availableMonitors = $this->getMonitorsAvailableForCourse($course, $busyMonitors);

        if ($availableMonitors->isEmpty()) {
            return response()->json(['available_hours' => []], 200); // No hay monitores disponibles
        }

        // 4. Verificar la disponibilidad de los utilizadores
        $busyUtilizers = [];
        foreach ($utilizers as $utilizer) {
            $busyUtilizers = array_merge($busyUtilizers, $this->getBusyUtilizerHours($utilizer, $date));
        }

        // 5. Filtrar horas ocupadas
        $hoursAvailable = array_filter($allHours, function ($hour) use ($busyMonitors, $busyUtilizers) {
            return !in_array($hour, $busyMonitors) && !in_array($hour, $busyUtilizers);
        });

        return response()->json([
            'available_hours' => array_values($hoursAvailable),
        ], 200);
    }


    private function getBusyMonitors($date, $courseId)
    {
        $busyMonitors = BookingUser::where('date', $date)
            ->where('status', 1)
            ->whereHas('monitor', function ($query) use ($courseId) {
                $query->whereHas('courses', function ($subQuery) use ($courseId) {
                    $subQuery->where('course_id', $courseId);
                });
            })->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->pluck('hour_start')
            ->toArray();

        return $busyMonitors;
    }

    private function getMonitorsAvailableForCourse($course, $busyMonitors)
    {
        $eligibleMonitors = MonitorSportsDegree::where('sport_id', $course['sport_id'])
            ->where('school_id', $course['school_id'])
            ->whereNotIn('id', $busyMonitors)
            ->get();

        return $eligibleMonitors;
    }

    private function getBusyUtilizerHours($utilizer, $date)
    {
        $overlappingBookings = BookingUser::where('client_id', $utilizer['client_id'])
            ->where('date', $date)
            ->where('status', 1)
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->get();

        $busyHours = [];
        foreach ($overlappingBookings as $booking) {
            $start = Carbon::createFromFormat('H:i', $booking->hour_start);
            $end = Carbon::createFromFormat('H:i', $booking->hour_end);

            while ($start->lessThan($end)) {
                $busyHours[] = $start->format('H:i');
                $start->addMinutes(30); // Ajustar según el intervalo
            }
        }

        return $busyHours;
    }


    public function findAvailableMonitorsForCourseDate(Request $request): JsonResponse
    {
        $courseDateId = $request->input('course_date_id');

        // Asegúrate de que el courseDateId se haya proporcionado
        if (!$courseDateId) {
            return $this->sendError('Course Date ID is required.', 422);
        }

        // Obtén la información específica de CourseDate
        $courseDate = CourseDate::find($courseDateId);
        if (!$courseDate) {
            return $this->sendError('Course Date not found.');
        }
        //TODO: Filter by school
        $allMonitors = Monitor::all();
        $availableMonitors = collect();

        foreach ($allMonitors as $monitor) {
            if (!$this->isMonitorBusy($monitor, $courseDate)) {
                $availableMonitors->push($monitor);
            }
        }

        return $this->sendResponse($availableMonitors, 'Available monitors retrieved successfully');
    }

    private function isMonitorBusy($monitor, $courseDate)
    {
        // Verificar en BookingUser si el monitor está ocupado
        $isBusyWithBooking = BookingUser::where('monitor_id', $monitor->id)
            ->whereDate('date', $courseDate->date)
            ->whereTime('hour_start', '<=', $courseDate->hour_end)
            ->whereTime('hour_end', '>=', $courseDate->hour_start)
            ->where('status', 1)
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->exists();

        if ($isBusyWithBooking) {
            return true;
        }

        // Verificar en MonitorNwd si el monitor tiene un bloqueo
        $isBusyWithNwd = MonitorNwd::where('monitor_id', $monitor->id)
            ->whereDate('start_date', '<=', $courseDate->date)
            ->whereDate('end_date', '>=', $courseDate->date)
            ->exists();

        return $isBusyWithNwd;
    }


}
