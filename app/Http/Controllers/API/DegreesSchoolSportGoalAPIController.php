<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateDegreesSchoolSportGoalAPIRequest;
use App\Http\Requests\API\UpdateDegreesSchoolSportGoalAPIRequest;
use App\Http\Resources\API\DegreesSchoolSportGoalResource;
use App\Models\DegreesSchoolSportGoal;
use App\Repositories\DegreesSchoolSportGoalRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DegreesSchoolSportGoalController
 */

class DegreesSchoolSportGoalAPIController extends AppBaseController
{
    /** @var  DegreesSchoolSportGoalRepository */
    private $degreesSchoolSportGoalRepository;

    public function __construct(DegreesSchoolSportGoalRepository $degreesSchoolSportGoalRepo)
    {
        $this->degreesSchoolSportGoalRepository = $degreesSchoolSportGoalRepo;
    }

    /**
     * @OA\Get(
     *      path="/degrees-school-sport-goals",
     *      summary="getDegreesSchoolSportGoalList",
     *      tags={"DegreesSchoolSportGoal"},
     *      description="Get all DegreesSchoolSportGoals",
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
     *                  @OA\Items(ref="#/components/schemas/DegreesSchoolSportGoal")
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
        $degreesSchoolSportGoals = $this->degreesSchoolSportGoalRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($degreesSchoolSportGoals, 'Degrees School Sport Goals retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/degrees-school-sport-goals",
     *      summary="createDegreesSchoolSportGoal",
     *      tags={"DegreesSchoolSportGoal"},
     *      description="Create DegreesSchoolSportGoal",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/DegreesSchoolSportGoal")
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
     *                  ref="#/components/schemas/DegreesSchoolSportGoal"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateDegreesSchoolSportGoalAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $degreesSchoolSportGoal = $this->degreesSchoolSportGoalRepository->create($input);

        return $this->sendResponse($degreesSchoolSportGoal, 'Degrees School Sport Goal saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/degrees-school-sport-goals/{id}",
     *      summary="getDegreesSchoolSportGoalItem",
     *      tags={"DegreesSchoolSportGoal"},
     *      description="Get DegreesSchoolSportGoal",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DegreesSchoolSportGoal",
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
     *                  ref="#/components/schemas/DegreesSchoolSportGoal"
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
        /** @var DegreesSchoolSportGoal $degreesSchoolSportGoal */
        $degreesSchoolSportGoal = $this->degreesSchoolSportGoalRepository->find($id, with: $request->get('with', []));

        if (empty($degreesSchoolSportGoal)) {
            return $this->sendError('Degrees School Sport Goal not found');
        }

        return $this->sendResponse($degreesSchoolSportGoal, 'Degrees School Sport Goal retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/degrees-school-sport-goals/{id}",
     *      summary="updateDegreesSchoolSportGoal",
     *      tags={"DegreesSchoolSportGoal"},
     *      description="Update DegreesSchoolSportGoal",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DegreesSchoolSportGoal",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/DegreesSchoolSportGoal")
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
     *                  ref="#/components/schemas/DegreesSchoolSportGoal"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateDegreesSchoolSportGoalAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var DegreesSchoolSportGoal $degreesSchoolSportGoal */
        $degreesSchoolSportGoal = $this->degreesSchoolSportGoalRepository->find($id, with: $request->get('with', []));

        if (empty($degreesSchoolSportGoal)) {
            return $this->sendError('Degrees School Sport Goal not found');
        }

        $degreesSchoolSportGoal = $this->degreesSchoolSportGoalRepository->update($input, $id);

        return $this->sendResponse(new DegreesSchoolSportGoalResource($degreesSchoolSportGoal), 'DegreesSchoolSportGoal updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/degrees-school-sport-goals/{id}",
     *      summary="deleteDegreesSchoolSportGoal",
     *      tags={"DegreesSchoolSportGoal"},
     *      description="Delete DegreesSchoolSportGoal",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DegreesSchoolSportGoal",
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
        /** @var DegreesSchoolSportGoal $degreesSchoolSportGoal */
        $degreesSchoolSportGoal = $this->degreesSchoolSportGoalRepository->find($id);

        if (empty($degreesSchoolSportGoal)) {
            return $this->sendError('Degrees School Sport Goal not found');
        }

        $degreesSchoolSportGoal->delete();

        return $this->sendSuccess('Degrees School Sport Goal deleted successfully');
    }
}
