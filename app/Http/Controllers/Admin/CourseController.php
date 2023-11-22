<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateCourseAPIRequest;
use App\Http\Resources\API\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class CourseController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/courses",
     *      summary="getCourseList",
     *      tags={"COurse"},
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
        $courses = Course::with('sport', 'courseDates.courseGroups.courseSubgroups', 'courseExtras')
            ->where('school_id', $request->school_id)->get();

        return $this->sendResponse(\App\Http\Resources\Admin\CourseResource::collection($courses),
            'Courses retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/courses",
     *      summary="createCourse",
     *      tags={"Course"},
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
        $request->validate([
            'course_type' => 'required|boolean',
            'is_flexible' => 'required|boolean',
            'sport_id' => 'required|exists:sports,id',
            'school_id' => 'required|exists:schools,id',
            'station_id' => 'nullable|exists:stations,id',
            'name' => 'required|string|max:65535',
            'short_description' => 'required|string|max:65535',
            'description' => 'required|string|max:65535',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_participants' => 'required|integer|min:1',
            'duration' => 'nullable|integer|min:1',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'date_start_res' => 'nullable|date',
            'date_end_res' => 'nullable|date|after_or_equal:date_start_res',
            'confirm_attendance' => 'required|boolean',
            'active' => 'required|boolean',
            'online' => 'required|boolean',
            'image' => 'nullable|string',
            'translations' => 'nullable|string',
            'price_range' => 'nullable|string',
            'discounts' => 'nullable|string',
            'settings' => 'nullable|string',
            'course_dates' => 'required|array',
            'course_dates.*.date' => 'required|date',
            'course_dates.*.hour_start' => 'required|string|max:255',
            'course_dates.*.hour_end' => 'required|string|max:255',
            'course_dates.*.groups' => 'required|array',
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

        return response()->json(['message' => 'Curso creado con éxito'], 201);
    }

    /**
     * @OA\Put(
     *      path="/courses/{id}",
     *      summary="updateCourse",
     *      tags={"Course"},
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

        // Actualiza los campos principales del curso
        $course->update($courseData);

        // Sincroniza las fechas
        if (isset($courseData['course_dates'])) {
            $updatedCourseDates = [];
            foreach ($courseData['course_dates'] as $dateData) {
                $date = $course->courseDates()->updateOrCreate(['id' => $dateData['id']], $dateData);
                $updatedCourseDates[] = $date->id;

                // Sincroniza los grupos
                if (isset($dateData['groups'])) {
                    $updatedCourseGroups = [];
                    foreach ($dateData['groups'] as $groupData) {
                        $group = $date->courseGroups()->updateOrCreate(['id' => $groupData['id']], $groupData);
                        $updatedCourseGroups[] = $group->id;

                        // Sincroniza los subgrupos
                        if (isset($groupData['subgroups'])) {
                            foreach ($groupData['subgroups'] as &$subgroup) {
                                $subgroup['course_id'] = $course->id;
                                $subgroup['course_date_id'] = $date->id;
                            }
                            $group->courseSubgroups()->delete(); // Elimina todos los subgrupos existentes
                            $group->courseSubgroups()->createMany($groupData['subgroups']);
                        }
                    }
                    $date->courseGroups()->whereNotIn('id', $updatedCourseGroups)->delete(); // Elimina los grupos que no se actualicen
                }
            }
            $course->courseDates()->whereNotIn('id', $updatedCourseDates)->delete(); // Elimina las fechas que no se actualicen
        }

        return $this->sendResponse($course, 'Course updated successfully');
    }

}
