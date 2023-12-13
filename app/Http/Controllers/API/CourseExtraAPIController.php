<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateCourseExtraAPIRequest;
use App\Http\Requests\API\UpdateCourseExtraAPIRequest;
use App\Http\Resources\API\CourseExtraResource;
use App\Models\CourseExtra;
use App\Repositories\CourseExtraRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CourseExtraController
 */

class CourseExtraAPIController extends AppBaseController
{
    /** @var  CourseExtraRepository */
    private $courseExtraRepository;

    public function __construct(CourseExtraRepository $courseExtraRepo)
    {
        $this->courseExtraRepository = $courseExtraRepo;
    }

    /**
     * @OA\Get(
     *      path="/course-extras",
     *      summary="getCourseExtraList",
     *      tags={"CourseExtra"},
     *      description="Get all CourseExtras",
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
     *                  @OA\Items(ref="#/components/schemas/CourseExtra")
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
        $courseExtras = $this->courseExtraRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($courseExtras, 'Course Extras retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/course-extras",
     *      summary="createCourseExtra",
     *      tags={"CourseExtra"},
     *      description="Create CourseExtra",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseExtra")
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
     *                  ref="#/components/schemas/CourseExtra"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseExtraAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $courseExtra = $this->courseExtraRepository->create($input);

        return $this->sendResponse($courseExtra, 'Course Extra saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/course-extras/{id}",
     *      summary="getCourseExtraItem",
     *      tags={"CourseExtra"},
     *      description="Get CourseExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseExtra",
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
     *                  ref="#/components/schemas/CourseExtra"
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
        /** @var CourseExtra $courseExtra */
        $courseExtra = $this->courseExtraRepository->find($id, with: $request->get('with', []));

        if (empty($courseExtra)) {
            return $this->sendError('Course Extra not found');
        }

        return $this->sendResponse($courseExtra, 'Course Extra retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/course-extras/{id}",
     *      summary="updateCourseExtra",
     *      tags={"CourseExtra"},
     *      description="Update CourseExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseExtra",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CourseExtra")
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
     *                  ref="#/components/schemas/CourseExtra"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCourseExtraAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CourseExtra $courseExtra */
        $courseExtra = $this->courseExtraRepository->find($id, with: $request->get('with', []));

        if (empty($courseExtra)) {
            return $this->sendError('Course Extra not found');
        }

        $courseExtra = $this->courseExtraRepository->update($input, $id);

        return $this->sendResponse(new CourseExtraResource($courseExtra), 'CourseExtra updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/course-extras/{id}",
     *      summary="deleteCourseExtra",
     *      tags={"CourseExtra"},
     *      description="Delete CourseExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CourseExtra",
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
        /** @var CourseExtra $courseExtra */
        $courseExtra = $this->courseExtraRepository->find($id);

        if (empty($courseExtra)) {
            return $this->sendError('Course Extra not found');
        }

        $courseExtra->delete();

        return $this->sendSuccess('Course Extra deleted successfully');
    }
}
