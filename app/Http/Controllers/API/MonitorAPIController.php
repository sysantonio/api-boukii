<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorAPIRequest;
use App\Http\Requests\API\UpdateMonitorAPIRequest;
use App\Http\Resources\API\MonitorResource;
use App\Models\Monitor;
use App\Repositories\MonitorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MonitorController
 */

class MonitorAPIController extends AppBaseController
{
    /** @var  MonitorRepository */
    private $monitorRepository;

    public function __construct(MonitorRepository $monitorRepo)
    {
        $this->monitorRepository = $monitorRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitors",
     *      summary="getMonitorList",
     *      tags={"Monitor"},
     *      description="Get all Monitors",
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
     *                  @OA\Items(ref="#/components/schemas/Monitor")
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
        $monitors = $this->monitorRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(MonitorResource::collection($monitors), 'Monitors retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitors",
     *      summary="createMonitor",
     *      tags={"Monitor"},
     *      description="Create Monitor",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Monitor")
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
     *                  ref="#/components/schemas/Monitor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $monitor = $this->monitorRepository->create($input);

        return $this->sendResponse(new MonitorResource($monitor), 'Monitor saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/monitors/{id}",
     *      summary="getMonitorItem",
     *      tags={"Monitor"},
     *      description="Get Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
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
     *                  ref="#/components/schemas/Monitor"
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
        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id);

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        return $this->sendResponse(new MonitorResource($monitor), 'Monitor retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitors/{id}",
     *      summary="updateMonitor",
     *      tags={"Monitor"},
     *      description="Update Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Monitor")
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
     *                  ref="#/components/schemas/Monitor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id);

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        $monitor = $this->monitorRepository->update($input, $id);

        return $this->sendResponse(new MonitorResource($monitor), 'Monitor updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/monitors/{id}",
     *      summary="deleteMonitor",
     *      tags={"Monitor"},
     *      description="Delete Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
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
        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id);

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        $monitor->delete();

        return $this->sendSuccess('Monitor deleted successfully');
    }
}
