<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateVoucherAPIRequest;
use App\Http\Requests\API\UpdateVoucherAPIRequest;
use App\Http\Resources\API\VoucherResource;
use App\Models\Voucher;
use App\Repositories\VoucherRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class VoucherController
 */

class VoucherAPIController extends AppBaseController
{
    /** @var  VoucherRepository */
    private $voucherRepository;

    public function __construct(VoucherRepository $voucherRepo)
    {
        $this->voucherRepository = $voucherRepo;
    }

    /**
     * @OA\Get(
     *      path="/vouchers",
     *      summary="getVoucherList",
     *      tags={"Voucher"},
     *      description="Get all Vouchers",
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
     *                  @OA\Items(ref="#/components/schemas/Voucher")
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
        $vouchers = $this->voucherRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id'),
            null,
            $request->get('onlyTrashed', false)
        );

        return $this->sendResponse($vouchers, 'Vouchers retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/vouchers-trashed",
     *      summary="getVoucherListTrashed",
     *      tags={"Voucher"},
     *      description="Get all Vouchers Trashed",
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
     *                  @OA\Items(ref="#/components/schemas/Voucher")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function indexWithTrashed(Request $request): JsonResponse
    {
        $vouchers = $this->voucherRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id'),
            null,
            $request->get('onlyTrashed', false)
        );

        return $this->sendResponse($vouchers, 'Vouchers retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/vouchers",
     *      summary="createVoucher",
     *      tags={"Voucher"},
     *      description="Create Voucher",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Voucher")
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
     *                  ref="#/components/schemas/Voucher"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateVoucherAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $voucher = $this->voucherRepository->create($input);

        return $this->sendResponse($voucher, 'Voucher saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/vouchers/{id}",
     *      summary="getVoucherItem",
     *      tags={"Voucher"},
     *      description="Get Voucher",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Voucher",
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
     *                  ref="#/components/schemas/Voucher"
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
        /** @var Voucher $voucher */
        $voucher = $this->voucherRepository->find($id, with: $request->get('with', []));

        if (empty($voucher)) {
            return $this->sendError('Voucher not found');
        }

        return $this->sendResponse($voucher, 'Voucher retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/vouchers/{id}",
     *      summary="updateVoucher",
     *      tags={"Voucher"},
     *      description="Update Voucher",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Voucher",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Voucher")
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
     *                  ref="#/components/schemas/Voucher"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateVoucherAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Voucher $voucher */
        $voucher = $this->voucherRepository->find($id, with: $request->get('with', []));

        if (empty($voucher)) {
            return $this->sendError('Voucher not found');
        }

        $voucher = $this->voucherRepository->update($input, $id);

        return $this->sendResponse(new VoucherResource($voucher), 'Voucher updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/vouchers/{id}",
     *      summary="deleteVoucher",
     *      tags={"Voucher"},
     *      description="Delete Voucher",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Voucher",
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
        /** @var Voucher $voucher */
        $voucher = $this->voucherRepository->find($id);

        if (empty($voucher)) {
            return $this->sendError('Voucher not found');
        }

        $voucher->delete();

        return $this->sendSuccess('Voucher deleted successfully');
    }
}
