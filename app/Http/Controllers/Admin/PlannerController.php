<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\BookingUser;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorsSchool;
use App\Models\Station;
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

class PlannerController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/admin/getPlanner",
     *      summary="Get Planner for all monitors",
     *      tags={"Admin"},
     *      description="Get planner",
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

    public function getPlanner(Request $request): JsonResponse
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $schoolId = $this->getSchool($request)->id;

        // Consulta para las reservas (BookingUser)
        $bookingQuery = BookingUser::with('booking', 'course.courseDates', 'client.sports',
            'client.evaluations.degree', 'client.evaluations.evaluationFulfilledGoals')
            ->where('school_id', $schoolId) // Filtra por school_id
            ->orderBy('hour_start');

        // Consulta para los MonitorNwd
        $nwdQuery = MonitorNwd::where('school_id', $schoolId) // Filtra por school_id
        ->orderBy('start_time');

        if($schoolId) {
            $bookingQuery->where('school_id', $schoolId);

            $nwdQuery->where('school_id', $schoolId);
        }

        // Si se proporcionaron date_start y date_end, busca en el rango de fechas
        if ($dateStart && $dateEnd) {
            // Busca en el rango de fechas proporcionado para las reservas
            $bookingQuery->whereBetween('date', [$dateStart, $dateEnd]);

            // Busca en el rango de fechas proporcionado para los MonitorNwd
            $nwdQuery->whereBetween('start_date', [$dateStart, $dateEnd])
                ->whereBetween('end_date', [$dateStart, $dateEnd]);
        } else {
            // Si no se proporcionan fechas, busca en el día de hoy
            $today = Carbon::today();

            // Busca en el día de hoy para las reservas
            $bookingQuery->whereDate('date', $today);

            // Busca en el día de hoy para los MonitorNwd
            $nwdQuery->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);
        }

        // Obtén los resultados para las reservas y los MonitorNwd
        $bookings = $bookingQuery->get();
        $nwd = $nwdQuery->get();

        $monitorSchools = MonitorsSchool::with('monitor')->where('school_id', $schoolId)->get();
        $monitors = $monitorSchools->pluck('monitor');

        $groupedData = $monitors->mapWithKeys(function ($monitor) use ($bookings, $nwd) {
            $monitorBookings = $bookings->where('monitor_id', $monitor->id);
            $monitorNwd = $nwd->where('monitor_id', $monitor->id);

            return [$monitor->id => [
                'monitor' => $monitor,
                'bookings' => $monitorBookings->groupBy('course.course_type'), // Agrupar por course_type
                'nwds' => $monitorNwd,
            ]];
        });


        return $this->sendResponse($groupedData, 'Planner retrieved successfully');
    }

}
