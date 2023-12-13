<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorSportAuthorizedDegreeAPIRequest;
use App\Http\Requests\API\UpdateMonitorSportAuthorizedDegreeAPIRequest;
use App\Http\Resources\API\MonitorSportAuthorizedDegreeResource;
use App\Models\MonitorSportAuthorizedDegree;
use App\Repositories\MonitorSportAuthorizedDegreeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MonitorSportAuthorizedDegreeController
 */

class MonitorSportAuthorizedDegreeAPIController extends AppBaseController
{
    /** @var  MonitorSportAuthorizedDegreeRepository */
    private $monitorSportAuthorizedDegreeRepository;

    public function __construct(MonitorSportAuthorizedDegreeRepository $monitorSportAuthorizedDegreeRepo)
    {
        $this->monitorSportAuthorizedDegreeRepository = $monitorSportAuthorizedDegreeRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitor-sport-authorized-degrees",
     *      summary="getMonitorSportAuthorizedDegreeList",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Get all MonitorSportAuthorizedDegrees",
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
     *                  @OA\Items(ref="#/components/schemas/MonitorSportAuthorizedDegree")
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
        $monitorSportAuthorizedDegrees = $this->monitorSportAuthorizedDegreeRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($monitorSportAuthorizedDegrees, 'Monitor Sport Authorized Degrees retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitor-sport-authorized-degrees",
     *      summary="createMonitorSportAuthorizedDegree",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Create MonitorSportAuthorizedDegree",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorSportAuthorizedDegree")
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
     *                  ref="#/components/schemas/MonitorSportAuthorizedDegree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorSportAuthorizedDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $monitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepository->create($input);

        return $this->sendResponse($monitorSportAuthorizedDegree, 'Monitor Sport Authorized Degree saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/monitor-sport-authorized-degrees/{id}",
     *      summary="getMonitorSportAuthorizedDegreeItem",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Get MonitorSportAuthorizedDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportAuthorizedDegree",
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
     *                  ref="#/components/schemas/MonitorSportAuthorizedDegree"
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
        /** @var MonitorSportAuthorizedDegree $monitorSportAuthorizedDegree */
        $monitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepository->find($id, with: $request->get('with', []));

        if (empty($monitorSportAuthorizedDegree)) {
            return $this->sendError('Monitor Sport Authorized Degree not found');
        }

        return $this->sendResponse($monitorSportAuthorizedDegree, 'Monitor Sport Authorized Degree retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitor-sport-authorized-degrees/{id}",
     *      summary="updateMonitorSportAuthorizedDegree",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Update MonitorSportAuthorizedDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportAuthorizedDegree",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/MonitorSportAuthorizedDegree")
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
     *                  ref="#/components/schemas/MonitorSportAuthorizedDegree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorSportAuthorizedDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var MonitorSportAuthorizedDegree $monitorSportAuthorizedDegree */
        $monitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepository->find($id, with: $request->get('with', []));

        if (empty($monitorSportAuthorizedDegree)) {
            return $this->sendError('Monitor Sport Authorized Degree not found');
        }

        $monitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepository->update($input, $id);

        return $this->sendResponse(new MonitorSportAuthorizedDegreeResource($monitorSportAuthorizedDegree), 'MonitorSportAuthorizedDegree updated successfully');
    }

    /**
     * @OA\Put(
     *      path="/api/monitor-sport-authorized-degrees/multiple",
     *      summary="Update or insert multiple monitor sport authorized degrees",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Update or insert monitor sport authorized degrees in bulk.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  required={"monitor_sport_id", "degree_id"},
     *                  @OA\Property(property="monitor_sport_id", type="integer", description="Monitor Sport ID"),
     *                  @OA\Property(property="degree_id", type="integer", description="Degree ID")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Monitor sport authorized degrees updated or inserted successfully",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *      )
     * )
     */
    public function updateMultiple(Request $request)
    {
        $dataToUpdate = $request->json()->all();

        if (empty($dataToUpdate)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        try {
            foreach ($dataToUpdate as $data) {
                MonitorSportAuthorizedDegree::updateOrInsert(
                    [
                        'monitor_sport_id' => $data['monitor_sport_id'],
                        'degree_id' => $data['degree_id'],
                    ]
                );
            }

            return response()->json(['message' => 'Monitor sport authorized degrees updated or inserted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating or inserting monitor sport authorized degrees'], 500);
        }
    }



    /**
     * @OA\Delete(
     *      path="/monitor-sport-authorized-degrees/{id}",
     *      summary="deleteMonitorSportAuthorizedDegree",
     *      tags={"MonitorSportAuthorizedDegree"},
     *      description="Delete MonitorSportAuthorizedDegree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of MonitorSportAuthorizedDegree",
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
        /** @var MonitorSportAuthorizedDegree $monitorSportAuthorizedDegree */
        $monitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepository->find($id);

        if (empty($monitorSportAuthorizedDegree)) {
            return $this->sendError('Monitor Sport Authorized Degree not found');
        }

        $monitorSportAuthorizedDegree->delete();

        return $this->sendSuccess('Monitor Sport Authorized Degree deleted successfully');
    }
}
