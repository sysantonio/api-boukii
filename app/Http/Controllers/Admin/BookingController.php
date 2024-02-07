<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\PayrexxHelpers;
use App\Http\Resources\API\BookingResource;
use App\Mail\BookingCancelMailer;
use App\Mail\BookingPayMailer;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\BookingUsers2;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Payrexx\Payrexx;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class BookingController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/admin/bookings",
     *      summary="getBookingList",
     *      tags={"Booking"},
     *      description="Get all Bookings",
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
     *                  @OA\Items(ref="#/components/schemas/Booking")
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
        $bookings = Booking::where('school_id', $request->school_id);

        return $this->sendResponse(BookingResource::collection($bookings), 'Bookings retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/admin/bookings/checkbooking",
     *      summary="checkOverlapBooking",
     *      tags={"Admin"},
     *      description="Check overlap booking for a client",
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
     *                  @OA\Items(ref="#/components/schemas/Client")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function checkClientBookingOverlap(Request $request): JsonResponse
    {
        $overlapBookingUsers = [];
        foreach ($request->bookingUsers as $bookingUser) {

            if (BookingUser::hasOverlappingBookings($bookingUser)) {
                $overlapBookingUsers[] = $bookingUser;
            }
        }

        if (count($overlapBookingUsers)) {
            return $this->sendResponse($overlapBookingUsers, 'Client has overlapping bookings', 404);
        }

        return $this->sendResponse([], 'Client has not overlaps bookings');
    }

    /**
     * @OA\Post(
     *      path="/admin/bookings/payments/{id}",
     *      summary="payBooking",
     *      tags={"Admin"},
     *      description="Pay specific booking",
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
     *                  @OA\Items(ref="#/components/schemas/Client")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function payBooking(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $booking = Booking::find($id);
        $paymentMethod = $request->get('payment_method_id') ?? $booking->payment_method_id;


        if (!$booking) {
            return $this->sendError('Booking not found', [], 404);
        }

        $booking->payment_method_id = $paymentMethod;
        $booking->save();


        if ($paymentMethod == 1) {
            return $this->sendError('Payment method not supported for this booking');
        }

        if ($paymentMethod == 2) {
            $payrexxLink = PayrexxHelpers::createGatewayLink(
                $school,
                $booking,
                $request,
                $booking->clientMain,
                'panel'
            );

            if ($payrexxLink) {
                return $this->sendResponse($payrexxLink, 'Link retrieved successfully');
            }

            return $this->sendError('Link could not be created');
        }

        if ($paymentMethod == 3) {

            $payrexxLink = PayrexxHelpers::createPayLink(
                $school,
                $booking,
                $request,
                $booking->clientMain
            );

            if (strlen($payrexxLink) > 1) {
                dispatch(function () use ($school, $booking, $payrexxLink) {
                    // Send by email
                    try {
                        $bookingData = $booking->fresh();   // To retrieve its generated PayrexxReference
                        \Mail::to($booking->clientMain->email)
                            ->send(new BookingPayMailer(
                                $school,
                                $bookingData,
                                $booking->clientMain,
                                $payrexxLink
                            ));
                    } catch (\Exception $e) {
                        Log::channel('payrexx')->error('PayrexxHelpers sendPayEmail Booking ID=' . $booking->id);
                        Log::channel('payrexx')->error($e->getMessage());
                    }

                })->afterResponse();

                return $this->sendResponse([], 'Mail sent correctly');

            }
            return $this->sendError('Link could not be created');

        }

        return $this->sendError('Invalid payment method');
    }

    /**
     * @OA\Post(
     *      path="/admin/bookings/refunds/{id}",
     *      summary="refundBooking",
     *      tags={"Admin"},
     *      description="Refund specific booking",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the booking to refund",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="amount",
     *          in="query",
     *          description="Amount to refund",
     *          required=true,
     *          @OA\Schema(type="number", format="float")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful refund",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  example={"refund": true}
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Refund completed successfully"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Booking not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="error",
     *                  type="array",
     *                  example={"message": "Booking not found"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="error",
     *                  type="array",
     *                  example={"message": "Invalid amount"}
     *              )
     *          )
     *      )
     * )
     */
    public function refundBooking(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $booking = Booking::with('payments')->find($id);
        $amountToRefund = $request->get('amount');

        if (!$booking) {
            return $this->sendError('Booking not found', 404);
        }

        if (!is_numeric($amountToRefund) || $amountToRefund <= 0) {
            return $this->sendError('Invalid amount', 400);
        }

        $refund = PayrexxHelpers::refundTransaction($booking, $amountToRefund);

        if ($refund) {
            return $this->sendResponse(['refund' => $refund], 'Refund completed successfully');
        }

        return $this->sendError('Refund failed', 500);
    }

    /**
     * @OA\Post(
     *      path="/admin/bookings/cancel",
     *      summary="cancelBooking",
     *      tags={"Admin"},
     *      description="Cancel specific booking or group of bookingIds",
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
     *                  @OA\Items(ref="#/components/schemas/Booking")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function cancelBookings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);


        $bookingUsers = BookingUser::whereIn('id', $request->bookingUsers)->get();
        $booking = $bookingUsers[0]->booking;

        if (!$bookingUsers) {
            return $this->sendError('Booking users not found', [], 404);
        }

        $booking->loadMissing(['bookingUsers', 'bookingUsers.client', 'bookingUsers.degree',
            'bookingUsers.monitor', 'bookingUsers.courseSubGroup', 'bookingUsers.course',
            'bookingUsers.courseDate', 'clientMain']);

/*        foreach ($bookingUsers as $bookingUser) {
            $bookingUser->status = 2;
            $bookingUser->save();
        }*/

        // Tell buyer user by email
        dispatch(function () use ($school, $booking, $bookingUsers) {
            $buyerUser = $booking->clientMain;

            // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
            try
            {
                \Mail::to($buyerUser->email)
                    ->send(new BookingCancelMailer(
                        $school,
                        $booking,
                        $bookingUsers,
                        $buyerUser,
                        null
                    ));
            }
            catch (\Exception $ex)
            {
                \Illuminate\Support\Facades\Log::debug('BookingController->cancelBookingFull BookingCancelMailer: ' . $ex->getMessage());
            }
        })->afterResponse();

        return $this->sendResponse([], 'Cancel completed successfully');

    }
}
