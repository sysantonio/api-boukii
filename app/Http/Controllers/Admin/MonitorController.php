<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\BookingUserResource;
use App\Http\Resources\Teach\HomeAgendaResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\CourseSubgroup;
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
     * @OA\Post(
     *      path="/admin/monitor/available",
     *      summary="getMonitorsAvailable",
     *      tags={"Admin"},
     *      description="Get monitors available",
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
     *                  @OA\Items(ref="#/components/schemas/Monitor")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getMonitorsAvailable(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        // Paso 1: Obtener todos los monitores que tengan el deporte y grado requerido.
        $eligibleMonitors = MonitorSportsDegree::whereHas('monitorSportAuthorizedDegrees', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })
            ->where('sport_id', $request->sportId)
            ->where('degree_id', '>=', $request->minimumDegreeId)
            ->with(['monitor' => function($query) use ($school) {
                $query->whereHas('monitorsSchools', function ($subQuery) use ($school) {
                    $subQuery->where('school_id', $school->id);
                });
            }])
            ->get()
            ->pluck('monitor');

        // Paso 2: Excluir monitores que tienen compromisos durante ese tiempo.
        // Esto incluye monitores con reservas (BookingUser) o no disponibles (MonitorNwd).
        $busyMonitors = BookingUser::whereDate('date', $request->date)
            ->where(function($query) use ($request) {
                $query->whereTime('hour_start', '<=', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>=', Carbon::createFromFormat('H:i', $request->startTime));
            })
            ->pluck('monitor_id')
            ->merge(MonitorNwd::whereDate('start_date', '<=', $request->date)
                ->whereDate('end_date', '>=', $request->date)
                ->where(function($query) use ($request) {
                    $query->whereTime('start_time', '<=', Carbon::createFromFormat('H:i', $request->endTime))
                        ->whereTime('end_time', '>=', Carbon::createFromFormat('H:i', $request->startTime));
                })
                ->pluck('monitor_id'))
            ->merge(CourseSubgroup::whereHas('courseDate', function($query) use ($request) {
                $query->whereDate('date', $request->date)
                    ->whereTime('hour_start', '<=', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>=', Carbon::createFromFormat('H:i', $request->startTime));
            })
                ->pluck('monitor_id'))
            ->unique();


        // Paso 3: Filtrar los monitores elegibles excluyendo los ocupados.
        $availableMonitors = $eligibleMonitors->whereNotIn('id', $busyMonitors);

        // Paso 4: Devolver los monitores disponibles.
        return $this->sendResponse($availableMonitors, 'Monitors returned successfully');

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
     *
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

    public function findAvailableMonitors($date, $startTime, $endTime, $sportId, $minimumDegreeId)
    {


    }

}
