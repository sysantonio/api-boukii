<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSportAPIRequest;
use App\Http\Requests\API\UpdateSportAPIRequest;
use App\Http\Resources\API\SportResource;
use App\Models\Sport;
use App\Repositories\SportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SportController
 */

class SportAPIController extends AppBaseController
{
    /** @var  SportRepository */
    private $sportRepository;

    public function __construct(SportRepository $sportRepo)
    {
        $this->sportRepository = $sportRepo;
    }

    /**
     * @OA\Get(
     *      path="/sports",
     *      summary="getSportList",
     *      tags={"Sport"},
     *      description="Get all Sports",
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
     *                  @OA\Items(ref="#/components/schemas/Sport")
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
        $sports = $this->sportRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(SportResource::collection($sports), 'Sports retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/sports",
     *      summary="createSport",
     *      tags={"Sport"},
     *      description="Create Sport",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Sport")
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
     *                  ref="#/components/schemas/Sport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $sport = $this->sportRepository->create($input);

        return $this->sendResponse(new SportResource($sport), 'Sport saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/sports/{id}",
     *      summary="getSportItem",
     *      tags={"Sport"},
     *      description="Get Sport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Sport",
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
     *                  ref="#/components/schemas/Sport"
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
        /** @var Sport $sport */
        $sport = $this->sportRepository->find($id);

        if (empty($sport)) {
            return $this->sendError('Sport not found');
        }

        return $this->sendResponse(new SportResource($sport), 'Sport retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/sports/{id}",
     *      summary="updateSport",
     *      tags={"Sport"},
     *      description="Update Sport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Sport",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Sport")
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
     *                  ref="#/components/schemas/Sport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Sport $sport */
        $sport = $this->sportRepository->find($id);

        if (empty($sport)) {
            return $this->sendError('Sport not found');
        }

        $sport = $this->sportRepository->update($input, $id);

        return $this->sendResponse(new SportResource($sport), 'Sport updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/sports/{id}",
     *      summary="deleteSport",
     *      tags={"Sport"},
     *      description="Delete Sport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Sport",
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
        /** @var Sport $sport */
        $sport = $this->sportRepository->find($id);

        if (empty($sport)) {
            return $this->sendError('Sport not found');
        }

        $sport->delete();

        return $this->sendSuccess('Sport deleted successfully');
    }
}
