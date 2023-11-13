<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorSportsDegreeAPIRequest;
use App\Http\Requests\API\UpdateMonitorSportsDegreeAPIRequest;
use App\Http\Resources\API\MonitorSportsDegreeResource;
use App\Models\MonitorSportsDegree;
use App\Repositories\MonitorSportsDegreeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MonitorSportsDegreeController
 */

class MonitorSportsDegreeAPIController extends AppBaseController
{
    /** @var  MonitorSportsDegreeRepository */
    private $monitorSportsDegreeRepository;

    public function __construct(MonitorSportsDegreeRepository $monitorSportsDegreeRepo)
    {
        $this->monitorSportsDegreeRepository = $monitorSportsDegreeRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitor-sports-degrees",
     *      summary="getMonitorSportsDegreeList",
     *      tags={"MonitorSportsDegree"},
     *      description="Get all MonitorSportsDegrees",
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
     *                  @OA\Items(ref="#/components/schemas/MonitorSportsDegree")
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
        $monitorSportsDegrees = $this->monitorSportsDegreeRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(MonitorSportsDegreeResource::collection($monitorSportsDegrees), 'Monitor Sports Degrees retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitor-sports-degrees",
     *      summary="createMonitorSportsDegree",
     *      tags={"MonitorSportsDegree"},
     *      description="Create MonitorSportsDegree",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorSportsDegree")
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
     *                  ref="#/components/schemas/MonitorSportsDegree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorSportsDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $monitorSportsDegree = $this->monitorSportsDegreeRepository->create($input);

        return $this->sendResponse(new MonitorSportsDegreeResource($monitorSportsDegree), 'Monitor Sports Degree saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/monitor-sports-degrees/{id}",
     *      summary="getMonitorSportsDegreeItem",
     *      tags={"MonitorSportsDegree"},
     *      description="Get MonitorSportsDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportsDegree",
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
     *                  ref="#/components/schemas/MonitorSportsDegree"
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
        /** @var MonitorSportsDegree $monitorSportsDegree */
        $monitorSportsDegree = $this->monitorSportsDegreeRepository->find($id);

        if (empty($monitorSportsDegree)) {
            return $this->sendError('Monitor Sports Degree not found');
        }

        return $this->sendResponse(new MonitorSportsDegreeResource($monitorSportsDegree), 'Monitor Sports Degree retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitor-sports-degrees/{id}",
     *      summary="updateMonitorSportsDegree",
     *      tags={"MonitorSportsDegree"},
     *      description="Update MonitorSportsDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportsDegree",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorSportsDegree")
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
     *                  ref="#/components/schemas/MonitorSportsDegree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorSportsDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var MonitorSportsDegree $monitorSportsDegree */
        $monitorSportsDegree = $this->monitorSportsDegreeRepository->find($id);

        if (empty($monitorSportsDegree)) {
            return $this->sendError('Monitor Sports Degree not found');
        }

        $monitorSportsDegree = $this->monitorSportsDegreeRepository->update($input, $id);

        return $this->sendResponse(new MonitorSportsDegreeResource($monitorSportsDegree), 'MonitorSportsDegree updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/monitor-sports-degrees/{id}",
     *      summary="deleteMonitorSportsDegree",
     *      tags={"MonitorSportsDegree"},
     *      description="Delete MonitorSportsDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportsDegree",
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
        /** @var MonitorSportsDegree $monitorSportsDegree */
        $monitorSportsDegree = $this->monitorSportsDegreeRepository->find($id);

        if (empty($monitorSportsDegree)) {
            return $this->sendError('Monitor Sports Degree not found');
        }

        $monitorSportsDegree->delete();

        return $this->sendSuccess('Monitor Sports Degree deleted successfully');
    }
}
