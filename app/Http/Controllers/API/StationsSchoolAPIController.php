<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateStationsSchoolAPIRequest;
use App\Http\Requests\API\UpdateStationsSchoolAPIRequest;
use App\Http\Resources\API\StationsSchoolResource;
use App\Models\StationsSchool;
use App\Repositories\StationsSchoolRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class StationsSchoolController
 */

class StationsSchoolAPIController extends AppBaseController
{
    /** @var  StationsSchoolRepository */
    private $stationsSchoolRepository;

    public function __construct(StationsSchoolRepository $stationsSchoolRepo)
    {
        $this->stationsSchoolRepository = $stationsSchoolRepo;
    }

    /**
     * @OA\Get(
     *      path="/stations-schools",
     *      summary="getStationsSchoolList",
     *      tags={"StationsSchool"},
     *      description="Get all StationsSchools",
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
     *                  @OA\Items(ref="#/components/schemas/StationsSchool")
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
        $stationsSchools = $this->stationsSchoolRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($stationsSchools, 'Stations Schools retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/stations-schools",
     *      summary="createStationsSchool",
     *      tags={"StationsSchool"},
     *      description="Create StationsSchool",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/StationsSchool")
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
     *                  ref="#/components/schemas/StationsSchool"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateStationsSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $stationsSchool = $this->stationsSchoolRepository->create($input);

        return $this->sendResponse($stationsSchool, 'Stations School saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/stations-schools/{id}",
     *      summary="getStationsSchoolItem",
     *      tags={"StationsSchool"},
     *      description="Get StationsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationsSchool",
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
     *                  ref="#/components/schemas/StationsSchool"
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
        /** @var StationsSchool $stationsSchool */
        $stationsSchool = $this->stationsSchoolRepository->find($id, with: $request->get('with', []));

        if (empty($stationsSchool)) {
            return $this->sendError('Stations School not found');
        }

        return $this->sendResponse($stationsSchool, 'Stations School retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/stations-schools/{id}",
     *      summary="updateStationsSchool",
     *      tags={"StationsSchool"},
     *      description="Update StationsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationsSchool",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/StationsSchool")
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
     *                  ref="#/components/schemas/StationsSchool"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateStationsSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var StationsSchool $stationsSchool */
        $stationsSchool = $this->stationsSchoolRepository->find($id, with: $request->get('with', []));

        if (empty($stationsSchool)) {
            return $this->sendError('Stations School not found');
        }

        $stationsSchool = $this->stationsSchoolRepository->update($input, $id);

        return $this->sendResponse(new StationsSchoolResource($stationsSchool), 'StationsSchool updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/stations-schools/{id}",
     *      summary="deleteStationsSchool",
     *      tags={"StationsSchool"},
     *      description="Delete StationsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationsSchool",
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
        /** @var StationsSchool $stationsSchool */
        $stationsSchool = $this->stationsSchoolRepository->find($id);

        if (empty($stationsSchool)) {
            return $this->sendError('Stations School not found');
        }

        $stationsSchool->delete();

        return $this->sendSuccess('Stations School deleted successfully');
    }
}
