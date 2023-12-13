<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use App\Repositories\CourseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class CourseController extends AppBaseController
{

    private $courseRepository;

    public function __construct(CourseRepository $courseRepo)
    {
        $this->courseRepository = $courseRepo;
    }


    /**
     * @OA\Get(
     *      path="/admin/courses",
     *      summary="getCourseList",
     *      tags={"Admin"},
     *      description="Get all Courses",
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
        // Define el valor por defecto para 'perPage'
        $perPage = $request->input('perPage', 10);

        // Obtén el ID de la escuela y añádelo a los parámetros de búsqueda
        $school = $this->getSchool($request);
        $searchParameters = array_merge($request->all(), ['school_id' => $school->id]);

        // Utiliza el CourseRepository para obtener los cursos con los parámetros de búsqueda
        $courses = $this->courseRepository->all(
            $searchParameters,
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $perPage,
            $request->get('with', ['station', 'sport', 'courseDates.courseGroups.courseSubgroups', 'courseExtras']),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        // Calcular reservas y plazas disponibles para cada curso
        foreach ($courses as $course) {
            $availability = $this->getCourseAvailability($course);
            $course->total_reservations = $availability['total_reservations'];
            $course->total_available_places = $availability['total_available_places'];
        }

        return $this->sendResponse(\App\Http\Resources\Admin\CourseResource::collection($courses),
            'Courses retrieved successfully');
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
     *      path="/admin/courses/{id}",
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
        $course = Course::with( 'station','bookingUsers.client.sports',
            'courseDates.courseGroups.courseSubgroups.monitor')
            ->where('school_id',1)->find($id);

        if (empty($course)) {
            return $this->sendError('Course does not exist in this school');
        }

        return $this->sendResponse(new \App\Http\Resources\Admin\CourseResource($course), 'Course retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/admin/courses",
     *      summary="createCourse",
     *      tags={"Admin"},
     *      description="Create Course",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
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
    public function store(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $request->merge(["school_id"=> $school->id]);

        //$request->school_id = $school->id;
        $request->validate([
            'course_type' => 'required',
            'is_flexible' => 'required',
            'sport_id' => 'required|exists:sports,id',
            'school_id' => 'required|exists:schools,id',
            'station_id' => 'nullable|exists:stations,id',
            'name' => 'required|string|max:65535',
            'short_description' => 'required|string|max:65535',
            'description' => 'required|string|max:65535',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_participants' => 'required|integer|min:1',
            'duration' => 'nullable',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'date_start_res' => 'nullable|date',
            'date_end_res' => 'nullable|date|after_or_equal:date_start_res',
            'confirm_attendance' => 'required|boolean',
            'active' => 'required|boolean',
            'online' => 'required|boolean',
            'image' => 'nullable|string',
            'translations' => 'nullable|string',
            'price_range' => 'nullable',
            'discounts' => 'nullable|string',
            'settings' => 'nullable|string',
            'course_dates' => 'required|array',
            'course_dates.*.date' => 'required|date',
            'course_dates.*.hour_start' => 'required|string|max:255',
            'course_dates.*.hour_end' => 'required|string|max:255',
            'course_dates.*.groups' => 'required_if:course_type,1|array',
            'course_dates.*.groups.*.degree_id' => 'required|exists:degrees,id',
            'course_dates.*.groups.*.age_min' => 'nullable|integer|min:0',
            'course_dates.*.groups.*.age_max' => 'nullable|integer|min:0',
            'course_dates.*.groups.*.recommended_age' => 'nullable|integer|min:0',
            'course_dates.*.groups.*.teachers_min' => 'nullable|integer|min:1',
            'course_dates.*.groups.*.teachers_max' => 'nullable|integer|min:1',
            'course_dates.*.groups.*.observations' => 'nullable|string|max:65535',
            'course_dates.*.groups.*.teacher_min_degree' => 'nullable|exists:degrees,id',
            'course_dates.*.groups.*.subgroups' => 'required|array',
            'course_dates.*.groups.*.subgroups.*.degree_id' => 'required|exists:degrees,id',
            'course_dates.*.groups.*.subgroups.*.monitor_id' => 'nullable|exists:monitors,id',
            'course_dates.*.groups.*.subgroups.*.max_participants' => 'nullable|integer|min:0',
        ]);

        $courseData = $request->all();

        if(!empty($courseData['image'])) {
            $base64Image = $request->input('image');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    $this->sendError('base64_decode failed');
                }
            } else {
                $this->sendError('did not match data URI with image data');
            }

            $imageName = 'course/image_'.time().'.'.$type;
            Storage::disk('public')->put($imageName, $imageData);
            $courseData['image'] = url(Storage::url($imageName));
        }

        $course = Course::create($courseData);

        // Crear las fechas y grupos
        if (isset($courseData['course_dates'])) {
            foreach ($courseData['course_dates'] as $dateData) {

                $date = $course->courseDates()->create($dateData);

                if (isset($dateData['groups'])) {
                    foreach ($dateData['groups'] as $groupData) {
                        $groupData['course_id'] = $course->id;
                        $group = $date->courseGroups()->create($groupData);

                        if (isset($groupData['subgroups'])) {
                            foreach ($groupData['subgroups'] as &$subgroup) {
                                $subgroup['course_id'] = $course->id;
                                $subgroup['course_date_id'] = $date->id;
                            }

                            $group->courseSubgroups()->createMany($groupData['subgroups']);
                        }
                    }
                }
            }
        }

        return $this->sendResponse($course,'Curso creado con éxito');
    }

    /**
     * @OA\Put(
     *      path="/admin/courses/{id}",
     *      summary="updateCourse",
     *      tags={"Admin"},
     *      description="Update Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
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
    public function update($id, Request $request): JsonResponse
    {
        $courseData = $request->all();
        $course = Course::findOrFail($id); // Suponiendo que tienes el ID del curso que deseas editar

        if(!empty($courseData['image'])) {
            $base64Image = $request->input('image');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    $this->sendError('base64_decode failed');
                }
            } else {
                $this->sendError('did not match data URI with image data');
            }

            $imageName = 'course/image_'.time().'.'.$type;
            Storage::disk('public')->put($imageName, $imageData);
            $courseData['image'] = url(url(Storage::url($imageName)));
        } else {
            $courseData = $request->except('image');
        }

        // Actualiza los campos principales del curso
        $course->update($courseData);

        // Sincroniza las fechas
        if (isset($courseData['course_dates'])) {
            $updatedCourseDates = [];
            foreach ($courseData['course_dates'] as $dateData) {
                // Verifica si existe 'id' antes de usarlo
                $dateId = isset($dateData['id']) ? $dateData['id'] : null;
                $date = $course->courseDates()->updateOrCreate(['id' => $dateId], $dateData);
                $updatedCourseDates[] = $date->id;

                if (isset($dateData['groups'])) {
                    $updatedCourseGroups = [];
                    foreach ($dateData['groups'] as $groupData) {
                        // Verifica si existe 'id' antes de usarlo
                        $groupId = isset($groupData['id']) ? $groupData['id'] : null;
                        $group = $date->courseGroups()->updateOrCreate(['id' => $groupId], $groupData);
                        $updatedCourseGroups[] = $group->id;

                        if (isset($groupData['subgroups'])) {
                            foreach ($groupData['subgroups'] as $subgroupData) {
                                // Preparar los datos de subgroup
                                $subgroupData['course_id'] = $course->id;
                                $subgroupData['course_date_id'] = $date->id;

                                // Verifica si existe 'id' antes de usarlo
                                $subgroupId = isset($subgroupData['id']) ? $subgroupData['id'] : null;
                                if ($subgroupId) {
                                    $group->courseSubgroups()->updateOrCreate(['id' => $subgroupId], $subgroupData);
                                } else {
                                    $group->courseSubgroups()->create($subgroupData);
                                }
                            }
                            // Considera si necesitas borrar subgrupos aquí
                        }
                    }
                    $date->courseGroups()->whereNotIn('id', $updatedCourseGroups)->delete();
                }
            }
            $course->courseDates()->whereNotIn('id', $updatedCourseDates)->delete();
        }

        return $this->sendResponse($course, 'Course updated successfully');
    }

}
