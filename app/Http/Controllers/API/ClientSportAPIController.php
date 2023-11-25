<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateClientSportAPIRequest;
use App\Http\Requests\API\UpdateClientSportAPIRequest;
use App\Models\ClientSport;
use App\Repositories\ClientSportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\ClientSportResource;

/**
 * Class ClientSportController
 */

class ClientSportAPIController extends AppBaseController
{
    /** @var  ClientSportRepository */
    private $clientSportRepository;

    public function __construct(ClientSportRepository $clientSportRepo)
    {
        $this->clientSportRepository = $clientSportRepo;
    }

    /**
     * @OA\Get(
     *      path="/client-sports",
     *      summary="getClientSportList",
     *      tags={"ClientSport"},
     *      description="Get all ClientSports",
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
     *                  @OA\Items(ref="#/components/schemas/ClientSport")
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
        $clientSports = $this->clientSportRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(ClientSportResource::collection($clientSports), 'Client Sports retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/client-sports",
     *      summary="createClientSport",
     *      tags={"ClientSport"},
     *      description="Create ClientSport",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientSport")
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
     *                  ref="#/components/schemas/ClientSport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateClientSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $clientSport = $this->clientSportRepository->create($input);

        return $this->sendResponse(new ClientSportResource($clientSport), 'Client Sport saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/client-sports/{id}",
     *      summary="getClientSportItem",
     *      tags={"ClientSport"},
     *      description="Get ClientSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientSport",
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
     *                  ref="#/components/schemas/ClientSport"
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
        /** @var ClientSport $clientSport */
        $clientSport = $this->clientSportRepository->find($id);

        if (empty($clientSport)) {
            return $this->sendError('Client Sport not found');
        }

        return $this->sendResponse(new ClientSportResource($clientSport), 'Client Sport retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/client-sports/{id}",
     *      summary="updateClientSport",
     *      tags={"ClientSport"},
     *      description="Update ClientSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientSport",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientSport")
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
     *                  ref="#/components/schemas/ClientSport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateClientSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var ClientSport $clientSport */
        $clientSport = $this->clientSportRepository->find($id);

        if (empty($clientSport)) {
            return $this->sendError('Client Sport not found');
        }

        $clientSport = $this->clientSportRepository->update($input, $id);

        return $this->sendResponse(new ClientSportResource($clientSport), 'ClientSport updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/client-sports/{id}",
     *      summary="deleteClientSport",
     *      tags={"ClientSport"},
     *      description="Delete ClientSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientSport",
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
        /** @var ClientSport $clientSport */
        $clientSport = $this->clientSportRepository->find($id);

        if (empty($clientSport)) {
            return $this->sendError('Client Sport not found');
        }

        $clientSport->delete();

        return $this->sendSuccess('Client Sport deleted successfully');
    }
}
