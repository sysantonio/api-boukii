<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBookingUserExtraAPIRequest;
use App\Http\Requests\API\UpdateBookingUserExtraAPIRequest;
use App\Http\Resources\API\BookingUserExtraResource;
use App\Models\BookingUserExtra;
use App\Repositories\BookingUserExtraRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class BookingUserExtraController
 */

class BookingUserExtraAPIController extends AppBaseController
{
    /** @var  BookingUserExtraRepository */
    private $bookingUserExtraRepository;

    public function __construct(BookingUserExtraRepository $bookingUserExtraRepo)
    {
        $this->bookingUserExtraRepository = $bookingUserExtraRepo;
    }

    /**
     * @OA\Get(
     *      path="/booking-user-extras",
     *      summary="getBookingUserExtraList",
     *      tags={"BookingUserExtra"},
     *      description="Get all BookingUserExtras",
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
     *                  @OA\Items(ref="#/components/schemas/BookingUserExtra")
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
        $bookingUserExtras = $this->bookingUserExtraRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(BookingUserExtraResource::collection($bookingUserExtras), 'Booking User Extras retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/booking-user-extras",
     *      summary="createBookingUserExtra",
     *      tags={"BookingUserExtra"},
     *      description="Create BookingUserExtra",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/BookingUserExtra")
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
     *                  ref="#/components/schemas/BookingUserExtra"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateBookingUserExtraAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $bookingUserExtra = $this->bookingUserExtraRepository->create($input);

        return $this->sendResponse(new BookingUserExtraResource($bookingUserExtra), 'Booking User Extra saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/booking-user-extras/{id}",
     *      summary="getBookingUserExtraItem",
     *      tags={"BookingUserExtra"},
     *      description="Get BookingUserExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUserExtra",
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
     *                  ref="#/components/schemas/BookingUserExtra"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        /** @var BookingUserExtra $bookingUserExtra */
        $bookingUserExtra = $this->bookingUserExtraRepository->find($id);

        if (empty($bookingUserExtra)) {
            return $this->sendError('Booking User Extra not found');
        }

        return $this->sendResponse(new BookingUserExtraResource($bookingUserExtra), 'Booking User Extra retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/booking-user-extras/{id}",
     *      summary="updateBookingUserExtra",
     *      tags={"BookingUserExtra"},
     *      description="Update BookingUserExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUserExtra",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/BookingUserExtra")
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
     *                  ref="#/components/schemas/BookingUserExtra"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateBookingUserExtraAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var BookingUserExtra $bookingUserExtra */
        $bookingUserExtra = $this->bookingUserExtraRepository->find($id);

        if (empty($bookingUserExtra)) {
            return $this->sendError('Booking User Extra not found');
        }

        $bookingUserExtra = $this->bookingUserExtraRepository->update($input, $id);

        return $this->sendResponse(new BookingUserExtraResource($bookingUserExtra), 'BookingUserExtra updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/booking-user-extras/{id}",
     *      summary="deleteBookingUserExtra",
     *      tags={"BookingUserExtra"},
     *      description="Delete BookingUserExtra",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUserExtra",
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
        /** @var BookingUserExtra $bookingUserExtra */
        $bookingUserExtra = $this->bookingUserExtraRepository->find($id);

        if (empty($bookingUserExtra)) {
            return $this->sendError('Booking User Extra not found');
        }

        $bookingUserExtra->delete();

        return $this->sendSuccess('Booking User Extra deleted successfully');
    }
}
