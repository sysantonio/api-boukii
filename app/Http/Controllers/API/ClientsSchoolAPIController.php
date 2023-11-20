<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateClientsSchoolAPIRequest;
use App\Http\Requests\API\UpdateClientsSchoolAPIRequest;
use App\Http\Resources\API\ClientsSchoolResource;
use App\Models\ClientsSchool;
use App\Repositories\ClientsSchoolRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClientsSchoolController
 */

class ClientsSchoolAPIController extends AppBaseController
{
    /** @var  ClientsSchoolRepository */
    private $clientsSchoolRepository;

    public function __construct(ClientsSchoolRepository $clientsSchoolRepo)
    {
        $this->clientsSchoolRepository = $clientsSchoolRepo;
    }

    /**
     * @OA\Get(
     *      path="/clients-schools",
     *      summary="getClientsSchoolList",
     *      tags={"ClientsSchool"},
     *      description="Get all ClientsSchools",
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
     *                  @OA\Items(ref="#/components/schemas/ClientsSchool")
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
        $clientsSchools = $this->clientsSchoolRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(ClientsSchoolResource::collection($clientsSchools), 'Clients Schools retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/clients-schools",
     *      summary="createClientsSchool",
     *      tags={"ClientsSchool"},
     *      description="Create ClientsSchool",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientsSchool")
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
     *                  ref="#/components/schemas/ClientsSchool"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateClientsSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $clientsSchool = $this->clientsSchoolRepository->create($input);

        return $this->sendResponse(new ClientsSchoolResource($clientsSchool), 'Clients School saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/clients-schools/{id}",
     *      summary="getClientsSchoolItem",
     *      tags={"ClientsSchool"},
     *      description="Get ClientsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsSchool",
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
     *                  ref="#/components/schemas/ClientsSchool"
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
        /** @var ClientsSchool $clientsSchool */
        $clientsSchool = $this->clientsSchoolRepository->find($id);

        if (empty($clientsSchool)) {
            return $this->sendError('Clients School not found');
        }

        return $this->sendResponse(new ClientsSchoolResource($clientsSchool), 'Clients School retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/clients-schools/{id}",
     *      summary="updateClientsSchool",
     *      tags={"ClientsSchool"},
     *      description="Update ClientsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsSchool",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/ClientsSchool")
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
     *                  ref="#/components/schemas/ClientsSchool"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateClientsSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var ClientsSchool $clientsSchool */
        $clientsSchool = $this->clientsSchoolRepository->find($id);

        if (empty($clientsSchool)) {
            return $this->sendError('Clients School not found');
        }

        $clientsSchool = $this->clientsSchoolRepository->update($input, $id);

        return $this->sendResponse(new ClientsSchoolResource($clientsSchool), 'ClientsSchool updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/clients-schools/{id}",
     *      summary="deleteClientsSchool",
     *      tags={"ClientsSchool"},
     *      description="Delete ClientsSchool",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of ClientsSchool",
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
        /** @var ClientsSchool $clientsSchool */
        $clientsSchool = $this->clientsSchoolRepository->find($id);

        if (empty($clientsSchool)) {
            return $this->sendError('Clients School not found');
        }

        $clientsSchool->delete();

        return $this->sendSuccess('Clients School deleted successfully');
    }
}
