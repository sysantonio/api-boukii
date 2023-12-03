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
     * @OA\Get(
     *      path="/availability",
     *      summary="Get Availability",
     *      tags={"Availability"},
     *      description="Get availability of courses based on type, dates, sport, and client",
     *      @OA\Parameter(
     *          name="start_date",
     *          description="Start date of the period",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          description="End date of the period",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="course_type",
     *          description="Type of the course",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="sport_id",
     *          description="ID of the sport",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="client_id",
     *          description="ID of the client",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
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

        try {
            $courses = Course::withAvailableDates($type, $startDate, $endDate, $sportId, $clientId)
                ->get();

            return $this->sendResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving bookings', 500);
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
