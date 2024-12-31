<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Degree;
use App\Models\Monitor;
use App\Models\MonitorsSchool;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class CourseController extends SlugAuthController
{

    /**
     * @OA\Get(
     *      path="/slug/courses",
     *      summary="getCourseList",
     *      tags={"BookingPage"},
     *      description="Get all Courses available by slug",
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
        $sportId = $request->input('sport_id') ?? 1;
        $clientId = $request->input('client_id');
        $minAge = $request->input('min_age') ?? null;
        $maxAge = $request->input('max_age') ?? null;
        $clientId = $request->input('client_id');
        $degreeOrder = $request->input('degree_order');
        $degreeOrderArray = [];
        if($degreeOrder) {
            $degreeOrderArray = explode(',', $degreeOrder);
        }

        $getLowerDegrees = 1;
        // return $this->sendResponse($this->school->id, 'Courses retrieved successfully');
        $today = now(); // Obtener la fecha actual

        try {
            $courses =
                Course::withAvailableDates($type, $startDate, $endDate, $sportId, $clientId, null, $getLowerDegrees,
                    $degreeOrderArray, $minAge, $maxAge)
                    ->with('courseDates.courseGroups.courseSubgroups.monitor')
                    ->where('school_id', $this->school->id)
                    ->where('online', 1)
                    ->where('active', 1)
                    ->where(function($query) use ($today) {
                        $query->where(function($subquery) use ($today) {
                            $subquery->whereNull('date_start_res')
                                ->whereNull('date_end_res');
                        })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereDate('date_start_res', '<=', $today)
                                    ->whereDate('date_end_res', '>=', $today);
                            })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereDate('date_start_res', '=', $today)
                                    ->whereNotNull('date_end_res');
                            })
                            ->orWhere(function($subquery) use ($today) {
                                $subquery->whereNotNull('date_start_res')
                                    ->whereDate('date_end_res', '=', $today);
                            });
                    })

                    ->get();

            return $this->sendResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function getCourseAvailability($course)
    {
        if (!$course) {
            return null; // o manejar como prefieras
        }

        $totalBookings = 0;
        $totalAvailablePlaces = 0;

        if ($course->course_type == 1) {
            // Cursos de tipo 1
            foreach ($course->courseDates as $courseDate) {
                foreach ($courseDate->courseSubgroups as $subgroup) {
                    $bookings = $subgroup->bookingUsers()->count();
                    $totalBookings += $bookings;
                    $totalAvailablePlaces += max(0, $subgroup->max_participants - $bookings);
                }
            }
        } else {
            // Cursos de tipo 2
            foreach ($course->courseDates as $courseDate) {
                $bookings = $courseDate->bookingUsers()->count();
                $totalBookings += $bookings;
            }
            $totalAvailablePlaces = max(0, $course->max_participants - $totalBookings);
        }

        return [
            'total_reservations' => $totalBookings,
            'total_available_places' => $totalAvailablePlaces
        ];
    }


    /**
     * @OA\Get(
     *      path="/slug/courses/{id}",
     *      summary="getCourseWithBookings",
     *      tags={"BookingPage"},
     *      description="Get Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
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
     *                  ref="#/components/schemas/Course"
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
        $today = now(); // Obtener la fecha actual
        // Comprueba si el cliente principal tiene booking_users asociados con el ID del monitor
        $course = Course::with([
            'bookingUsers.client.sports',
            'courseExtras',
            'courseDates.courseGroups' => function ($query) {
                $query->with(['courseSubgroups' => function ($subQuery) {
                    $subQuery->withCount('bookingUsers')->with('degree');
                }]);
            }
        ])->where('school_id', $this->school->id)
            ->where('online', 1)
            ->where('active', 1)
            ->where(function($query) use ($today) {
                $query->where(function($subquery) use ($today) {
                    $subquery->whereNull('date_start_res')
                        ->whereNull('date_end_res');
                })
                    ->orWhere(function($subquery) use ($today) {
                        $subquery->whereDate('date_start_res', '<=', $today)
                            ->whereDate('date_end_res', '>=', $today);
                    })
                    ->orWhere(function($subquery) use ($today) {
                        $subquery->whereDate('date_start_res', '=', $today)
                            ->whereNotNull('date_end_res');
                    })
                    ->orWhere(function($subquery) use ($today) {
                        $subquery->whereNotNull('date_start_res')
                            ->whereDate('date_end_res', '=', $today);
                    });
            })
        ->find($id);

        if (empty($course)) {
            return $this->sendError('Course does not exist in this school');
        } else {
            $availableDegreeIds = collect();
            $unAvailableDegreeIds = collect();
            foreach ($course->courseDates as $courseDate) {
                foreach ($courseDate->courseGroups as $group) {
                    $group->courseSubgroups = $group->courseSubgroups->filter(function ($subgroup) use ($availableDegreeIds, $unAvailableDegreeIds, $group) {
                        $hasAvailability = $subgroup->booking_users_count < $subgroup->max_participants;

                        // Crear la estructura de datos del degree
                        $availableDegree = [
                            'degree_id' => $group->degree_id,
                            'recommended_age' => $group->recommended_age,
                            'age_max' => $group->age_max,
                            'age_min' => $group->age_min
                        ];

                        // Registrar disponibilidad o no disponibilidad
                        if ($hasAvailability) {
                            if (!$availableDegreeIds->contains($availableDegree)) {
                                $availableDegreeIds->push($availableDegree);
                            }
                        } else {
                            if (!$unAvailableDegreeIds->contains($availableDegree)) {
                                $unAvailableDegreeIds->push($availableDegree);
                            }
                        }

                        return $hasAvailability;
                    });

                    // Verificar si todos los subgrupos han sido rechazados
                    if ($group->courseSubgroups->isEmpty()) {
                        $courseDate->courseGroups = $courseDate->courseGroups->reject(function ($g) use ($group) {
                            return $g->id === $group->id;
                        });
                    }
                }
            }


            $uniqueDegrees = $availableDegreeIds->unique(function ($item) {
                return $item['degree_id'] . '-' . $item['recommended_age'];
            });

            $availableDegrees = $uniqueDegrees->map(function ($item) {
                $degree = Degree::find($item['degree_id']);
                if ($degree) {
                    $degree->load('degreesSchoolSportGoals');
                    $degree->recommended_age = $item['recommended_age'];
                    $degree->age_max = $item['age_max'];
                    $degree->age_min = $item['age_min'];
                }
                return $degree;
            })->filter();

            $course->availableDegrees = $availableDegrees;
        }

        return $this->sendResponse($course,
            'Course retrieved successfully');
    }

    public function getDurationsAvailableByCourseDateAndStart($id, Request $request): JsonResponse
    {
        $courseDate = CourseDate::with('course')->find($id);

        if (!$courseDate) {
            return $this->sendError('Invalid course date ID.');
        }

        $course = $courseDate->course;
        $startTime = $request->hour_start;
        $endTime = $courseDate->hour_end; // Hora máxima del curso
        $availableDurations = [];

        if (!$startTime || !strtotime($startTime)) {
            return $this->sendError('Invalid start time.');
        }

        // Verificar si es un día festivo
        if ($this->isHoliday($this->school->id, $courseDate->date)) {
            return $this->sendResponse([], 'No availability: holiday.');
        }

        // Buscar monitores activos para la escuela y el rango de fechas
        $monitors = $this->getActiveMonitorsForSchool($this->school->id, $courseDate->date, $startTime, $endTime);

        if ($monitors->isEmpty()) {
            return $this->sendResponse([], 'No monitors available.');
        }

        // Cursos con duración fija
        if (!$course->is_flexible) {
            $durationInSeconds = $this->convertDurationToSeconds($course->duration);
            $endTimeForFixed = $this->addSecondsToTime($startTime, $durationInSeconds);

            // Comprobar si la hora final está dentro de la hora máxima
            if (strtotime($endTimeForFixed) <= strtotime($endTime)) {
                if ($this->areMonitorsAvailable($monitors, $courseDate->date, $startTime, $endTimeForFixed)) {
                    $availableDurations[] = $this->convertSecondsToHourFormat($durationInSeconds);
                }
            }
        } else {
            // Cursos flexibles
            foreach ($course->price_range as $price) {
                foreach ($price as $participants => $priceValue) {
                    if (is_numeric($priceValue)) {
                        $intervalInSeconds = $this->convertDurationRangeToSeconds($price['intervalo']);
                        $endTimeForFlexible = $this->addSecondsToTime($startTime, $intervalInSeconds);

                        // Comprobar si la hora final está dentro de la hora máxima
                        if (strtotime($endTimeForFlexible) <= strtotime($endTime)) {
                            if ($this->areMonitorsAvailable($monitors, $courseDate->date, $startTime, $endTimeForFlexible)) {
                                $availableDurations[] = $this->convertSecondsToHourFormat($intervalInSeconds);
                            }
                        }
                    }
                }
            }
        }

        $uniqueDurations = array_unique($availableDurations);

        if (empty($uniqueDurations)) {
            return $this->sendResponse([], 'No availability.');
        }

        return $this->sendResponse($uniqueDurations, 'Available durations fetched successfully.');
    }

// Método auxiliar para comprobar días festivos
    private function isHoliday($schoolId, $date): bool
    {
        $season = Season::where('school_id', $schoolId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();



        if (!$season || empty($season->vacation_days)) {
            return false;
        }

        $vacationDays = json_decode($season->vacation_days, true);

        $formattedDate = $date instanceof Carbon ? $date->format('Y-m-d') : (string)$date;

        return in_array($formattedDate, $vacationDays, true);
    }


// Método auxiliar para encontrar monitores activos

    private function getActiveMonitorsForSchool($schoolId, $date, $startTime, $endTime)
    {
        $monitorSchools = MonitorsSchool::with([
            'monitor.sports' => function ($query) use ($schoolId) {
                $query->where('monitor_sports_degrees.school_id', $schoolId);
            },
            'monitor.courseSubgroups' => function ($query) use ($date) {
                $query->whereHas('courseDate', function ($query) use ($date) {
                    $query->whereDate('date', $date);
                });
            }
        ])
            ->where('school_id', $schoolId)
            ->where('active_school', 1)
            ->get();

        return $monitorSchools->pluck('monitor');
    }

// Métodos auxiliares para validaciones

    private function areMonitorsAvailable($monitors, $date, $startTime, $endTime): bool
    {
        foreach ($monitors as $monitor) {
            if (!Monitor::isMonitorBusy($monitor->id, $date, $startTime, $endTime)) {
                return true; // Hay al menos un monitor disponible
            }
        }
        return false; // Ningún monitor está disponible en el rango
    }

    private function addSecondsToTime($time, $seconds)
    {
        $time = strtotime($time);
        return date('H:i:s', $time + $seconds);
    }

    private function convertDurationToSeconds($duration)
    {
        $parts = explode(' ', $duration);
        $hours = 0;
        $minutes = 0;

        foreach ($parts as $part) {
            if (str_contains($part, 'h')) {
                $hours = (int) str_replace('h', '', $part);
            } elseif (str_contains($part, 'min')) {
                $minutes = (int) str_replace('min', '', $part);
            }
        }

        return ($hours * 3600) + ($minutes * 60);
    }

    private function convertDurationRangeToSeconds($duration)
    {
        return $this->convertDurationToSeconds($duration);
    }

    private function convertSecondsToDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = ($seconds % 3600) / 60;

        $result = [];
        if ($hours > 0) {
            $result[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $result[] = $minutes . 'min';
        }

        return implode(' ', $result);
    }

    private function convertSecondsToHourFormat($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = ($seconds % 3600) / 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }



}
