<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateDiscountCodeAPIRequest;
use App\Http\Requests\API\UpdateDiscountCodeAPIRequest;
use App\Models\DiscountCode;
use App\Repositories\DiscountCodeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\DiscountCodeResource;

/**
 * Class DiscountCodeController
 */

class DiscountCodeAPIController extends AppBaseController
{
    /** @var  DiscountCodeRepository */
    private $discountCodeRepository;

    public function __construct(DiscountCodeRepository $discountCodeRepo)
    {
        $this->discountCodeRepository = $discountCodeRepo;
    }

    /**
     * @OA\Get(
     *      path="/discount-codes",
     *      summary="getDiscountCodeList",
     *      tags={"DiscountCode"},
     *      description="Get all DiscountCodes",
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
     *                  @OA\Items(ref="#/components/schemas/DiscountCode")
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
        $discountCodes = $this->discountCodeRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($discountCodes, 'Discount Codes retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/discount-codes",
     *      summary="createDiscountCode",
     *      tags={"DiscountCode"},
     *      description="Create DiscountCode",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/DiscountCode")
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
     *                  ref="#/components/schemas/DiscountCode"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateDiscountCodeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $discountCode = $this->discountCodeRepository->create($input);

        return $this->sendResponse($discountCode, 'Discount Code saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/discount-codes/{id}",
     *      summary="getDiscountCodeItem",
     *      tags={"DiscountCode"},
     *      description="Get DiscountCode",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DiscountCode",
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
     *                  ref="#/components/schemas/DiscountCode"
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
        /** @var DiscountCode $discountCode */
        $discountCode = $this->discountCodeRepository->find($id,  with: $request->get('with', []));

        if (empty($discountCode)) {
            return $this->sendError('Discount Code not found');
        }

        return $this->sendResponse($discountCode, 'Discount Code retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/discount-codes/{id}",
     *      summary="updateDiscountCode",
     *      tags={"DiscountCode"},
     *      description="Update DiscountCode",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DiscountCode",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/DiscountCode")
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
     *                  ref="#/components/schemas/DiscountCode"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateDiscountCodeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var DiscountCode $discountCode */
        $discountCode = $this->discountCodeRepository->find($id);

        if (empty($discountCode)) {
            return $this->sendError('Discount Code not found');
        }

        $discountCode = $this->discountCodeRepository->update($input, $id);

        return $this->sendResponse(new DiscountCodeResource($discountCode), 'DiscountCode updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/discount-codes/{id}",
     *      summary="deleteDiscountCode",
     *      tags={"DiscountCode"},
     *      description="Delete DiscountCode",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of DiscountCode",
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
        /** @var DiscountCode $discountCode */
        $discountCode = $this->discountCodeRepository->find($id);

        if (empty($discountCode)) {
            return $this->sendError('Discount Code not found');
        }

        $discountCode->delete();

        return $this->sendSuccess('Discount Code deleted successfully');
    }
}
