<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBookingAPIRequest;
use App\Http\Requests\API\UpdateBookingAPIRequest;
use App\Http\Resources\API\BookingResource;
use App\Mail\BookingInfoUpdateMailer;
use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Class BookingController
 */

class BookingAPIController extends AppBaseController
{
    /** @var  BookingRepository */
    private $bookingRepository;

    public function __construct(BookingRepository $bookingRepo)
    {
        $this->bookingRepository = $bookingRepo;
    }

    /**
     * @OA\Get(
     *      path="/bookings",
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
        $bookings = $this->bookingRepository->all(
            searchArray: $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with', 'isMultiple', 'courseType', 'finished']),
            search: $request->get('search'),
            skip: $request->get('skip'),
            limit: $request->get('limit'),
            with: $request->get('with', []),
            order: $request->get('order', 'desc'),
            orderColumn: $request->get('orderColumn', 'id'),
            additionalConditions: function ($query) use ($request) {
                // Filtrar por reservas con múltiples bookingUsers con user_id diferentes
                if ($request->has('isMultiple') && $request->isMultiple == true) {
                    $query->whereHas('bookingUsers', function ($subQuery) {
                        $subQuery->select('booking_id')
                            ->groupBy('booking_id')
                            ->havingRaw('COUNT(DISTINCT user_id) > 1');
                    });
                }

                if ($request->has('courseType')) {
                    $query->whereHas('bookingUsers.course', function ($subQuery) use ($request) {
                        $subQuery->where('course_type', $request->courseType);
                    });
                }

                // Filtrar por reservas que tienen todos los bookingUsers con courseDate anteriores al día de hoy
                if ($request->has('finished')) {
                    $today = now()->format('Y-m-d H:i:s');
                    $isFinished = $request->finished == 1; // Verifica si finished es 1

                    $query->whereDoesntHave('bookingUsers', function ($subQuery) use ($today, $isFinished) {
                        $subQuery->where(function ($dateQuery) use ($today, $isFinished) {
                            if ($isFinished) {
                                // Filtra las reservas finalizadas
                                $dateQuery->where('date', '<=', $today)
                                    ->orWhere(function ($hourQuery) use ($today) {
                                        $hourQuery->where('date', $today)
                                            ->where('hour_end', '<=', $today);
                                    });
                            } else {
                                // Filtra las reservas no finalizadas
                                $dateQuery->where('date', '>', $today)
                                    ->orWhere(function ($hourQuery) use ($today) {
                                        $hourQuery->where('date', $today)
                                            ->where('hour_end', '>', $today);
                                    });
                            }
                        });
                    });
                }
            }
        );

        return $this->sendResponse($bookings, 'Bookings retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/bookings",
     *      summary="createBooking",
     *      tags={"Booking"},
     *      description="Create Booking",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Booking")
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
     *                  ref="#/components/schemas/Booking"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateBookingAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $booking = $this->bookingRepository->create($input);

        return $this->sendResponse(new BookingResource($booking), 'Booking saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/bookings/{id}",
     *      summary="getBookingItem",
     *      tags={"Booking"},
     *      description="Get Booking",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Booking",
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
     *                  ref="#/components/schemas/Booking"
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
        /** @var Booking $booking */
        $booking = $this->bookingRepository->find($id, with: $request->get('with', []));

        if (empty($booking)) {
            return $this->sendError('Booking not found');
        }

        return $this->sendResponse($booking, 'Booking retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/bookings/{id}",
     *      summary="updateBooking",
     *      tags={"Booking"},
     *      description="Update Booking",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Booking",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Booking")
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
     *                  ref="#/components/schemas/Booking"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateBookingAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Booking $booking */
        $booking = $this->bookingRepository->find($id, with: $request->get('with', []));

        if (empty($booking)) {
            return $this->sendError('Booking not found');
        }

        $booking = $this->bookingRepository->update($input, $id);

        if($request->has('send_mail') && $request->input('send_mail')) {
            dispatch(function () use ($booking) {
                // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                try {
                    Mail::to($booking->clientMain->email)->send(new BookingInfoUpdateMailer($booking->school, $booking, $booking->clientMain));
                } catch (\Exception $ex) {
                    \Illuminate\Support\Facades\Log::debug('Admin/COurseController BookingInfoUpdateMailer: ',
                        $ex->getTrace());
                }
            })->afterResponse();
        }

        return $this->sendResponse($booking, 'Booking updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/bookings/{id}",
     *      summary="deleteBooking",
     *      tags={"Booking"},
     *      description="Delete Booking",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Booking",
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
        /** @var Booking $booking */
        $booking = $this->bookingRepository->find($id);
        if (empty($booking)) {
            return $this->sendError('Booking not found');
        }

        $booking->delete();

        return $this->sendSuccess('Booking deleted successfully');
    }
}
