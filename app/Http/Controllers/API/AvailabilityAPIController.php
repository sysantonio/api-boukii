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


        try {
            $courses = Course::with('station','sport', 'courseDates.courseGroups.courseSubgroups', 'courseExtras')
                ->withAvailableDates($type, $startDate, $endDate, $sportId, $clientId, $degreeId, $getLowerDegrees)
                ->get();

            return $this->sendResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving courses', 500);
        }
    }

    public function findAvailableMonitorsForCourseDate(Request $request): JsonResponse
    {
        $courseDateId = $request->input('course_date_id');

        // Asegúrate de que el courseDateId se haya proporcionado
        if (!$courseDateId) {
            return $this->sendError('Course Date ID is required.', [], 422);
        }

        // Obtén la información específica de CourseDate
        $courseDate = CourseDate::find($courseDateId);
        if (!$courseDate) {
            return $this->sendError('Course Date not found.', [], 404);
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
