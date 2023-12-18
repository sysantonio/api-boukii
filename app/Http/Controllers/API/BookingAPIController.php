<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBookingAPIRequest;
use App\Http\Requests\API\UpdateBookingAPIRequest;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        // Inicia la consulta básica
        $query = $this->bookingRepository->allQuery(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id'),
            $request->get('with', [])
        );

        // Filtrar por reservas con múltiples bookingUsers con user_id diferentes
        if ($request->has('isMultiple') && $request->isMultiple == true) {
            $query->whereHas('bookingUsers', function ($subQuery) {
                $subQuery->select('booking_id')
                    ->groupBy('booking_id')
                    ->havingRaw('COUNT(DISTINCT user_id) > 1');
            });
        }

        if ($request->has('courseType')) {
            $query->whereHas('bookingUsers.course', function ($query) use ($request) {
                $query->where('course_type', $request->courseType);
            });
        }


        // Ejecutar la consulta y obtener los resultados
        $bookings = $query->paginate($request->get('perPage', 10));


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

        return $this->sendResponse(new BookingResource($booking), 'Booking updated successfully');
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
