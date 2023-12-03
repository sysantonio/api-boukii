<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class CourseController extends SlugAuthController
{

    /**
     * @OA\Get(
     *      path="/slug/courses",
     *      summary="getCourseList",
     *      tags={"BookingPage"},
     *      description="Get all Courses available by slug",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
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
    public function index(Request $request): JsonResponse
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
                ->with('courseDates.courseGroups.courseSubgroups.monitor')
                ->where('school_id', $this->school->id)
                ->where('online', 1)
                ->where('active', 1)
                ->get();

            return $this->sendResponse($courses, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving bookings', 500);
        }
    }

    public function getCourseAvailability($course)
    {
        if (!$course) {
            return null; // o manejar como prefieras
        }

        $totalBookings = 0;
        $totalAvailablePlaces = 0;

        if ($course->course_type == 1) {
            // Cursos de tipo 1
            foreach ($course->courseDates as $courseDate) {
                foreach ($courseDate->courseSubgroups as $subgroup) {
                    $bookings = $subgroup->bookingUsers()->count();
                    $totalBookings += $bookings;
                    $totalAvailablePlaces += max(0, $subgroup->max_participants - $bookings);
                }
            }
        } else {
            // Cursos de tipo 2
            foreach ($course->courseDates as $courseDate) {
                $bookings = $courseDate->bookingUsers()->count();
                $totalBookings += $bookings;
            }
            $totalAvailablePlaces = max(0, $course->max_participants - $totalBookings);
        }

        return [
            'total_reservations' => $totalBookings,
            'total_available_places' => $totalAvailablePlaces
        ];
    }


    /**
     * @OA\Get(
     *      path="/booking/courses/{id}",
     *      summary="getCourseWithBookings",
     *      tags={"Admin"},
     *      description="Get Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Course"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id, Request $request): JsonResponse
    {
        //$school = $this->getSchool($request);

        // Comprueba si el cliente principal tiene booking_users asociados con el ID del monitor
        $course = Course::with( 'bookingUsers.client.sports',
            'courseDates.courseGroups.courseSubgroups.monitor')
            ->where('school_id',1)->find($id);

        if (empty($course)) {
            return $this->sendError('Course does not exist in this school');
        }

        return $this->sendResponse(new \App\Http\Resources\Admin\CourseResource($course), 'Course retrieved successfully');
    }


}
