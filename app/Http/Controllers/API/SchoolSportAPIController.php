<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSchoolSportAPIRequest;
use App\Http\Requests\API\UpdateSchoolSportAPIRequest;
use App\Http\Resources\API\SchoolSportResource;
use App\Models\SchoolSport;
use App\Repositories\SchoolSportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SchoolSportController
 */

class SchoolSportAPIController extends AppBaseController
{
    /** @var  SchoolSportRepository */
    private $schoolSportRepository;

    public function __construct(SchoolSportRepository $schoolSportRepo)
    {
        $this->schoolSportRepository = $schoolSportRepo;
    }

    /**
     * @OA\Get(
     *      path="/school-sports",
     *      summary="getSchoolSportList",
     *      tags={"SchoolSport"},
     *      description="Get all SchoolSports",
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
     *                  @OA\Items(ref="#/components/schemas/SchoolSport")
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
        $schoolSports = $this->schoolSportRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(SchoolSportResource::collection($schoolSports), 'School Sports retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/school-sports",
     *      summary="createSchoolSport",
     *      tags={"SchoolSport"},
     *      description="Create SchoolSport",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolSport")
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
     *                  ref="#/components/schemas/SchoolSport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSchoolSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $schoolSport = $this->schoolSportRepository->create($input);

        return $this->sendResponse(new SchoolSportResource($schoolSport), 'School Sport saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/school-sports/{id}",
     *      summary="getSchoolSportItem",
     *      tags={"SchoolSport"},
     *      description="Get SchoolSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSport",
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
     *                  ref="#/components/schemas/SchoolSport"
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
        /** @var SchoolSport $schoolSport */
        $schoolSport = $this->schoolSportRepository->find($id);

        if (empty($schoolSport)) {
            return $this->sendError('School Sport not found');
        }

        return $this->sendResponse(new SchoolSportResource($schoolSport), 'School Sport retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/school-sports/{id}",
     *      summary="updateSchoolSport",
     *      tags={"SchoolSport"},
     *      description="Update SchoolSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSport",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolSport")
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
     *                  ref="#/components/schemas/SchoolSport"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSchoolSportAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var SchoolSport $schoolSport */
        $schoolSport = $this->schoolSportRepository->find($id);

        if (empty($schoolSport)) {
            return $this->sendError('School Sport not found');
        }

        $schoolSport = $this->schoolSportRepository->update($input, $id);

        return $this->sendResponse(new SchoolSportResource($schoolSport), 'SchoolSport updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/school-sports/{id}",
     *      summary="deleteSchoolSport",
     *      tags={"SchoolSport"},
     *      description="Delete SchoolSport",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolSport",
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
        /** @var SchoolSport $schoolSport */
        $schoolSport = $this->schoolSportRepository->find($id);

        if (empty($schoolSport)) {
            return $this->sendError('School Sport not found');
        }

        $schoolSport->delete();

        return $this->sendSuccess('School Sport deleted successfully');
    }
}
