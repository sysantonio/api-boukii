<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateCourseDateAPIRequest;
use App\Http\Requests\API\UpdateCourseDateAPIRequest;
use App\Http\Resources\API\CourseDateResource;
use App\Models\CourseDate;
use App\Repositories\CourseDateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CourseDateController
 */

class CourseDateAPIController extends AppBaseController
{
    /** @var  CourseDateRepository */
    private $courseDateRepository;

    public function __construct(CourseDateRepository $courseDateRepo)
    {
        $this->courseDateRepository = $courseDateRepo;
    }

    /**
     * @OA\Get(
     *      path="/course-dates",
     *      summary="getCourseDateList",
     *      tags={"CourseDate"},
     *      description="Get all CourseDates",
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
     *                  @OA\Items(ref="#/components/schemas/CourseDate")
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
        $courseDates = $this->courseDateRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($courseDates, 'Course Dates retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/course-dates",
     *      summary="createCourseDate",
     *      tags={"CourseDate"},
     *      description="Create CourseDate",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseDate")
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
     *                  ref="#/components/schemas/CourseDate"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseDateAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $courseDate = $this->courseDateRepository->create($input);

        return $this->sendResponse($courseDate, 'Course Date saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/course-dates/{id}",
     *      summary="getCourseDateItem",
     *      tags={"CourseDate"},
     *      description="Get CourseDate",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseDate",
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
     *                  ref="#/components/schemas/CourseDate"
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
        /** @var CourseDate $courseDate */
        $courseDate = $this->courseDateRepository->find($id, with: $request->get('with', []));

        if (empty($courseDate)) {
            return $this->sendError('Course Date not found');
        }

        return $this->sendResponse($courseDate, 'Course Date retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/course-dates/{id}",
     *      summary="updateCourseDate",
     *      tags={"CourseDate"},
     *      description="Update CourseDate",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseDate",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseDate")
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
     *                  ref="#/components/schemas/CourseDate"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCourseDateAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CourseDate $courseDate */
        $courseDate = $this->courseDateRepository->find($id, with: $request->get('with', []));

        if (empty($courseDate)) {
            return $this->sendError('Course Date not found');
        }

        $courseDate = $this->courseDateRepository->update($input, $id);

        return $this->sendResponse(new CourseDateResource($courseDate), 'CourseDate updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/course-dates/{id}",
     *      summary="deleteCourseDate",
     *      tags={"CourseDate"},
     *      description="Delete CourseDate",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseDate",
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
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        /** @var CourseDate $courseDate */
        $courseDate = $this->courseDateRepository->find($id, with: $request->get('with', []));

        if (empty($courseDate)) {
            return $this->sendError('Course Date not found');
        }

        $courseDate->delete();

        return $this->sendSuccess('Course Date deleted successfully');
    }
}
