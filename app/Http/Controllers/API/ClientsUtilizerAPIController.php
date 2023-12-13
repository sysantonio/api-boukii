<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateClientsUtilizerAPIRequest;
use App\Http\Requests\API\UpdateClientsUtilizerAPIRequest;
use App\Http\Resources\API\ClientsUtilizerResource;
use App\Models\ClientsUtilizer;
use App\Repositories\ClientsUtilizerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClientsUtilizerController
 */

class ClientsUtilizerAPIController extends AppBaseController
{
    /** @var  ClientsUtilizerRepository */
    private $clientsUtilizerRepository;

    public function __construct(ClientsUtilizerRepository $clientsUtilizerRepo)
    {
        $this->clientsUtilizerRepository = $clientsUtilizerRepo;
    }

    /**
     * @OA\Get(
     *      path="/clients-utilizers",
     *      summary="getClientsUtilizerList",
     *      tags={"ClientsUtilizer"},
     *      description="Get all ClientsUtilizers",
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
     *                  @OA\Items(ref="#/components/schemas/ClientsUtilizer")
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
        $clientsUtilizers = $this->clientsUtilizerRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($clientsUtilizers, 'Clients Utilizers retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/clients-utilizers",
     *      summary="createClientsUtilizer",
     *      tags={"ClientsUtilizer"},
     *      description="Create ClientsUtilizer",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientsUtilizer")
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
     *                  ref="#/components/schemas/ClientsUtilizer"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateClientsUtilizerAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $clientsUtilizer = $this->clientsUtilizerRepository->create($input);

        return $this->sendResponse($clientsUtilizer, 'Clients Utilizer saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/clients-utilizers/{id}",
     *      summary="getClientsUtilizerItem",
     *      tags={"ClientsUtilizer"},
     *      description="Get ClientsUtilizer",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsUtilizer",
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
     *                  ref="#/components/schemas/ClientsUtilizer"
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
        /** @var ClientsUtilizer $clientsUtilizer */
        $clientsUtilizer = $this->clientsUtilizerRepository->find($id, with: $request->get('with', []));

        if (empty($clientsUtilizer)) {
            return $this->sendError('Clients Utilizer not found');
        }

        return $this->sendResponse($clientsUtilizer, 'Clients Utilizer retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/clients-utilizers/{id}",
     *      summary="updateClientsUtilizer",
     *      tags={"ClientsUtilizer"},
     *      description="Update ClientsUtilizer",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsUtilizer",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientsUtilizer")
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
     *                  ref="#/components/schemas/ClientsUtilizer"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateClientsUtilizerAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var ClientsUtilizer $clientsUtilizer */
        $clientsUtilizer = $this->clientsUtilizerRepository->find($id, with: $request->get('with', []));

        if (empty($clientsUtilizer)) {
            return $this->sendError('Clients Utilizer not found');
        }

        $clientsUtilizer = $this->clientsUtilizerRepository->update($input, $id);

        return $this->sendResponse(new ClientsUtilizerResource($clientsUtilizer), 'ClientsUtilizer updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/clients-utilizers/{id}",
     *      summary="deleteClientsUtilizer",
     *      tags={"ClientsUtilizer"},
     *      description="Delete ClientsUtilizer",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsUtilizer",
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
        /** @var ClientsUtilizer $clientsUtilizer */
        $clientsUtilizer = $this->clientsUtilizerRepository->find($id);

        if (empty($clientsUtilizer)) {
            return $this->sendError('Clients Utilizer not found');
        }

        $clientsUtilizer->delete();

        return $this->sendSuccess('Clients Utilizer deleted successfully');
    }
}
