<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorObservationAPIRequest;
use App\Http\Requests\API\UpdateMonitorObservationAPIRequest;
use App\Http\Resources\API\MonitorObservationResource;
use App\Models\MonitorObservation;
use App\Repositories\MonitorObservationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MonitorObservationController
 */

class MonitorObservationAPIController extends AppBaseController
{
    /** @var  MonitorObservationRepository */
    private $monitorObservationRepository;

    public function __construct(MonitorObservationRepository $monitorObservationRepo)
    {
        $this->monitorObservationRepository = $monitorObservationRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitor-observations",
     *      summary="getMonitorObservationList",
     *      tags={"MonitorObservation"},
     *      description="Get all MonitorObservations",
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
     *                  @OA\Items(ref="#/components/schemas/MonitorObservation")
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
        $monitorObservations = $this->monitorObservationRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($monitorObservations, 'Monitor Observations retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitor-observations",
     *      summary="createMonitorObservation",
     *      tags={"MonitorObservation"},
     *      description="Create MonitorObservation",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorObservation")
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
     *                  ref="#/components/schemas/MonitorObservation"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorObservationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $monitorObservation = $this->monitorObservationRepository->create($input);

        return $this->sendResponse($monitorObservation, 'Monitor Observation saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/monitor-observations/{id}",
     *      summary="getMonitorObservationItem",
     *      tags={"MonitorObservation"},
     *      description="Get MonitorObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorObservation",
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
     *                  ref="#/components/schemas/MonitorObservation"
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
        /** @var MonitorObservation $monitorObservation */
        $monitorObservation = $this->monitorObservationRepository->find($id, with: $request->get('with', []));

        if (empty($monitorObservation)) {
            return $this->sendError('Monitor Observation not found');
        }

        return $this->sendResponse($monitorObservation, 'Monitor Observation retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitor-observations/{id}",
     *      summary="updateMonitorObservation",
     *      tags={"MonitorObservation"},
     *      description="Update MonitorObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorObservation",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorObservation")
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
     *                  ref="#/components/schemas/MonitorObservation"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorObservationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var MonitorObservation $monitorObservation */
        $monitorObservation = $this->monitorObservationRepository->find($id, with: $request->get('with', []));

        if (empty($monitorObservation)) {
            return $this->sendError('Monitor Observation not found');
        }

        $monitorObservation = $this->monitorObservationRepository->update($input, $id);

        return $this->sendResponse(new MonitorObservationResource($monitorObservation), 'MonitorObservation updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/monitor-observations/{id}",
     *      summary="deleteMonitorObservation",
     *      tags={"MonitorObservation"},
     *      description="Delete MonitorObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorObservation",
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
        /** @var MonitorObservation $monitorObservation */
        $monitorObservation = $this->monitorObservationRepository->find($id, with: $request->get('with', []));

        if (empty($monitorObservation)) {
            return $this->sendError('Monitor Observation not found');
        }

        $monitorObservation->delete();

        return $this->sendSuccess('Monitor Observation deleted successfully');
    }
}
