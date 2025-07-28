<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseCrudController;
use App\Http\Requests\API\CreateBookingUserAPIRequest;
use App\Http\Requests\API\UpdateBookingUserAPIRequest;
use App\Http\Resources\API\BookingUserResource;
use App\Models\BookingUser;
use App\Repositories\BookingUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class BookingUserController
 */

class BookingUserAPIController extends BaseCrudController
{
    public function __construct(BookingUserRepository $bookingUserRepo)
    {
        parent::__construct($bookingUserRepo);
        $this->resource = BookingUserResource::class;
    }

    /**
     * @OA\Get(
     *      path="/booking-users",
     *      summary="getBookingUserList",
     *      tags={"BookingUser"},
     *      description="Get all BookingUsers",
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
     *                  @OA\Items(ref="#/components/schemas/BookingUser")
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
        $bookingUsers = $this->repository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id'),
            additionalConditions: function ($query) {
                $query->whereHas('booking');

            }
        );

        return $this->sendResponse($bookingUsers, 'Booking Users retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/booking-users",
     *      summary="createBookingUser",
     *      tags={"BookingUser"},
     *      description="Create BookingUser",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/BookingUser")
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
     *                  ref="#/components/schemas/BookingUser"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        return parent::store($request);
    }

    /**
     * @OA\Get(
     *      path="/booking-users/{id}",
     *      summary="getBookingUserItem",
     *      tags={"BookingUser"},
     *      description="Get BookingUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUser",
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
     *                  ref="#/components/schemas/BookingUser"
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
        return parent::show($id, $request);
    }

    /**
     * @OA\Put(
     *      path="/booking-users/{id}",
     *      summary="updateBookingUser",
     *      tags={"BookingUser"},
     *      description="Update BookingUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUser",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/BookingUser")
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
     *                  ref="#/components/schemas/BookingUser"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, Request $request): JsonResponse
    {
        return parent::update($id, $request);
    }

    /**
     * @OA\Delete(
     *      path="/booking-users/{id}",
     *      summary="deleteBookingUser",
     *      tags={"BookingUser"},
     *      description="Delete BookingUser",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of BookingUser",
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
        return parent::destroy($id);
    }
}
