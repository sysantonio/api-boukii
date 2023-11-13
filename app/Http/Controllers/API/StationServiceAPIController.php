<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateStationServiceAPIRequest;
use App\Http\Requests\API\UpdateStationServiceAPIRequest;
use App\Http\Resources\API\StationServiceResource;
use App\Models\StationService;
use App\Repositories\StationServiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class StationServiceController
 */

class StationServiceAPIController extends AppBaseController
{
    /** @var  StationServiceRepository */
    private $stationServiceRepository;

    public function __construct(StationServiceRepository $stationServiceRepo)
    {
        $this->stationServiceRepository = $stationServiceRepo;
    }

    /**
     * @OA\Get(
     *      path="/station-services",
     *      summary="getStationServiceList",
     *      tags={"StationService"},
     *      description="Get all StationServices",
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
     *                  @OA\Items(ref="#/components/schemas/StationService")
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
        $stationServices = $this->stationServiceRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(StationServiceResource::collection($stationServices), 'Station Services retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/station-services",
     *      summary="createStationService",
     *      tags={"StationService"},
     *      description="Create StationService",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/StationService")
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
     *                  ref="#/components/schemas/StationService"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateStationServiceAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $stationService = $this->stationServiceRepository->create($input);

        return $this->sendResponse(new StationServiceResource($stationService), 'Station Service saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/station-services/{id}",
     *      summary="getStationServiceItem",
     *      tags={"StationService"},
     *      description="Get StationService",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationService",
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
     *                  ref="#/components/schemas/StationService"
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
        /** @var StationService $stationService */
        $stationService = $this->stationServiceRepository->find($id);

        if (empty($stationService)) {
            return $this->sendError('Station Service not found');
        }

        return $this->sendResponse(new StationServiceResource($stationService), 'Station Service retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/station-services/{id}",
     *      summary="updateStationService",
     *      tags={"StationService"},
     *      description="Update StationService",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationService",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/StationService")
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
     *                  ref="#/components/schemas/StationService"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateStationServiceAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var StationService $stationService */
        $stationService = $this->stationServiceRepository->find($id);

        if (empty($stationService)) {
            return $this->sendError('Station Service not found');
        }

        $stationService = $this->stationServiceRepository->update($input, $id);

        return $this->sendResponse(new StationServiceResource($stationService), 'StationService updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/station-services/{id}",
     *      summary="deleteStationService",
     *      tags={"StationService"},
     *      description="Delete StationService",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of StationService",
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
        /** @var StationService $stationService */
        $stationService = $this->stationServiceRepository->find($id);

        if (empty($stationService)) {
            return $this->sendError('Station Service not found');
        }

        $stationService->delete();

        return $this->sendSuccess('Station Service deleted successfully');
    }
}
