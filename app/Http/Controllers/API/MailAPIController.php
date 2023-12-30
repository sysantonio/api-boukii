<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateMailAPIRequest;
use App\Http\Requests\API\UpdateMailAPIRequest;
use App\Models\Mail;
use App\Repositories\MailRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\MailResource;

/**
 * Class MailController
 */

class MailAPIController extends AppBaseController
{
    /** @var  MailRepository */
    private $mailRepository;

    public function __construct(MailRepository $mailRepo)
    {
        $this->mailRepository = $mailRepo;
    }

    /**
     * @OA\Get(
     *      path="/mails",
     *      summary="getMailList",
     *      tags={"Mail"},
     *      description="Get all Mails",
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
     *                  @OA\Items(ref="#/components/schemas/Mail")
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
        $mails = $this->mailRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($mails, 'Mails retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/mails",
     *      summary="createMail",
     *      tags={"Mail"},
     *      description="Create Mail",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Mail")
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
     *                  ref="#/components/schemas/Mail"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMailAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $mail = $this->mailRepository->create($input);

        return $this->sendResponse($mail, 'Mail saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/mails/{id}",
     *      summary="getMailItem",
     *      tags={"Mail"},
     *      description="Get Mail",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Mail",
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
     *                  ref="#/components/schemas/Mail"
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
        /** @var Mail $mail */
        $mail = $this->mailRepository->find($id,  with: $request->get('with', []));

        if (empty($mail)) {
            return $this->sendError('Mail not found');
        }

        return $this->sendResponse($mail, 'Mail retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/mails/{id}",
     *      summary="updateMail",
     *      tags={"Mail"},
     *      description="Update Mail",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Mail",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Mail")
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
     *                  ref="#/components/schemas/Mail"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMailAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Mail $mail */
        $mail = $this->mailRepository->find($id);

        if (empty($mail)) {
            return $this->sendError('Mail not found');
        }

        $mail = $this->mailRepository->update($input, $id);

        return $this->sendResponse($mail, 'Mail updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/mails/{id}",
     *      summary="deleteMail",
     *      tags={"Mail"},
     *      description="Delete Mail",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Mail",
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
        /** @var Mail $mail */
        $mail = $this->mailRepository->find($id);

        if (empty($mail)) {
            return $this->sendError('Mail not found');
        }

        $mail->delete();

        return $this->sendSuccess('Mail deleted successfully');
    }
}
