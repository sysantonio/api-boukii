<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use App\Models\Sport;
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

class SchoolController extends SlugAuthController
{

    /**
     * @OA\Get(
     *      path="/slug/school",
     *      summary="getSchooldata",
     *      tags={"BookingPage"},
     *      description="Get school data by slug",
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
     *                  @OA\Items(ref="#/components/schemas/School")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $school = $this->school;
            $schoolId = $school->id;
            //$school->load('degrees');
            $sports = Sport::with(['degrees', 'schools' => function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            }])->whereHas('schools', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->whereHas('courses', function($query) {
                $query->where('active', 1)->where('online', 1);
            })->get();
            $school->sports = $sports;
            return $this->sendResponse($school, 'School retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }



}
