<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateEmailLogAPIRequest;
use App\Http\Requests\API\UpdateEmailLogAPIRequest;
use App\Http\Resources\API\EmailLogResource;
use App\Models\EmailLog;
use App\Repositories\EmailLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class EmailLogController
 */

class EmailLogAPIController extends AppBaseController
{
    /** @var  EmailLogRepository */
    private $emailLogRepository;

    public function __construct(EmailLogRepository $emailLogRepo)
    {
        $this->emailLogRepository = $emailLogRepo;
    }

    /**
     * @OA\Get(
     *      path="/email-logs",
     *      summary="getEmailLogList",
     *      tags={"EmailLog"},
     *      description="Get all EmailLogs",
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
     *                  @OA\Items(ref="#/components/schemas/EmailLog")
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
        $emailLogs = $this->emailLogRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($emailLogs, 'Email Logs retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/email-logs",
     *      summary="createEmailLog",
     *      tags={"EmailLog"},
     *      description="Create EmailLog",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/EmailLog")
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
     *                  ref="#/components/schemas/EmailLog"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateEmailLogAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $emailLog = $this->emailLogRepository->create($input);

        return $this->sendResponse($emailLog, 'Email Log saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/email-logs/{id}",
     *      summary="getEmailLogItem",
     *      tags={"EmailLog"},
     *      description="Get EmailLog",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EmailLog",
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
     *                  ref="#/components/schemas/EmailLog"
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
        /** @var EmailLog $emailLog */
        $emailLog = $this->emailLogRepository->find($id, with: $request->get('with', []));

        if (empty($emailLog)) {
            return $this->sendError('Email Log not found');
        }

        return $this->sendResponse($emailLog, 'Email Log retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/email-logs/{id}",
     *      summary="updateEmailLog",
     *      tags={"EmailLog"},
     *      description="Update EmailLog",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EmailLog",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/EmailLog")
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
     *                  ref="#/components/schemas/EmailLog"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateEmailLogAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var EmailLog $emailLog */
        $emailLog = $this->emailLogRepository->find($id, with: $request->get('with', []));

        if (empty($emailLog)) {
            return $this->sendError('Email Log not found');
        }

        $emailLog = $this->emailLogRepository->update($input, $id);

        return $this->sendResponse(new EmailLogResource($emailLog), 'EmailLog updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/email-logs/{id}",
     *      summary="deleteEmailLog",
     *      tags={"EmailLog"},
     *      description="Delete EmailLog",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of EmailLog",
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
        /** @var EmailLog $emailLog */
        $emailLog = $this->emailLogRepository->find($id);

        if (empty($emailLog)) {
            return $this->sendError('Email Log not found');
        }

        $emailLog->delete();

        return $this->sendSuccess('Email Log deleted successfully');
    }
}
