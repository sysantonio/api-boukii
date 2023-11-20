<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateStationAPIRequest;
use App\Http\Requests\API\UpdateStationAPIRequest;
use App\Http\Resources\API\StationResource;
use App\Models\Station;
use App\Repositories\StationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class StationController
 */

class StationAPIController extends AppBaseController
{
    /** @var  StationRepository */
    private $stationRepository;

    public function __construct(StationRepository $stationRepo)
    {
        $this->stationRepository = $stationRepo;
    }

    /**
     * @OA\Get(
     *      path="/stations",
     *      summary="getStationList",
     *      tags={"Station"},
     *      description="Get all Stations",
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
     *                  @OA\Items(ref="#/components/schemas/Station")
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
        $stations = $this->stationRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(StationResource::collection($stations), 'Stations retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/stations",
     *      summary="createStation",
     *      tags={"Station"},
     *      description="Create Station",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Station")
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
     *                  ref="#/components/schemas/Station"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateStationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $station = $this->stationRepository->create($input);

        return $this->sendResponse(new StationResource($station), 'Station saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/stations/{id}",
     *      summary="getStationItem",
     *      tags={"Station"},
     *      description="Get Station",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Station",
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
     *                  ref="#/components/schemas/Station"
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
        /** @var Station $station */
        $station = $this->stationRepository->find($id);

        if (empty($station)) {
            return $this->sendError('Station not found');
        }

        return $this->sendResponse(new StationResource($station), 'Station retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/stations/{id}",
     *      summary="updateStation",
     *      tags={"Station"},
     *      description="Update Station",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Station",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Station")
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
     *                  ref="#/components/schemas/Station"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateStationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Station $station */
        $station = $this->stationRepository->find($id);

        if (empty($station)) {
            return $this->sendError('Station not found');
        }

        $station = $this->stationRepository->update($input, $id);

        return $this->sendResponse(new StationResource($station), 'Station updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/stations/{id}",
     *      summary="deleteStation",
     *      tags={"Station"},
     *      description="Delete Station",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Station",
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
        /** @var Station $station */
        $station = $this->stationRepository->find($id);

        if (empty($station)) {
            return $this->sendError('Station not found');
        }

        $station->delete();

        return $this->sendSuccess('Station deleted successfully');
    }
}
