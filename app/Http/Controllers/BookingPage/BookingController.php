<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\Voucher;
use App\Models\VouchersLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class BookingController extends SlugAuthController
{

    /**
     * @OA\Post(
     *      path="/slug/bookings",
     *      summary="createCourse",
     *      tags={"BookingPage"},
     *      description="Create Course",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
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
     *                  ref="#/components/schemas/Course"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function store(Request $request)
    {

        //TODO: Check OVERLAP
        $data = $request->all();
        // Crear la reserva (Booking)
        $booking = Booking::create([
            'school_id' => $data['school_id'],
            'client_main_id' => $data['client_main_id'],
            'price_total' => $data['price_total'],
            'has_cancellation_insurance' => $data['has_cancellation_insurance'],
            'price_cancellation_insurance' => $data['price_cancellation_insurance'],
            'status' => 1,
            'currency' => 'CHF',

            // ... otros campos
        ]);

        // Crear BookingUser para cada detalle
        foreach ($data['cart'] as $cartItem) {
            foreach ($cartItem['details'] as $detail) {
                BookingUser::create([
                    'school_id' => $detail['school_id'],
                    'booking_id' => $booking->id,
                    'client_id' => $detail['client_id'],
                    'price' => $detail['price'],
                    'currency' => $detail['currency'],
                    'course_id' => $detail['course_id'],
                    'course_date_id' => $detail['course_date_id'],
                    'course_group_id' => $detail['course_group_id'],
                    'course_subgroup_id' => $detail['course_subgroup_id'],
                    'date' => $detail['date'],
                    'hour_start' => $detail['hour_start'],
                    'hour_end' => $detail['hour_end'],
                    // Puedes añadir campos adicionales según necesites
                ]);
            }
        }

        // Actualizar VouchersLog y el cupón si es necesario
        if ($data['voucherAmount'] > 0) {
            // Suponiendo que 'voucher_id' es parte de tu request
            $voucher = Voucher::find($request->voucher['id']);
            $voucher->remaining_balance -= $data['voucherAmount'];
            $voucher->save();


            VouchersLog::create([
                'voucher_id' => $voucher->id,
                'booking_id' => $booking->id,
                'amount' => -$data['voucherAmount'],
            ]);
        }

        $client = Client::find($data['client_main_id'])->load('user');
        BookingLog::create([
            'booking_id' => $booking->id,
            'action' => 'Booking created from booking page',
            'user_id' => $client->user->id
        ]);

        return response()->json(['message' => 'Reserva creada con éxito', 'booking_id' => $booking->id], 201);

    }

    /**
     * @OA\Post(
     *      path="/slug/bookings/checkbooking",
     *      summary="checkOverlapBooking",
     *      tags={"BookingPage"},
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
        //dd($request->all());
        foreach ($request->bookingUsers as $bookingUser) {

            if(BookingUser::hasOverlappingBookings($bookingUser)) {
                return $this->sendError( 'Client has booking on that date');
            }
        }

        return $this->sendResponse([], 'Client has not overlaps bookings');
    }

}
