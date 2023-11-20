<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateClientObservationAPIRequest;
use App\Http\Requests\API\UpdateClientObservationAPIRequest;
use App\Http\Resources\API\ClientObservationResource;
use App\Models\ClientObservation;
use App\Repositories\ClientObservationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClientObservationController
 */

class ClientObservationAPIController extends AppBaseController
{
    /** @var  ClientObservationRepository */
    private $clientObservationRepository;

    public function __construct(ClientObservationRepository $clientObservationRepo)
    {
        $this->clientObservationRepository = $clientObservationRepo;
    }

    /**
     * @OA\Get(
     *      path="/client-observations",
     *      summary="getClientObservationList",
     *      tags={"ClientObservation"},
     *      description="Get all ClientObservations",
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
     *                  @OA\Items(ref="#/components/schemas/ClientObservation")
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
        $clientObservations = $this->clientObservationRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(ClientObservationResource::collection($clientObservations), 'Client Observations retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/client-observations",
     *      summary="createClientObservation",
     *      tags={"ClientObservation"},
     *      description="Create ClientObservation",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientObservation")
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
     *                  ref="#/components/schemas/ClientObservation"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateClientObservationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $clientObservation = $this->clientObservationRepository->create($input);

        return $this->sendResponse(new ClientObservationResource($clientObservation), 'Client Observation saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/client-observations/{id}",
     *      summary="getClientObservationItem",
     *      tags={"ClientObservation"},
     *      description="Get ClientObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientObservation",
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
     *                  ref="#/components/schemas/ClientObservation"
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
        /** @var ClientObservation $clientObservation */
        $clientObservation = $this->clientObservationRepository->find($id);

        if (empty($clientObservation)) {
            return $this->sendError('Client Observation not found');
        }

        return $this->sendResponse(new ClientObservationResource($clientObservation), 'Client Observation retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/client-observations/{id}",
     *      summary="updateClientObservation",
     *      tags={"ClientObservation"},
     *      description="Update ClientObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientObservation",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientObservation")
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
     *                  ref="#/components/schemas/ClientObservation"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateClientObservationAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var ClientObservation $clientObservation */
        $clientObservation = $this->clientObservationRepository->find($id);

        if (empty($clientObservation)) {
            return $this->sendError('Client Observation not found');
        }

        $clientObservation = $this->clientObservationRepository->update($input, $id);

        return $this->sendResponse(new ClientObservationResource($clientObservation), 'ClientObservation updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/client-observations/{id}",
     *      summary="deleteClientObservation",
     *      tags={"ClientObservation"},
     *      description="Delete ClientObservation",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientObservation",
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
        /** @var ClientObservation $clientObservation */
        $clientObservation = $this->clientObservationRepository->find($id);

        if (empty($clientObservation)) {
            return $this->sendError('Client Observation not found');
        }

        $clientObservation->delete();

        return $this->sendSuccess('Client Observation deleted successfully');
    }
}
