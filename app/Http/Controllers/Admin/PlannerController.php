<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\BookingUser;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorsSchool;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        /*$cacheKey = 'planner_data_' . md5(serialize($request->all()));

        // Intenta obtener los datos desde la caché
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            // Devuelve los datos de la caché si están disponibles
            return $this->sendResponse($cachedData, 'Planner retrieved successfully from cache');
        }

        // Si los datos no están en caché, realiza la consulta y guárdala en caché durante 10 minutos
        $data = $this->performPlannerQuery($request);

        // Guarda los datos en caché durante 10 minutos
        Cache::put($cacheKey, $data, now()->addMinutes(10));*/
        $data = $this->performPlannerQuery($request);
        return $this->sendResponse($data, 'Planner retrieved successfully');
    }

    public function performPlannerQuery(Request $request): \Illuminate\Support\Collection
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $monitorId = $request->input('monitor_id');

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


        if ($monitorId) {
            // Filtra solo las reservas y los NWD para el monitor específico
            $bookingQuery->where('monitor_id', $monitorId);
            $nwdQuery->where('monitor_id', $monitorId);

            // Obtén solo el monitor específico
            $monitors = MonitorsSchool::with('monitor.sports')
                ->where('school_id', $schoolId)
                ->whereHas('monitor', function ($query) use ($monitorId) {
                    $query->where('id', $monitorId);
                })
                ->get()
                ->pluck('monitor');
        } else {
            // Si no se proporcionó monitor_id, obtén todos los monitores como antes
            $monitorSchools = MonitorsSchool::with('monitor.sports')->where('school_id', $schoolId)
                ->where('active_school', 1)
                ->get();
            $monitors = $monitorSchools->pluck('monitor');
        }

        // Obtén los resultados para las reservas y los MonitorNwd
        $bookings = $bookingQuery->get();
        $nwd = $nwdQuery->get();
        $subgroupsPerGroup = CourseSubgroup::select('course_group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('course_group_id')
            ->pluck('total', 'course_group_id');
        $groupedData = collect([]);
       // return $this->sendResponse($subgroupsPerGroup, 'Planner retrieved successfully');
        foreach ($monitors as $monitor) {

            $monitorBookings = $bookings->where('monitor_id', $monitor->id)
                ->groupBy(function ($booking) use($subgroupsPerGroup) {
                    $courseId = $booking->course_id;
                    $courseDateId = $booking->course_date_id;
                    $subgroupId = $booking->course_subgroup_id ?? 'none';

                    if ($booking->course->course_type == 1 && $subgroupId !== 'none') {
                        $totalSubgroups = $subgroupsPerGroup[$booking->course_group_id] ?? 1;
                        $subgroupPosition = CourseSubgroup::where('course_group_id', $booking->course_group_id)
                            ->where('id', '<=', $subgroupId)
                            ->count();

                        $booking->subgroup_number = $subgroupPosition;
                        $booking->total_subgroups = $totalSubgroups;
                    }
                    // Diferencia la agrupación basada en el course_type
                    if ($booking->course->course_type == 2) {
                        // Agrupa por booking.course_id y booking.course_date_id para el tipo 2
                        return $booking->course_id . '-' . $booking->course_date_id;
                    } else {
                        // Agrupa por booking.course_id, booking.course_date_id y booking.course_subgroup_id para el tipo 1
                        return $booking->course_id . '-' . $booking->course_date_id . '-' . $booking->course_subgroup_id;
                    }
                });

            $monitorNwd = $nwd->where('monitor_id', $monitor->id);

            $groupedData[$monitor->id] = [
                'monitor' => $monitor,
                'bookings' => $monitorBookings,
                'nwds' => $monitorNwd,
            ];
        }

//      Incluye reservas que no tienen monitor asignado
        $bookingsWithoutMonitor = $bookings->whereNull('monitor_id')->groupBy(function ($booking) use($subgroupsPerGroup) {
            if ($booking->course->course_type == 2) {
                return $booking->course_id . '-' . $booking->course_date_id;
            } else {
                $subgroupId = $booking->course_subgroup_id ?? 'none';
                if ($subgroupId !== 'none') {
                    $totalSubgroups = $subgroupsPerGroup[$booking->course_group_id] ?? 1;
                    $subgroupPosition = CourseSubgroup::where('course_group_id', $booking->course_group_id)
                        ->where('id', '<=', $subgroupId)
                        ->count();

                    $booking->subgroup_number = $subgroupPosition;
                    $booking->total_subgroups = $totalSubgroups;
                }
                return $booking->course_id . '-' . $booking->course_date_id . '-' . $booking->course_subgroup_id;
            }

        });
        if ($bookingsWithoutMonitor->isNotEmpty()) {
            $groupedData['no_monitor'] = [
                'monitor' => null,
                'bookings' => $bookingsWithoutMonitor,
                'nwds' => collect([]),
            ];
        }

        return $groupedData;
    }

    /**
     * @OA\Post(
     *      path="/admin/planner/monitors/transfer",
     *      summary="Transfer Monitor",
     *      tags={"Admin"},
     *      description="Transfer a monitor to multiple booking users and update their course subgroups if applicable.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Request body for transferring a monitor.",
     *          @OA\JsonContent(
     *              required={"monitor_id", "booking_users"},
     *              @OA\Property(property="monitor_id", type="integer", description="The ID of the monitor to transfer."),
     *              @OA\Property(property="booking_users", type="array", description="Array of booking users to update."),
     *              @OA\Property(property="booking_users.*.id", type="integer", description="The ID of the booking user."),
     *              @OA\Property(property="booking_users.*.date", type="string", format="date", description="The date of the booking user."),
     *              @OA\Property(property="booking_users.*.hour_start", type="string", format="time", description="The start time of the booking user."),
     *              @OA\Property(property="booking_users.*.hour_end", type="string", format="time", description="The end time of the booking user."),
     *              @OA\Property(property="booking_users.*.course_subgroup_id", type="integer", description="The ID of the course subgroup if applicable."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean"),
     *              @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  description="Message indicating a successful transfer.",
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean"),
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Error message if the request is invalid.",
     *              ),
     *          ),
     *      )
     * )
     */
    public function transferMonitor(Request $request)
    {
        $monitorId = $request->input('monitor_id');
        $bookingUserIds  = $request->input('booking_users');
        $overlapDetected = false;

        $monitor = Monitor::find($monitorId);

        if (!$monitor) {
            return $this->sendError('Monitor not found');
        }

        // Iterar sobre los bookingUsers
        foreach ($bookingUserIds as $bookingUserId) {
            // Obtener la información del bookingUser
            $bookingUser = BookingUser::find($bookingUserId);

            if (!$bookingUser) {
                return $this->sendError("BookingUser with ID $bookingUserId not found");
            }

            // Verificar si el monitor está ocupado para este bookingUser utilizando isMonitorBusy
            if (Monitor::isMonitorBusy($monitorId, $bookingUser['date'], $bookingUser['hour_start'],
                $bookingUser['hour_end'])) {
                $overlapDetected = true;
                break; // Se detectó superposición, sal del bucle
            }
        }

        if ($overlapDetected) {
            return $this->sendError('Overlap detected. Monitor cannot be transferred.');
        }

        // Si no hay superposición, actualizar el monitor_id de todos los bookingUsers y subgrupos si es necesario
        foreach ($bookingUserIds as $bookingUserId) {

            // Actualizar el monitor_id del bookingUser
            $bookingUserModel = BookingUser::find($bookingUserId);

            $courseSubgroupId = $bookingUserModel['course_subgroup_id'];

            if ($bookingUserModel) {
                $bookingUserModel->update(['monitor_id' => $monitorId]);
            }

            // Si el bookingUser tiene un course_subgroup_id, actualizar el monitor_id del subgrupo
            if ($courseSubgroupId) {
                $courseSubgroup = CourseSubgroup::find($courseSubgroupId);

                if ($courseSubgroup) {
                    $courseSubgroup->update(['monitor_id' => $monitorId]);
                }
            }
        }

        return $this->sendResponse($monitor,'Monitor updated for bookingUsers successfully');
    }



}
