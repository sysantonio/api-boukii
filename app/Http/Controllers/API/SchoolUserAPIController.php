<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSchoolUserAPIRequest;
use App\Http\Requests\API\UpdateSchoolUserAPIRequest;
use App\Http\Resources\API\SchoolUserResource;
use App\Models\SchoolUser;
use App\Repositories\SchoolUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SchoolUserController
 */

class SchoolUserAPIController extends AppBaseController
{
    /** @var  SchoolUserRepository */
    private $schoolUserRepository;

    public function __construct(SchoolUserRepository $schoolUserRepo)
    {
        $this->schoolUserRepository = $schoolUserRepo;
    }

    /**
     * @OA\Get(
     *      path="/school-users",
     *      summary="getSchoolUserList",
     *      tags={"SchoolUser"},
     *      description="Get all SchoolUsers",
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
     *                  @OA\Items(ref="#/components/schemas/SchoolUser")
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
        $schoolUsers = $this->schoolUserRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($schoolUsers, 'School Users retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/school-users",
     *      summary="createSchoolUser",
     *      tags={"SchoolUser"},
     *      description="Create SchoolUser",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolUser")
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
     *                  ref="#/components/schemas/SchoolUser"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSchoolUserAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $schoolUser = $this->schoolUserRepository->create($input);

        return $this->sendResponse($schoolUser, 'School User saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/school-users/{id}",
     *      summary="getSchoolUserItem",
     *      tags={"SchoolUser"},
     *      description="Get SchoolUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolUser",
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
     *                  ref="#/components/schemas/SchoolUser"
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
        /** @var SchoolUser $schoolUser */
        $schoolUser = $this->schoolUserRepository->find($id, with: $request->get('with', []));

        if (empty($schoolUser)) {
            return $this->sendError('School User not found');
        }

        return $this->sendResponse($schoolUser, 'School User retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/school-users/{id}",
     *      summary="updateSchoolUser",
     *      tags={"SchoolUser"},
     *      description="Update SchoolUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolUser",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolUser")
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
     *                  ref="#/components/schemas/SchoolUser"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSchoolUserAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var SchoolUser $schoolUser */
        $schoolUser = $this->schoolUserRepository->find($id, with: $request->get('with', []));

        if (empty($schoolUser)) {
            return $this->sendError('School User not found');
        }

        $schoolUser = $this->schoolUserRepository->update($input, $id);

        return $this->sendResponse(new SchoolUserResource($schoolUser), 'SchoolUser updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/school-users/{id}",
     *      summary="deleteSchoolUser",
     *      tags={"SchoolUser"},
     *      description="Delete SchoolUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolUser",
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
        /** @var SchoolUser $schoolUser */
        $schoolUser = $this->schoolUserRepository->find($id, with: $request->get('with', []));

        if (empty($schoolUser)) {
            return $this->sendError('School User not found');
        }

        $schoolUser->delete();

        return $this->sendSuccess('School User deleted successfully');
    }
}
