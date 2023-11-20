<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateDegreeAPIRequest;
use App\Http\Requests\API\UpdateDegreeAPIRequest;
use App\Http\Resources\API\DegreeResource;
use App\Models\Degree;
use App\Repositories\DegreeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DegreeController
 */

class DegreeAPIController extends AppBaseController
{
    /** @var  DegreeRepository */
    private $degreeRepository;

    public function __construct(DegreeRepository $degreeRepo)
    {
        $this->degreeRepository = $degreeRepo;
    }

    /**
     * @OA\Get(
     *      path="/degrees",
     *      summary="getDegreeList",
     *      tags={"Degree"},
     *      description="Get all Degrees",
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
     *                  @OA\Items(ref="#/components/schemas/Degree")
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
        $degrees = $this->degreeRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(DegreeResource::collection($degrees), 'Degrees retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/degrees",
     *      summary="createDegree",
     *      tags={"Degree"},
     *      description="Create Degree",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Degree")
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
     *                  ref="#/components/schemas/Degree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $degree = $this->degreeRepository->create($input);

        return $this->sendResponse(new DegreeResource($degree), 'Degree saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/degrees/{id}",
     *      summary="getDegreeItem",
     *      tags={"Degree"},
     *      description="Get Degree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Degree",
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
     *                  ref="#/components/schemas/Degree"
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
        /** @var Degree $degree */
        $degree = $this->degreeRepository->find($id);

        if (empty($degree)) {
            return $this->sendError('Degree not found');
        }

        return $this->sendResponse(new DegreeResource($degree), 'Degree retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/degrees/{id}",
     *      summary="updateDegree",
     *      tags={"Degree"},
     *      description="Update Degree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Degree",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Degree")
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
     *                  ref="#/components/schemas/Degree"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateDegreeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Degree $degree */
        $degree = $this->degreeRepository->find($id);

        if (empty($degree)) {
            return $this->sendError('Degree not found');
        }

        $degree = $this->degreeRepository->update($input, $id);

        return $this->sendResponse(new DegreeResource($degree), 'Degree updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/degrees/{id}",
     *      summary="deleteDegree",
     *      tags={"Degree"},
     *      description="Delete Degree",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Degree",
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
        /** @var Degree $degree */
        $degree = $this->degreeRepository->find($id);

        if (empty($degree)) {
            return $this->sendError('Degree not found');
        }

        $degree->delete();

        return $this->sendSuccess('Degree deleted successfully');
    }
}
