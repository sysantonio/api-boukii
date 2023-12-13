<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSportTypeAPIRequest;
use App\Http\Requests\API\UpdateSportTypeAPIRequest;
use App\Http\Resources\API\SportTypeResource;
use App\Models\SportType;
use App\Repositories\SportTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SportTypeController
 */

class SportTypeAPIController extends AppBaseController
{
    /** @var  SportTypeRepository */
    private $sportTypeRepository;

    public function __construct(SportTypeRepository $sportTypeRepo)
    {
        $this->sportTypeRepository = $sportTypeRepo;
    }

    /**
     * @OA\Get(
     *      path="/sport-types",
     *      summary="getSportTypeList",
     *      tags={"SportType"},
     *      description="Get all SportTypes",
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
     *                  @OA\Items(ref="#/components/schemas/SportType")
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
        $sportTypes = $this->sportTypeRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($sportTypes, 'Sport Types retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/sport-types",
     *      summary="createSportType",
     *      tags={"SportType"},
     *      description="Create SportType",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SportType")
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
     *                  ref="#/components/schemas/SportType"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSportTypeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $sportType = $this->sportTypeRepository->create($input);

        return $this->sendResponse($sportType, 'Sport Type saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/sport-types/{id}",
     *      summary="getSportTypeItem",
     *      tags={"SportType"},
     *      description="Get SportType",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SportType",
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
     *                  ref="#/components/schemas/SportType"
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
        /** @var SportType $sportType */
        $sportType = $this->sportTypeRepository->find($id, with: $request->get('with', []));

        if (empty($sportType)) {
            return $this->sendError('Sport Type not found');
        }

        return $this->sendResponse($sportType, 'Sport Type retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/sport-types/{id}",
     *      summary="updateSportType",
     *      tags={"SportType"},
     *      description="Update SportType",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SportType",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SportType")
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
     *                  ref="#/components/schemas/SportType"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSportTypeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var SportType $sportType */
        $sportType = $this->sportTypeRepository->find($id, with: $request->get('with', []));

        if (empty($sportType)) {
            return $this->sendError('Sport Type not found');
        }

        $sportType = $this->sportTypeRepository->update($input, $id);

        return $this->sendResponse(new SportTypeResource($sportType), 'SportType updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/sport-types/{id}",
     *      summary="deleteSportType",
     *      tags={"SportType"},
     *      description="Delete SportType",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SportType",
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
        /** @var SportType $sportType */
        $sportType = $this->sportTypeRepository->find($id, with: $request->get('with', []));

        if (empty($sportType)) {
            return $this->sendError('Sport Type not found');
        }

        $sportType->delete();

        return $this->sendSuccess('Sport Type deleted successfully');
    }
}
