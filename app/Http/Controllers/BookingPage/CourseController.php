<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use App\Models\Degree;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $sportId = $request->input('sport_id') ?? 1;
        $clientId = $request->input('client_id');
        $minAge = $request->input('min_age') ?? null;
        $maxAge = $request->input('max_age') ?? null;
        $clientId = $request->input('client_id');
        $degreeOrder = $request->input('degree_order');
        $degreeOrderArray = [];
        if($degreeOrder) {
            $degreeOrderArray = explode(',', $degreeOrder);
        }

        $getLowerDegrees = 1;
        // return $this->sendResponse($this->school->id, 'Courses retrieved successfully');
        $today = now(); // Obtener la fecha actual

        try {
            $courses =
                Course::withAvailableDates($type, $startDate, $endDate, $sportId, $clientId, null, $getLowerDegrees,
                    $degreeOrderArray, $minAge, $maxAge)
                    ->with('courseDates.courseGroups.courseSubgroups.monitor')
                    ->where('school_id', $this->school->id)
                    ->where('online', 1)
                    ->where('active', 1)
                    ->where(function($query) use ($today) {
                        $query->where(function($subquery) use ($today) {
                            $subquery->whereNull('date_start_res')
                                ->whereNull('date_end_res');
                        })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereDate('date_start_res', '<=', $today)
                                    ->whereDate('date_end_res', '>=', $today);
                            })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereDate('date_start_res', '=', $today)
                                    ->whereNotNull('date_end_res');
                            })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereNotNull('date_start_res')
                                    ->whereDate('date_end_res', '=', $today);
                            });
                    })

                    ->get();

            return $this->sendResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
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
     *      path="/slug/courses/{id}",
     *      summary="getCourseWithBookings",
     *      tags={"BookingPage"},
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
        $course = Course::with([
            'bookingUsers.client.sports',
            'courseExtras',
            'courseDates.courseGroups' => function ($query) {
                $query->with(['courseSubgroups' => function ($subQuery) {
                    $subQuery->withCount('bookingUsers')->with('degree');
                }]);
            }
        ])->where('school_id', $this->school->id)->find($id);

        if (empty($course)) {
            return $this->sendError('Course does not exist in this school');
        } else {
            $availableDegreeIds = collect();
            $unAvailableDegreeIds = collect();
            foreach ($course->courseDates as $courseDate) {
                foreach ($courseDate->courseGroups as $group) {
                    $group->courseSubgroups = $group->courseSubgroups->filter(function ($subgroup)
                    use ($availableDegreeIds, $group, $unAvailableDegreeIds) {
                        $hasAvailability = $subgroup->booking_users_count < $subgroup->max_participants;
                        $availableDegree = [
                            'degree_id' => $group->degree_id,
                            'recommended_age' => $group->recommended_age
                        ];
                        if ($hasAvailability) {
                            if(!$unAvailableDegreeIds->contains($availableDegree)) {
                                $availableDegreeIds->push($availableDegree);
                            }
                        } else {
                            $unAvailableDegreeIds->push($availableDegree);
                        }
                        return $hasAvailability;
                    });

                    if ($group->courseSubgroups->isEmpty()) {
                        $courseDate->courseGroups = $courseDate->courseGroups->reject(function ($g) use ($group) {
                            return $g->id === $group->id;
                        });
                    }
                }
            }

            $uniqueDegrees = $availableDegreeIds->unique(function ($item) {
                return $item['degree_id'] . '-' . $item['recommended_age'];
            });

            $availableDegrees = $uniqueDegrees->map(function ($item) {
                $degree = Degree::find($item['degree_id']);
                if ($degree) {
                    $degree->load('degreesSchoolSportGoals');
                    $degree->recommended_age = $item['recommended_age'];
                }
                return $degree;
            })->filter();

            $course->availableDegrees = $availableDegrees;
        }

        return $this->sendResponse($course,
            'Course retrieved successfully');
    }


}
