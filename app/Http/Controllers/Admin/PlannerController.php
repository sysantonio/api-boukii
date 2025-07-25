<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\BookingUser;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorSportAuthorizedDegree;
use App\Models\MonitorsSchool;
use App\Models\Station;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
    const CACHE_TTL = 600; // 10 minutes

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
        $schoolId = $this->getSchool($request)->id;
        $cacheKey = 'planner_' . md5(json_encode([
            'school_id'  => $schoolId,
            'date_start' => $request->input('date_start'),
            'date_end'   => $request->input('date_end'),
            'monitor_id' => $request->input('monitor_id'),
            'languages'  => $request->input('languages'),
        ]));

        $ttl = config('app.planner_cache_ttl', self::CACHE_TTL);

        $data = Cache::remember($cacheKey, $ttl, function () use ($request) {
            return $this->performPlannerQuery($request);
        });

        return $this->sendResponse($data, 'Planner retrieved successfully');
    }

    public function performPlannerQuery(Request $request): \Illuminate\Support\Collection
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $monitorId = $request->input('monitor_id');
        $languagesInput = $request->input('languages');
        $languageIds = [];
        if (!empty($languagesInput)) {
            if (is_string($languagesInput)) {
                $languagesInput = array_map('trim', explode(',', $languagesInput));
            }
            if (!is_array($languagesInput)) {
                $languagesInput = [$languagesInput];
            }
            $languageIds = array_filter(array_map('intval', $languagesInput));
        }

        $schoolId = $this->getSchool($request)->id;

        $subgroupsQuery = CourseSubgroup::with(['courseGroup.course', 'bookingUsers.client.sports', 'bookingUsers.booking.user',
            'bookingUsers.client.evaluations.degree', 'bookingUsers.client.evaluations.evaluationFulfilledGoals'])
            ->whereHas('courseGroup.course', function ($query) use ($schoolId) {
                // Agrega la comprobación de la escuela aquí
                $query->where('school_id', $schoolId)->where('active', 1);
            })
            ->whereHas('courseDate', function ($query) use ($dateStart, $dateEnd) {
                if ($dateStart && $dateEnd) {
                    // Filtra las fechas del subgrupo en el rango proporcionado
                    $query->whereBetween('date', [$dateStart, $dateEnd])->where('active', 1);
                } else {
                    $today = Carbon::today();

                    // Busca en el día de hoy para las reservas
                    $query->whereDate('date', $today)->where('active', 1);
                }
            })
            ->with('bookingUsers', function ($query) {
                // Agregar la restricción para traer solo las booking_users con status = 1
                $query->where('status', 1)->whereHas('booking');
            });

        // Consulta para las reservas (BookingUser)
        $bookingQuery = BookingUser::with(['booking.user', 'course.courseDates', 'client.sports',
            'client.evaluations.degree', 'client.evaluations.evaluationFulfilledGoals'])
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->where('school_id', $schoolId)
            ->where('course_subgroup_id', null)
            ->where('status', 1)
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
            $subgroupsQuery->where('monitor_id', $monitorId);

            // Obtén solo el monitor específico
            $monitors = MonitorsSchool::with(['monitor.sports'=> function ($query) use ($schoolId) {
                $query->where('monitor_sports_degrees.school_id', $schoolId);
            },
                'monitor.courseSubgroups'
            => function ($query) use ($dateStart, $dateEnd) {
                    $query->whereHas('courseDate', function ($query)  use ($dateStart, $dateEnd) {
                        if ($dateStart && $dateEnd) {
                            // Filtra las fechas del subgrupo en el rango proporcionado
                            $query->whereBetween('date', [$dateStart, $dateEnd]);
                        } else {
                            $today = Carbon::today();

                            // Busca en el día de hoy para las reservas
                            $query->whereDate('date', $today);

                        }
                    });
                }])
                ->where('school_id', $schoolId)
                ->whereHas('monitor', function ($query) use ($monitorId, $languageIds) {
                    $query->where('id', $monitorId);
                    if (!empty($languageIds)) {
                        $query->where(function ($q) use ($languageIds) {
                            $q->whereIn('language1_id', $languageIds)
                                ->orWhereIn('language2_id', $languageIds)
                                ->orWhereIn('language3_id', $languageIds)
                                ->orWhereIn('language4_id', $languageIds)
                                ->orWhereIn('language5_id', $languageIds)
                                ->orWhereIn('language6_id', $languageIds);
                        });
                    }
                })
                ->get()
                ->pluck('monitor');
        } else {
            // Si no se proporcionó monitor_id, obtén todos los monitores como antes
            $monitorSchools = MonitorsSchool::with(['monitor.sports'
            => function ($query) use ($schoolId) {
                    $query->where('monitor_sports_degrees.school_id', $schoolId);
                },
                'monitor.courseSubgroups'
                => function ($query) use ($dateStart, $dateEnd) {
                    $query->whereHas('courseDate', function ($query)  use ($dateStart, $dateEnd) {
                        if ($dateStart && $dateEnd) {
                            // Filtra las fechas del subgrupo en el rango proporcionado
                            $query->whereBetween('date', [$dateStart, $dateEnd]);
                        } else {
                            $today = Carbon::today();

                            // Busca en el día de hoy para las reservas
                            $query->whereDate('date', $today);

                        }
                    });
                }])
                ->where('school_id', $schoolId)
                ->where('active_school', 1)
                ->when(!empty($languageIds), function ($query) use ($languageIds) {
                    $query->whereHas('monitor', function ($q) use ($languageIds) {
                        $q->where(function ($q2) use ($languageIds) {
                            $q2->whereIn('language1_id', $languageIds)
                                ->orWhereIn('language2_id', $languageIds)
                                ->orWhereIn('language3_id', $languageIds)
                                ->orWhereIn('language4_id', $languageIds)
                                ->orWhereIn('language5_id', $languageIds)
                                ->orWhereIn('language6_id', $languageIds);
                        });
                    });
                })
                ->get();
            $monitors = $monitorSchools->pluck('monitor');
        }

        foreach ($monitors as $monitor) {

            // Recorrer los deportes del monitor
            foreach ($monitor->sports as $sport) {

                $sport->authorizedDegrees = MonitorSportAuthorizedDegree::whereHas('monitorSport',
                    function ($q) use($sport, $schoolId, $monitor){
                    $q->where('sport_id', $sport->id)->where('school_id', $schoolId)->where('monitor_id', $monitor->id);
                })->with('degree')->get();
            }
        }

        // Obtén los resultados para las reservas y los MonitorNwd
        $nwd = $nwdQuery->get();
        $subgroups = $subgroupsQuery->get();
        $bookings = $bookingQuery->get();

        // Attach booking user id to each booking user for planner consumers
        $bookings->each(function ($bookingUser) {
            if ($bookingUser->relationLoaded('booking') && $bookingUser->booking) {
                $bookingUser->user_id = $bookingUser->booking->user_id;
            }
        });
        $subgroupsPerGroup = CourseSubgroup::select('course_group_id', DB::raw('COUNT(*) as total'))
            ->groupBy('course_group_id')
            ->pluck('total', 'course_group_id');
        $groupedData = collect([]);

        foreach ($monitors as $monitor) {

            $daysWithinRange = CarbonPeriod::create($dateStart, $dateEnd)->toArray();

            $allDaysMeetCriteria = true;

            foreach ($daysWithinRange as $day) {
                $hasFullDayNwd = MonitorNwd::where('school_id', $schoolId)
                        ->where('monitor_id', $monitor->id)
                        ->where('full_day', true)
                        ->where('user_nwd_subtype_id', 1)
                        ->whereDate('start_date', '<=', $day)
                        ->whereDate('end_date', '>=', $day)
                        ->count() > 0;

                if (!$hasFullDayNwd) {
                    $allDaysMeetCriteria = false;
                    break;
                }
            }

            $monitorBookings = $bookings->where('monitor_id', $monitor->id)
                ->groupBy(function ($booking) use($subgroupsPerGroup) {
                    // Diferencia la agrupación basada en el course_type
                    if ($booking->course->course_type == 2 || $booking->course->course_type == 3) {
                        // Agrupa por booking.course_id y booking.course_date_id para el tipo 2
                        return $booking->course_id . '-' . $booking->course_date_id;
                    }
                });

            $monitor->hasFullDayNwd = $allDaysMeetCriteria;

            $subgroupsWithMonitor = $subgroups->where('monitor_id', $monitor->id);

            $subgroupsArray = [];

            $subgroupsWithMonitor->each(function ($subgroup) use (&$subgroupsArray, $subgroupsPerGroup) {
                $subgroupId = $subgroup->id;
                $courseDateId = $subgroup->course_date_id;
                $courseId = $subgroup->course_id;

                $totalSubgroups = $subgroupsPerGroup[$subgroup->course_group_id] ?? 1;
                $subgroupPosition = CourseSubgroup::where('course_group_id', $subgroup->course_group_id)
                    ->where('id', '<=', $subgroupId)
                    ->count();

                $subgroup->subgroup_number = $subgroupPosition;
                $subgroup->total_subgroups = $totalSubgroups;

                $subgroup->loadMissing(['course.courseDates', 'courseGroup']);

                // Define la misma nomenclatura que en los bookings
                $nomenclature = $courseId . '-' . $courseDateId . '-' . $subgroupId;

                // Agrega el subgrupo al array con la nomenclatura como índice
                $subgroupsArray[$nomenclature] = $subgroup;
            });

            $allBookings = $monitorBookings->concat($subgroupsArray);


            $monitorNwd = $nwd->where('monitor_id', $monitor->id);

            $groupedData[$monitor->id] = [
                'monitor' => $monitor,
                'bookings' => $allBookings,
                'nwds' => $monitorNwd,
                /*'subgroups' => $availableSubgroups,*/
            ];
        }
        $bookingsWithoutMonitor = $bookings->whereNull('monitor_id')->groupBy(function ($booking) use ($subgroupsPerGroup) {
            if ($booking->course->course_type == 2 || $booking->course->course_type == 3) {
                // Si tiene group_id, agrúpalo por course_id, course_date_id y group_id
                if ($booking->group_id) {
                    return $booking->course_id . '-' . $booking->course_date_id . '-' . $booking->booking_id . '-' . $booking->group_id;
                }
                // Si no tiene group_id, agrupa por course_id y course_date_id
                return $booking->course_id . '-' . $booking->course_date_id . '-' . $booking->booking_id;
            }
        });



        $subgroupsWithoutMonitor = $subgroups->where('monitor_id', null);

        $subgroupsArray = [];

        $subgroupsWithoutMonitor->each(function ($subgroup) use (&$subgroupsArray, $subgroupsPerGroup) {
            $subgroupId = $subgroup->id;
            $courseDateId = $subgroup->course_date_id;
            $courseId = $subgroup->course_id;

            $totalSubgroups = $subgroupsPerGroup[$subgroup->course_group_id] ?? 1;
            $subgroupPosition = CourseSubgroup::where('course_group_id', $subgroup->course_group_id)
                ->where('id', '<=', $subgroupId)
                ->count();

            $subgroup->subgroup_number = $subgroupPosition;
            $subgroup->total_subgroups = $totalSubgroups;

            $subgroup->loadMissing(['course.courseDates', 'courseGroup']);

            // Define la misma nomenclatura que en los bookings
            $nomenclature = $courseId . '-' . $courseDateId . '-' . $subgroupId;

            // Agrega el subgrupo al array con la nomenclatura como índice
            $subgroupsArray[$nomenclature] = $subgroup;
        });

        $allBookings = $bookingsWithoutMonitor->concat($subgroupsArray);

        if ($allBookings->isNotEmpty()) {
            $groupedData['no_monitor'] = [
                'monitor' => null,
                'bookings' => $allBookings,
                'nwds' => collect([]),
                /* 'subgroups' => $subgroupsWithoutMonitor,*/
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
     *              @OA\Property(property="booking_users", type="array", description="Array of booking users to update.",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", description="The ID of the booking user."),
     *                      @OA\Property(property="date", type="string", format="date", description="The date of the booking user."),
     *                      @OA\Property(property="hour_start", type="string", format="time", description="The start time of the booking user."),
     *                      @OA\Property(property="hour_end", type="string", format="time", description="The end time of the booking user."),
     *                      @OA\Property(property="course_subgroup_id", type="integer", description="The ID of the course subgroup if applicable."),
     *                  )
     *              ),
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
        $courseSubgroupId  = $request->input('subgroup_id');
        if ($courseSubgroupId) {
            $courseSubgroup = CourseSubgroup::find($courseSubgroupId);

            if ($courseSubgroup) {
                // Comprobación de superposición usando la información de courseDate
                $date = $courseSubgroup->courseDate->date;
                $hourStart = $courseSubgroup->courseDate->hour_start;
                $hourEnd = $courseSubgroup->courseDate->hour_end;
                if($monitorId !== null) {
                    if (Monitor::isMonitorBusy($monitorId, $date, $hourStart, $hourEnd)) {
                        return $this->sendError('Overlap detected for subgroup.
                        Monitor cannot be transferred.');
                    }
                }

                // Actualizar el monitor_id del subgrupo
                $courseSubgroup->update(['monitor_id' => $monitorId]);
                $bookingUsers = BookingUser::where('course_subgroup_id', $courseSubgroup->id)->get();

                foreach ($bookingUsers as $bookingUser) {
                    // Actualizar el monitor_id de cada BookingUser
                    $bookingUser->monitor_id = $courseSubgroup->monitor_id;
                    $bookingUser->save();
                }

            } else {
                return $this->sendError('Subgroup cannot be found.');
            }

            // Actualizar el monitor_id del subgrupo

        }
        $overlapDetected = false;

        if ($monitorId !== null) {
            // Check if the monitor exists (only if monitor_id is provided)
            $monitor = Monitor::find($monitorId);

            if (!$monitor) {
                return $this->sendError('Monitor not found');
            }
        }

        // If monitor_id is null, set all monitors to null
        if ($monitorId === null) {
            foreach ($bookingUserIds as $bookingUserId) {
                $bookingUserModel = BookingUser::find($bookingUserId);

                if ($bookingUserModel) {
                    $bookingUserModel->update(['monitor_id' => null, 'accepted' => true]);
                }

                $courseSubgroupId = $bookingUserModel['course_subgroup_id'];

                // If the bookingUser has a course_subgroup_id, update the monitor_id of the subgroup
                if ($courseSubgroupId) {
                    $courseSubgroup = CourseSubgroup::find($courseSubgroupId);

                    if ($courseSubgroup) {
                        $courseSubgroup->update(['monitor_id' => null]);
                    }
                }
            }

            return $this->sendResponse(null, 'Monitor set to null for all bookingUsers successfully');
        }

        // Iterar sobre los bookingUsers
        foreach ($bookingUserIds as $bookingUserId) {
            // Obtener la información del bookingUser
            $bookingUser = BookingUser::find($bookingUserId);

            if (!$bookingUser) {
                return $this->sendError("BookingUser with ID $bookingUserId not found");
            }

            // If monitor_id is not null, check for monitor availability using isMonitorBusy
            if (Monitor::isMonitorBusy($monitorId, $bookingUser['date'], $bookingUser['hour_start'], $bookingUser['hour_end'])) {
                $overlapDetected = true;
                break; // Se detectó superposición, sal del bucle
            }
        }

        if ($overlapDetected) {
            return $this->sendError('Overlap detected. Monitor cannot be transferred.');
        }

        // Si no hay superposición y monitor_id is not null, update the monitor_id of all bookingUsers and subgroups if necessary
        foreach ($bookingUserIds as $bookingUserId) {
            // Actualizar el monitor_id del bookingUser
            $bookingUserModel = BookingUser::find($bookingUserId);

            $courseSubgroupId = $bookingUserModel['course_subgroup_id'];

            if ($bookingUserModel) {
                $bookingUserModel->update(['monitor_id' => $monitorId, 'accepted' => true]);
            }

            // Si el bookingUser tiene un course_subgroup_id, actualizar el monitor_id del subgrupo
            if ($courseSubgroupId) {
                $courseSubgroup = CourseSubgroup::find($courseSubgroupId);

                if ($courseSubgroup) {
                    $courseSubgroup->update(['monitor_id' => $monitorId]);
                }
            }
        }

        return $this->sendResponse($monitor, 'Monitor updated for bookingUsers successfully');
    }



}
