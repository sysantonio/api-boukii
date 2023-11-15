<?php

namespace App\Http\Controllers\Teach;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\BookingUserResource;
use App\Http\Resources\Teach\HomeAgendaResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\MonitorNwd;
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

class HomeController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/teach/getAgenda",
     *      summary="Get Monitor Agenda",
     *      tags={"Teach"},
     *      description="Get Monitor agenda",
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
     *                  type="object",
     *                  @OA\Property(
     *                      property="bookings",
     *                      type="array",
     *                      @OA\Items(
     *                          ref="#/components/schemas/Booking"
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="nwds",
     *                      type="array",
     *                      @OA\Items(
     *                          ref="#/components/schemas/MonitorNwd"
     *                      )
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function getAgenda(Request $request): JsonResponse
    {

        $monitor = $this->getMonitor($request);

        $bookings = BookingUser::with('booking', 'course.courseDates', 'client')
            ->where('school_id', $request->school_id)
            ->whereDate('date', Carbon::today())
            ->byMonitor($monitor->id)
            ->orderBy('hour_start')
            ->get();
        $nwd = MonitorNwd::where('monitor_id', $monitor->id)
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->orderBy('start_time')
            ->get();

        $data = ['bookings' => $bookings, 'nwd' => $nwd];

        return $this->sendResponse(new HomeAgendaResource($data), 'Agenda retrieved successfully');
    }

}
