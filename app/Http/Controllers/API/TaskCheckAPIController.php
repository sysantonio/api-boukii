<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateTaskCheckAPIRequest;
use App\Http\Requests\API\UpdateTaskCheckAPIRequest;
use App\Http\Resources\API\TaskCheckResource;
use App\Models\TaskCheck;
use App\Repositories\TaskCheckRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TaskCheckController
 */

class TaskCheckAPIController extends AppBaseController
{
    /** @var  TaskCheckRepository */
    private $taskCheckRepository;

    public function __construct(TaskCheckRepository $taskCheckRepo)
    {
        $this->taskCheckRepository = $taskCheckRepo;
    }

    /**
     * @OA\Get(
     *      path="/task-checks",
     *      summary="getTaskCheckList",
     *      tags={"TaskCheck"},
     *      description="Get all TaskChecks",
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
     *                  @OA\Items(ref="#/components/schemas/TaskCheck")
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
        $taskChecks = $this->taskCheckRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(TaskCheckResource::collection($taskChecks), 'Task Checks retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/task-checks",
     *      summary="createTaskCheck",
     *      tags={"TaskCheck"},
     *      description="Create TaskCheck",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/TaskCheck")
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
     *                  ref="#/components/schemas/TaskCheck"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateTaskCheckAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $taskCheck = $this->taskCheckRepository->create($input);

        return $this->sendResponse(new TaskCheckResource($taskCheck), 'Task Check saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/task-checks/{id}",
     *      summary="getTaskCheckItem",
     *      tags={"TaskCheck"},
     *      description="Get TaskCheck",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TaskCheck",
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
     *                  ref="#/components/schemas/TaskCheck"
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
        /** @var TaskCheck $taskCheck */
        $taskCheck = $this->taskCheckRepository->find($id);

        if (empty($taskCheck)) {
            return $this->sendError('Task Check not found');
        }

        return $this->sendResponse(new TaskCheckResource($taskCheck), 'Task Check retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/task-checks/{id}",
     *      summary="updateTaskCheck",
     *      tags={"TaskCheck"},
     *      description="Update TaskCheck",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TaskCheck",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/TaskCheck")
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
     *                  ref="#/components/schemas/TaskCheck"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateTaskCheckAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var TaskCheck $taskCheck */
        $taskCheck = $this->taskCheckRepository->find($id);

        if (empty($taskCheck)) {
            return $this->sendError('Task Check not found');
        }

        $taskCheck = $this->taskCheckRepository->update($input, $id);

        return $this->sendResponse(new TaskCheckResource($taskCheck), 'TaskCheck updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/task-checks/{id}",
     *      summary="deleteTaskCheck",
     *      tags={"TaskCheck"},
     *      description="Delete TaskCheck",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of TaskCheck",
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
        /** @var TaskCheck $taskCheck */
        $taskCheck = $this->taskCheckRepository->find($id);

        if (empty($taskCheck)) {
            return $this->sendError('Task Check not found');
        }

        $taskCheck->delete();

        return $this->sendSuccess('Task Check deleted successfully');
    }
}
