<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\PayrexxHelpers;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\BookingUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        if (!$booking) {
            return $this->sendError('Booking not found', [], 404);
        }

        if ($booking->payment_method_id == 1) {
            return $this->sendError('Payment method not supported for this booking');
        }

        $payrexxLink = PayrexxHelpers::createGatewayLink(
            $school,
            $booking,
            $request->bonus,
            $booking->clientMain,
            $request->bookingCourses,
            $request->reduction,
            'panel'
        );

        if ($payrexxLink) {
            return $this->sendResponse($payrexxLink, 'Link retrieved successfully');
        }

        if ($booking->payment_method_id == 2) {
            $payrexxLink = PayrexxHelpers::createGatewayLink(
                $school,
                $booking,
                $request->bonus,
                $booking->clientMain,
                $request->bookingCourses,
                $request->reduction,
                'panel'
            );

            if ($payrexxLink) {
                return $this->sendResponse($payrexxLink, 'Link retrieved successfully');
            }

            return $this->sendError('Link could not be created');
        }

        if ($booking->payment_method_id == 3) {
            dispatch(function () use ($school, $booking, $request) {
                PayrexxHelpers::sendPayEmail(
                    $school,
                    $booking,
                    $request->bonus,
                    $booking->clientMain,
                    $request->bookingCourses,
                    $request->reduction
                );
            })->afterResponse();

            return $this->sendResponse([], 'Mail sent correctly');
        }

        return $this->sendError('Invalid payment method');
    }
}
