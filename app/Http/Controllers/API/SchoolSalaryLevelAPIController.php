<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSchoolSalaryLevelAPIRequest;
use App\Http\Requests\API\UpdateSchoolSalaryLevelAPIRequest;
use App\Http\Resources\API\SchoolSalaryLevelResource;
use App\Models\SchoolSalaryLevel;
use App\Repositories\SchoolSalaryLevelRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SchoolSalaryLevelController
 */

class SchoolSalaryLevelAPIController extends AppBaseController
{
    /** @var  SchoolSalaryLevelRepository */
    private $schoolSalaryLevelRepository;

    public function __construct(SchoolSalaryLevelRepository $schoolSalaryLevelRepo)
    {
        $this->schoolSalaryLevelRepository = $schoolSalaryLevelRepo;
    }

    /**
     * @OA\Get(
     *      path="/school-salary-levels",
     *      summary="getSchoolSalaryLevelList",
     *      tags={"SchoolSalaryLevel"},
     *      description="Get all SchoolSalaryLevels",
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
     *                  @OA\Items(ref="#/components/schemas/SchoolSalaryLevel")
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
        $schoolSalaryLevels = $this->schoolSalaryLevelRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(SchoolSalaryLevelResource::collection($schoolSalaryLevels), 'School Salary Levels retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/school-salary-levels",
     *      summary="createSchoolSalaryLevel",
     *      tags={"SchoolSalaryLevel"},
     *      description="Create SchoolSalaryLevel",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolSalaryLevel")
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
     *                  ref="#/components/schemas/SchoolSalaryLevel"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSchoolSalaryLevelAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $schoolSalaryLevel = $this->schoolSalaryLevelRepository->create($input);

        return $this->sendResponse(new SchoolSalaryLevelResource($schoolSalaryLevel), 'School Salary Level saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/school-salary-levels/{id}",
     *      summary="getSchoolSalaryLevelItem",
     *      tags={"SchoolSalaryLevel"},
     *      description="Get SchoolSalaryLevel",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSalaryLevel",
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
     *                  ref="#/components/schemas/SchoolSalaryLevel"
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
        /** @var SchoolSalaryLevel $schoolSalaryLevel */
        $schoolSalaryLevel = $this->schoolSalaryLevelRepository->find($id);

        if (empty($schoolSalaryLevel)) {
            return $this->sendError('School Salary Level not found');
        }

        return $this->sendResponse(new SchoolSalaryLevelResource($schoolSalaryLevel), 'School Salary Level retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/school-salary-levels/{id}",
     *      summary="updateSchoolSalaryLevel",
     *      tags={"SchoolSalaryLevel"},
     *      description="Update SchoolSalaryLevel",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSalaryLevel",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolSalaryLevel")
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
     *                  ref="#/components/schemas/SchoolSalaryLevel"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSchoolSalaryLevelAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var SchoolSalaryLevel $schoolSalaryLevel */
        $schoolSalaryLevel = $this->schoolSalaryLevelRepository->find($id);

        if (empty($schoolSalaryLevel)) {
            return $this->sendError('School Salary Level not found');
        }

        $schoolSalaryLevel = $this->schoolSalaryLevelRepository->update($input, $id);

        return $this->sendResponse(new SchoolSalaryLevelResource($schoolSalaryLevel), 'SchoolSalaryLevel updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/school-salary-levels/{id}",
     *      summary="deleteSchoolSalaryLevel",
     *      tags={"SchoolSalaryLevel"},
     *      description="Delete SchoolSalaryLevel",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSalaryLevel",
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
        /** @var SchoolSalaryLevel $schoolSalaryLevel */
        $schoolSalaryLevel = $this->schoolSalaryLevelRepository->find($id);

        if (empty($schoolSalaryLevel)) {
            return $this->sendError('School Salary Level not found');
        }

        $schoolSalaryLevel->delete();

        return $this->sendSuccess('School Salary Level deleted successfully');
    }
}
