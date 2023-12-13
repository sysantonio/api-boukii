<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class ClientController extends SlugAuthController
{


    /**
     * @OA\Get(
     *      path="/slug/client/{id}/voucher/{code}",
     *      summary="getVoucherForClient",
     *      tags={"BookingPage"},
     *      description="Search by code voucher for a client",
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
    public function getVoucherByCode($id, $code, Request $request): JsonResponse
    {

        $voucher =
            Voucher::where('school_id', $this->school->id)->where('client_id', $id)->where('code', $code)->first();

        if(!$voucher) {
            return $this->sendError( 'Voucher not found');
        }

        return $this->sendResponse($voucher, 'Voucher returned successfully');
    }

}
