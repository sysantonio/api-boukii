<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\BookingUserResource;
use App\Http\Resources\Teach\HomeAgendaResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\MonitorNwd;
use App\Models\MonitorObservation;
use App\Models\MonitorSportsDegree;
use App\Models\MonitorsSchool;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

;

/**
 * Class HomeController
 * @package App\Http\Controllers\Teach
 */

class MonitorController extends AppBaseController
{

    public function __construct()
    {

    }



    /**
     * @OA\Get(
     *      path="/admin/monitor/pastBookings",
     *      summary="getMonitorPastBookings",
     *      tags={"Admin"},
     *      description="Get past Bookings of Monitor",
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
    public function getPastBookings(Request $request): JsonResponse
    {
        $monitor = $this->getMonitor($request);

        $seasonStart = Season::where('school_id', $request->school_id)->where('is_active', 1)->select('start_date')->first();


        $bookingQuery = BookingUser::with('booking', 'course.courseDates', 'client')
            ->where('school_id', $monitor->active_school)
            ->byMonitor($monitor->id)
            ->where('date', '<=', Carbon::today());

        if ($seasonStart) {
            $bookingQuery->orWhere('date', '>', $seasonStart->date_start);
        }

        $bookings = $bookingQuery
            ->selectRaw('MIN(id) as id, booking_id, MAX(date) as date, MAX(hour_start) as hour_start') // Ajusta esto según tus necesidades
            ->orderBy('date')
            ->orderBy('hour_start')
            ->groupBy('booking_id')
            ->get();

        return $this->sendResponse($bookings, 'Bookings returned successfully');
    }

}
