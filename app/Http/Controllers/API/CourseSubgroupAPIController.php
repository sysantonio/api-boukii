<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateCourseSubgroupAPIRequest;
use App\Http\Requests\API\UpdateCourseSubgroupAPIRequest;
use App\Http\Resources\API\CourseSubgroupResource;
use App\Models\CourseSubgroup;
use App\Repositories\CourseSubgroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CourseSubgroupController
 */

class CourseSubgroupAPIController extends AppBaseController
{
    /** @var  CourseSubgroupRepository */
    private $courseSubgroupRepository;

    public function __construct(CourseSubgroupRepository $courseSubgroupRepo)
    {
        $this->courseSubgroupRepository = $courseSubgroupRepo;
    }

    /**
     * @OA\Get(
     *      path="/course-subgroups",
     *      summary="getCourseSubgroupList",
     *      tags={"CourseSubgroup"},
     *      description="Get all CourseSubgroups",
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
     *                  @OA\Items(ref="#/components/schemas/CourseSubgroup")
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
        $courseSubgroups = $this->courseSubgroupRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(CourseSubgroupResource::collection($courseSubgroups), 'Course Subgroups retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/course-subgroups",
     *      summary="createCourseSubgroup",
     *      tags={"CourseSubgroup"},
     *      description="Create CourseSubgroup",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseSubgroup")
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
     *                  ref="#/components/schemas/CourseSubgroup"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseSubgroupAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $courseSubgroup = $this->courseSubgroupRepository->create($input);

        return $this->sendResponse(new CourseSubgroupResource($courseSubgroup), 'Course Subgroup saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/course-subgroups/{id}",
     *      summary="getCourseSubgroupItem",
     *      tags={"CourseSubgroup"},
     *      description="Get CourseSubgroup",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseSubgroup",
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
     *                  ref="#/components/schemas/CourseSubgroup"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        /** @var CourseSubgroup $courseSubgroup */
        $courseSubgroup = $this->courseSubgroupRepository->find($id);

        if (empty($courseSubgroup)) {
            return $this->sendError('Course Subgroup not found');
        }

        return $this->sendResponse(new CourseSubgroupResource($courseSubgroup), 'Course Subgroup retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/course-subgroups/{id}",
     *      summary="updateCourseSubgroup",
     *      tags={"CourseSubgroup"},
     *      description="Update CourseSubgroup",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseSubgroup",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseSubgroup")
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
     *                  ref="#/components/schemas/CourseSubgroup"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCourseSubgroupAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CourseSubgroup $courseSubgroup */
        $courseSubgroup = $this->courseSubgroupRepository->find($id);

        if (empty($courseSubgroup)) {
            return $this->sendError('Course Subgroup not found');
        }

        $courseSubgroup = $this->courseSubgroupRepository->update($input, $id);

        return $this->sendResponse(new CourseSubgroupResource($courseSubgroup), 'CourseSubgroup updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/course-subgroups/{id}",
     *      summary="deleteCourseSubgroup",
     *      tags={"CourseSubgroup"},
     *      description="Delete CourseSubgroup",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseSubgroup",
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
        /** @var CourseSubgroup $courseSubgroup */
        $courseSubgroup = $this->courseSubgroupRepository->find($id);

        if (empty($courseSubgroup)) {
            return $this->sendError('Course Subgroup not found');
        }

        $courseSubgroup->delete();

        return $this->sendSuccess('Course Subgroup deleted successfully');
    }
}
