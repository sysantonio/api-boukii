<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateEvaluationFileAPIRequest;
use App\Http\Requests\API\UpdateEvaluationFileAPIRequest;
use App\Models\EvaluationFile;
use App\Repositories\EvaluationFileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\EvaluationFileResource;

/**
 * Class EvaluationFileController
 */

class EvaluationFileAPIController extends AppBaseController
{
    /** @var  EvaluationFileRepository */
    private $evaluationFileRepository;

    public function __construct(EvaluationFileRepository $evaluationFileRepo)
    {
        $this->evaluationFileRepository = $evaluationFileRepo;
    }

    /**
     * @OA\Get(
     *      path="/evaluation-files",
     *      summary="getEvaluationFileList",
     *      tags={"EvaluationFile"},
     *      description="Get all EvaluationFiles",
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
     *                  @OA\Items(ref="#/components/schemas/EvaluationFile")
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
        $evaluationFiles = $this->evaluationFileRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(EvaluationFileResource::collection($evaluationFiles), 'Evaluation Files retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/evaluation-files",
     *      summary="createEvaluationFile",
     *      tags={"EvaluationFile"},
     *      description="Create EvaluationFile",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/EvaluationFile")
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
     *                  ref="#/components/schemas/EvaluationFile"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateEvaluationFileAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $evaluationFile = $this->evaluationFileRepository->create($input);

        return $this->sendResponse(new EvaluationFileResource($evaluationFile), 'Evaluation File saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/evaluation-files/{id}",
     *      summary="getEvaluationFileItem",
     *      tags={"EvaluationFile"},
     *      description="Get EvaluationFile",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EvaluationFile",
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
     *                  ref="#/components/schemas/EvaluationFile"
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
        /** @var EvaluationFile $evaluationFile */
        $evaluationFile = $this->evaluationFileRepository->find($id);

        if (empty($evaluationFile)) {
            return $this->sendError('Evaluation File not found');
        }

        return $this->sendResponse(new EvaluationFileResource($evaluationFile), 'Evaluation File retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/evaluation-files/{id}",
     *      summary="updateEvaluationFile",
     *      tags={"EvaluationFile"},
     *      description="Update EvaluationFile",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EvaluationFile",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/EvaluationFile")
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
     *                  ref="#/components/schemas/EvaluationFile"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateEvaluationFileAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var EvaluationFile $evaluationFile */
        $evaluationFile = $this->evaluationFileRepository->find($id);

        if (empty($evaluationFile)) {
            return $this->sendError('Evaluation File not found');
        }

        $evaluationFile = $this->evaluationFileRepository->update($input, $id);

        return $this->sendResponse(new EvaluationFileResource($evaluationFile), 'EvaluationFile updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/evaluation-files/{id}",
     *      summary="deleteEvaluationFile",
     *      tags={"EvaluationFile"},
     *      description="Delete EvaluationFile",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EvaluationFile",
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
        /** @var EvaluationFile $evaluationFile */
        $evaluationFile = $this->evaluationFileRepository->find($id);

        if (empty($evaluationFile)) {
            return $this->sendError('Evaluation File not found');
        }

        $evaluationFile->delete();

        return $this->sendSuccess('Evaluation File deleted successfully');
    }
}
