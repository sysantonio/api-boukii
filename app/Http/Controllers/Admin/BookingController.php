<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\PayrexxHelpers;
use App\Http\Resources\API\BookingResource;
use App\Mail\BookingCancelMailer;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\BookingUsers2;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::info('Payment method request:' . $request->get('payment_method_id'));
        $paymentMethod = $request->get('payment_method_id') ?? $booking->payment_method_id;


        if (!$booking) {
            return $this->sendError('Booking not found', [], 404);
        }

        $booking->payment_method_id = $paymentMethod;
        $booking->save();

        Log::info('Payment method post request:' . $paymentMethod);

        if ($paymentMethod == 1) {
            return $this->sendError('Payment method not supported for this booking');
        }

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
            dispatch(function () use ($school, $booking, $request) {
                PayrexxHelpers::sendPayEmail(
                    $school,
                    $booking,
                    $request,
                    $booking->clientMain
                );
            })->afterResponse();

            return $this->sendResponse([], 'Mail sent correctly');
        }

        return $this->sendError('Invalid payment method');
    }

    /**
     * @OA\Post(
     *      path="/admin/bookings/cancel",
     *      summary="payBooking",
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
        $amountToRefund = $request->amount;
        $refundAll = false;
        if($request->has('bookingId')) {
            $refundAll = true;
            $booking = Booking::find($request->bookingId);
            if (!$booking) {
                return $this->sendError('Booking not found', [], 404);
            }
            $amountToRefund = $booking->price_total;
            $bookingUsers = $booking->bookingUsers;
        } else {
            $bookingUsers = BookingUser::whereIn($request->bookingUsers)->get();
            $booking = $bookingUsers[0]->booking;
        }


        if (!$bookingUsers) {
            return $this->sendError('Booking users not found', [], 404);
        }

        if ($booking->paid && $booking->payrexx_reference && $amountToRefund > 0)
        {
            // 2023-01-04 Boukki REQUEST FOR NO MONEY BACK
            if (!PayrexxHelpers::refundTransaction($booking, $amountToRefund))
            {
                // CAN'T require a valid Payrexx response, because maybe there's no money available, ex. it was previously refunded from their web
                return $this->sendError('Payrexx refund error');
            }

        }

        $voucherData = new Voucher();
        if($booking->paid && $booking->has_cancellation_insurance == 0)
        {

            $amountVoucher = $amountToRefund;
            if($amountVoucher > 0)
            {
                $code = "BOU".str_pad($booking->id, 6, "0", STR_PAD_LEFT)
                    .rand(0,9).date("y").date("m").date("d").rand(0,9);

                $newVoucher = Voucher::create([
                    'code' => $code,
                    'quantity' => $amountVoucher,
                    'remaining_balance' => $amountVoucher,
                    'payed' => 1,
                    'client_id' => $booking->client_main_id,
                    'school_id' => $school->id
                ]);

                $voucherData = $newVoucher;
            }
        }

        $booking->status = $refundAll ? 3 : 2;
        $booking->save();

        $booking->loadMissing(['bookingUsers', 'bookingUsers.client', 'bookingUsers.degree', 'bookingUsers.monitor',
            'bookingUsers.courseSubGroup', 'bookingUsers.course', 'bookingUsers.courseDate']);

        foreach ($bookingUsers as $bookingUser) {
            $bookingUser->status = 2;
            $bookingUser->save();
        }

        // Tell buyer user by email
        dispatch(function () use ($school, $booking, $bookingUsers, $voucherData) {
            $buyerUser = $bookingUsers->clientMain;

            // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
            try
            {
                \Mail::to($buyerUser->email)
                    ->send(new BookingCancelMailer(
                        $school,
                        $booking,
                        $bookingUsers,
                        $buyerUser,
                        $voucherData
                    ));
            }
            catch (\Exception $ex)
            {
                \Illuminate\Support\Facades\Log::debug('BookingController->cancelBookingFull BookingCancelMailer: ' . $ex->getMessage());
            }
        })->afterResponse();


    }
}
