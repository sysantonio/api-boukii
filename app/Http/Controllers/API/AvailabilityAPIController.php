<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBookingAPIRequest;
use App\Http\Requests\API\UpdateBookingAPIRequest;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\Course;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class BookingController
 */

class AvailabilityAPIController extends AppBaseController
{

    public function __construct()
    {

    }

    /**
     * @OA\Get(
     *      path="/availability",
     *      summary="Get Availability",
     *      tags={"Availability"},
     *      description="Get availability of courses based on type, dates, sport, and client",
     *      @OA\Parameter(
     *          name="start_date",
     *          description="Start date of the period",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          description="End date of the period",
     *          required=true,
     *          in="query",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="course_type",
     *          description="Type of the course",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="sport_id",
     *          description="ID of the sport",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="client_id",
     *          description="ID of the client",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Course")
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
        // Validación de las fechas
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        if (!$startDate || !$endDate || $startDate->gt($endDate)) {
            return $this->sendError('Invalid date range', 422);
        }

        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');

        $type = $request->input('course_type') ?? 1;
        $sportId = $request->input('sport_id');
        $clientId = $request->input('client_id');

        try {
            $courses = Course::withAvailableDates($type, $startDate, $endDate, $sportId, $clientId)->get();

            return $this->sendResponse($courses, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving bookings', 500);
        }
    }


}
