<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class ClientsController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/admin/clients",
     *      summary="getClientList",
     *      tags={"Admin"},
     *      description="Get all Clients",
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
     *                  @OA\Items(ref="#/components/schemas/Booking")
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
        $clientsWithUtilizers = Client::has('clientsUtilizers')->with('utilizers')->paginate();

        return response()->json($clientsWithUtilizers);
    }

    /**
     * @OA\Get(
     *      path="/admin/clients/{id}",
     *      summary="getClientItem",
     *      tags={"Admin"},
     *      description="Get Client",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Client",
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
     *                  ref="#/components/schemas/Client"
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
        /** @var Client $client */
        $mainClient = Client::with('utilizers')->find($id);

        if (empty($mainClient)) {
            return $this->sendError('Client not found');
        }

        return $this->sendResponse($client, 'Client retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/admin/clients/{id}/utilizers",
     *      summary="getClientUtilizersList",
     *      tags={"Client"},
     *      description="Get all Clients",
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
     *                  @OA\Items(ref="#/components/schemas/Booking")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getUtilizers($id, Request $request): JsonResponse
    {
        $mainClient = Client::with('utilizers')->find($id);

        $utilizers = $mainClient->utilizers;

        return $this->sendResponse($utilizers, 'Utilizers returned successfully');
    }

}
