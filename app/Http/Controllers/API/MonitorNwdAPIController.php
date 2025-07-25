<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorNwdAPIRequest;
use App\Http\Requests\API\UpdateMonitorNwdAPIRequest;
use App\Http\Resources\API\MonitorNwdResource;
use App\Models\BookingUser;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Repositories\MonitorNwdRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class MonitorNwdController
 */

class MonitorNwdAPIController extends AppBaseController
{
    /** @var  MonitorNwdRepository */
    private $monitorNwdRepository;

    public function __construct(MonitorNwdRepository $monitorNwdRepo)
    {
        $this->monitorNwdRepository = $monitorNwdRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitor-nwds",
     *      summary="getMonitorNwdList",
     *      tags={"MonitorNwd"},
     *      description="Get all MonitorNwds",
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
     *                  @OA\Items(ref="#/components/schemas/MonitorNwd")
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
        $monitorNwds = $this->monitorNwdRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($monitorNwds, 'Monitor Nwds retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitor-nwds",
     *      summary="createMonitorNwd",
     *      tags={"MonitorNwd"},
     *      description="Create MonitorNwd",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorNwd")
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
     *                  ref="#/components/schemas/MonitorNwd"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorNwdAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        try {
            // Verificar si el monitor está ocupado antes de actualizar
            if (Monitor::isMonitorBusy($input['monitor_id'], $input['start_date'], $input['start_time'], $input['end_time'])) {
                return $this->sendError('El monitor está ocupado durante ese tiempo y no se puede crear el MonitorNwd', 409);
            }

            $monitorNwd = $this->monitorNwdRepository->create($input);

            return $this->sendResponse($monitorNwd, 'Monitor Nwd saved successfully');
        } catch (\Exception $e) {
            // Loguear el error
            Log::error('Error al guardar MonitorNwd:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retornar error al cliente
            return $this->sendError('Ocurrió un error al guardar el Monitor Nwd. Inténtalo nuevamente.', 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/monitor-nwds/{id}",
     *      summary="getMonitorNwdItem",
     *      tags={"MonitorNwd"},
     *      description="Get MonitorNwd",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorNwd",
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
     *                  ref="#/components/schemas/MonitorNwd"
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
        /** @var MonitorNwd $monitorNwd */
        $monitorNwd = $this->monitorNwdRepository->find($id, with: $request->get('with', []));

        if (empty($monitorNwd)) {
            return $this->sendError('Monitor Nwd not found');
        }

        return $this->sendResponse($monitorNwd, 'Monitor Nwd retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitor-nwds/{id}",
     *      summary="updateMonitorNwd",
     *      tags={"MonitorNwd"},
     *      description="Update MonitorNwd",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorNwd",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorNwd")
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
     *                  ref="#/components/schemas/MonitorNwd"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorNwdAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var MonitorNwd $monitorNwd */
        $monitorNwd = $this->monitorNwdRepository->find($id, with: $request->get('with', []));

        if (empty($monitorNwd)) {
            return $this->sendError('Monitor Nwd not found');
        }

        // Verificar si el monitor está ocupado antes de actualizar
        if (Monitor::isMonitorBusy($monitorNwd->monitor_id, $input['start_date'], $input['start_time'], $input['end_time'], $id)) {
            return $this->sendError('El monitor está ocupado durante ese tiempo y no se puede actualizar el MonitorNwd', 409);
        }

        $monitorNwd = $this->monitorNwdRepository->update($input, $id);

        return $this->sendResponse(new MonitorNwdResource($monitorNwd), 'MonitorNwd updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/monitor-nwds/{id}",
     *      summary="deleteMonitorNwd",
     *      tags={"MonitorNwd"},
     *      description="Delete MonitorNwd",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorNwd",
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
        /** @var MonitorNwd $monitorNwd */
        $monitorNwd = $this->monitorNwdRepository->find($id);

        if (empty($monitorNwd)) {
            return $this->sendError('Monitor Nwd not found');
        }

        $monitorNwd->delete();

        return $this->sendSuccess('Monitor Nwd deleted successfully');
    }
}
