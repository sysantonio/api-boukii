<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Mail\BookingInfoUpdateMailer;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Monitor;
use App\Repositories\CourseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class CourseController extends AppBaseController
{

    private $courseRepository;

    public function __construct(CourseRepository $courseRepo)
    {
        $this->courseRepository = $courseRepo;
    }


    /**
     * @OA\Get(
     *      path="/admin/courses",
     *      summary="getCourseList",
     *      tags={"Admin"},
     *      description="Get all Courses",
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
        $courses = $this->courseRepository->all(
            searchArray: $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            search: $request->get('search'),
            skip: $request->get('skip'),
            limit: $request->get('limit'),
            pagination: $request->get('perPage'),
            with: $request->get('with',  ['station', 'sport',
                'courseDates.courseGroups.courseSubgroups.bookingUsers',
                'courseExtras']),
            order: $request->get('order', 'desc'),
            orderColumn: $request->get('orderColumn', 'id'),
            additionalConditions: function ($query) use ($request) {
                // Obtén el ID de la escuela y añádelo a los parámetros de búsqueda
                $school = $this->getSchool($request);
                $query->where('school_id', $school->id);

                $query->when($request->has('sport_id') && is_array($request->sport_id), function ($query) use ($request) {
                    $query->whereIn('sport_id', $request->sport_id);
                });

                $query->when($request->has('finished') && $request->finished, function ($query) {
                    $today = now()->format('Y-m-d');
                    $query->whereDoesntHave('courseDates', function ($subquery) use ($today) {
                        $subquery->where('date', '>=', $today);
                    });
                });

                $query->when($request->has('active'), function ($query) {
                    $today = now()->format('Y-m-d');
                    $query->whereDoesntHave('courseDates', function ($subquery) use ($today) {
                        $subquery->where('date', '<=', $today);
                    });
                });

                // Agregar condiciones para el rango de fechas
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $query->whereHas('courseDates', function ($subquery) use ($startDate, $endDate) {
                        $subquery->whereBetween('date', [$startDate, $endDate]);
                    });
                }
            }
        );

        // Calcula reservas y plazas disponibles para cada curso
        foreach ($courses as $course) {
            $availability = $this->getCourseAvailability($course);
            $course->total_reservations = $availability['total_reservations'];
            $course->total_available_places = $availability['total_available_places'];
        }

        return $this->sendResponse($courses, 'Courses retrieved successfully');
    }

    public function getCourseAvailability($course)
    {
        if (!$course) {
            return null; // o manejar como prefieras
        }

        $totalMonitors = Monitor::whereHas('monitorsSchools', function ($query) use ($course) {
            $query->where('school_id', $course['school_id'])->where('active_school', 1);

        })->count();
        $totalBookings = 0;
        $totalAvailablePlaces = 0;
        $totalPlaces = 0;

        if ($course->course_type == 1) {
            // Cursos de tipo 1
            foreach ($course->courseDates as $courseDate) {
                foreach ($courseDate->courseSubgroups as $subgroup) {
                    $bookings = $subgroup->bookingUsers()->where('status', 1)->count();
                    $totalBookings += $bookings;
                    $totalPlaces += $subgroup->max_participants;
                    $totalAvailablePlaces += max(0, $subgroup->max_participants - $bookings);
                }
            }
        } else {
            // Cursos de tipo 2
            $totalIntervals = 0;
            foreach ($course->courseDates as $courseDate) {
                $bookings = $courseDate->bookingUsers()->where('status', 1)->count();
                $totalBookings += $bookings;
                // Si es flexible, contar los intervalos disponibles
                if ($course->is_flexible && $course->price_range) {
                    foreach ($course->price_range as $price) {
                        foreach ($price as $participants => $priceValue) {
                            $priceValue = str_replace(',', '.', $priceValue);
                            if (is_numeric($priceValue)) {
                                // Obtener la duración del intervalo en segundos
                                $intervalInSeconds = $this->convertDurationRangeToSeconds($price['intervalo']);
                                $start = strtotime($courseDate->hour_min);
                                $end = strtotime($courseDate->hour_max);
                                if($start && $end) {
                                    //  $totalIntervals = 0;
                                    while ($start < $end) {
                                        $totalIntervals++;
                                        $start += $intervalInSeconds;
                                    }
                                } else{
                                    $totalIntervals = 5;
                                }
                                // Calcular el número de intervalos disponibles
                                $totalAvailablePlaces += $totalIntervals;
                                $totalPlaces += $totalIntervals;
                                // Romper el bucle una vez que se encuentre un precio numérico
                                break 2;
                            }
                        }
                    }
                    $totalPlaces += $totalIntervals;
                    $totalAvailablePlaces += $totalIntervals;
                } else {
                    // Si no es flexible, calcular el número de intervalos disponibles en función de la duración del curso
                    $start = strtotime($courseDate->hour_min);
                    $end = strtotime($courseDate->hour_max);
                    $durationInSeconds = $this->convertDurationToSeconds($course->duration); // Convertir la duración a segundos
                    if($start && $end) {
                      //  $totalIntervals = 0;
                        while ($start < $end) {
                            $totalIntervals++;
                            $start += $durationInSeconds;
                        }
                    } else{
                        $totalIntervals = 5;
                    }

                    $totalAvailablePlaces += $totalIntervals;
                    $totalPlaces += $totalIntervals;
                }
            }

            $totalAvailablePlaces = max(0, $totalAvailablePlaces - $totalBookings);
        }

        return [
            'total_reservations' => $totalBookings,
            'total_available_places' => $totalAvailablePlaces,
            'total_places' => $totalPlaces
        ];
    }

    private function convertDurationToSeconds($duration)
    {
        if (strpos($duration, 'h') !== false) {
            // Si el formato es "Xh Ymin", convertirlo a segundos
            preg_match('/(\d+)h (\d+)min/', $duration, $matches);
            $hours = intval($matches[1]);
            $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Si no hay minutos, establecer en 0
            return ($hours * 3600) + ($minutes * 60);
        } elseif (strpos($duration, 'min') !== false) {
            // Si el formato es solo "Ymin", convertirlo a segundos
            preg_match('/(\d+)min/', $duration, $matches);
            $minutes = intval($matches[1]);
            return $minutes * 60;
        } else {
            // Si el formato es "HH:mm:ss", convertirlo a segundos
            $time = explode(':', $duration);
            $hours = intval($time[0]);
            $minutes = intval($time[1]);
            $seconds = intval($time[2]);
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }
    }

    private function convertDurationRangeToSeconds($duration)
    {
        if (strpos($duration, 'h') !== false) {
            // Si el formato es "Xh Ymin" o "Xh", convertirlo a segundos
            preg_match('/(\d+)h(?: (\d+)min)?/', $duration, $matches);
            if (!empty($matches[1])) {
                $hours = intval($matches[1]);
                $minutes = isset($matches[2]) ? intval($matches[2]) : 0; // Si no hay minutos, establecer en 0
                return ($hours * 3600) + ($minutes * 60);
            }
        } elseif (strpos($duration, 'min') !== false) {
            // Si el formato es solo "Ymin", convertirlo a segundos
            preg_match('/(\d+)min/', $duration, $matches);
            if (!empty($matches[1])) {
                $minutes = intval($matches[1]);
                return $minutes * 60;
            }
        }

        // Si no se pudo convertir, devolver 0 segundos
        return 0;
    }


    /**
     * @OA\Get(
     *      path="/admin/courses/{id}",
     *      summary="getCourseWithBookings",
     *      tags={"Admin"},
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
        $school = $this->getSchool($request);

        // Comprueba si el cliente principal tiene booking_users asociados con el ID del monitor
        $course = Course::with( 'station','bookingUsers.client.sports',
            'courseDates.courseGroups.courseSubgroups.monitor',
            'courseDates.courseGroups.courseSubgroups.bookingUsers')
            ->where('school_id', $school->id)->find($id);

        if (empty($course)) {
            return $this->sendError('Course does not exist in this school');
        }

        $availability = $this->getCourseAvailability($course);
        $course->total_reservations = $availability['total_reservations'];
        $course->total_available_places = $availability['total_available_places'];

        return $this->sendResponse($course, 'Course retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/admin/courses",
     *      summary="createCourse",
     *      tags={"Admin"},
     *      description="Create Course",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
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
    public function store(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $request->merge(["school_id"=> $school->id]);

        try {
            $request->validate([
                'course_type' => 'required',
                'is_flexible' => 'required',
                'sport_id' => 'required|exists:sports,id',
                'school_id' => 'required|exists:schools,id',
                'station_id' => 'nullable|exists:stations,id',
                'name' => 'required|string|max:65535',
                'short_description' => 'required|string|max:65535',
                'description' => 'required|string|max:65535',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'max_participants' => 'required|integer|min:1',
                'duration' => 'nullable',
                'date_start' => 'required|date',
                'date_end' => 'required|date|after_or_equal:date_start',
                'date_start_res' => 'nullable|date',
                'date_end_res' => 'nullable|date|after_or_equal:date_start_res',
                'confirm_attendance' => 'required|boolean',
                'active' => 'required|boolean',
                'online' => 'required|boolean',
                'image' => 'nullable|string',
                'translations' => 'nullable|string',
                'price_range' => 'nullable',
                'discounts' => 'nullable|string',
                'settings' => 'nullable|string',
                'course_dates' => 'required|array',
                'course_dates.*.date' => 'required|date',
                'course_dates.*.hour_start' => 'required|string|max:255',
                'course_dates.*.hour_end' => 'required|string|max:255',
                'course_dates.*.groups' => 'required_if:course_type,1|array',
                'course_dates.*.groups.*.degree_id' => 'required|exists:degrees,id',
                'course_dates.*.groups.*.age_min' => 'nullable|integer|min:0',
                'course_dates.*.groups.*.age_max' => 'nullable|integer|min:0',
                'course_dates.*.groups.*.recommended_age' => 'nullable|integer|min:0',
                'course_dates.*.groups.*.teachers_min' => 'nullable|integer|min:1',
                'course_dates.*.groups.*.teachers_max' => 'nullable|integer|min:1',
                'course_dates.*.groups.*.observations' => 'nullable|string|max:65535',
                'course_dates.*.groups.*.teacher_min_degree' => 'nullable|exists:degrees,id',
                'course_dates.*.groups.*.subgroups' => 'required|array',
                'course_dates.*.groups.*.subgroups.*.degree_id' => 'required|exists:degrees,id',
                'course_dates.*.groups.*.subgroups.*.monitor_id' => 'nullable|exists:monitors,id',
                'course_dates.*.groups.*.subgroups.*.max_participants' => 'nullable|integer|min:0',
            ]);

            $courseData = $request->all();

            if(!empty($courseData['image'])) {
                $base64Image = $request->input('image');

                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]);
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        $this->sendError('base64_decode failed');
                    }
                } else {
                    $this->sendError('did not match data URI with image data');
                }

                $imageName = 'course/image_'.time().'.'.$type;
                Storage::disk('public')->put($imageName, $imageData);
                $courseData['image'] = url(Storage::url($imageName));
            }

            DB::beginTransaction();

            $course = Course::create($courseData);

            // Crear las fechas y grupos
            if (isset($courseData['course_dates'])) {
                $settings = isset($courseData['settings']) ? json_decode($courseData['settings'], true) : null;
                $weekDays = $settings ? $settings['weekDays'] : null;

                foreach ($courseData['course_dates'] as $dateData) {
                    if ($weekDays) {
                        $date = new \DateTime($dateData['date']);
                        $dayOfWeek = strtolower($date->format('l')); // Get the day of the week in lowercase

                        if (isset($weekDays[$dayOfWeek]) && $weekDays[$dayOfWeek]) {
                            $date = $course->courseDates()->create($dateData);
                        } else {
                            continue; // Skip this date since it's not in the specified weekdays
                        }
                    } else {
                        $date = $course->courseDates()->create($dateData);
                    }

                    if (isset($dateData['groups'])) {
                        foreach ($dateData['groups'] as $groupData) {
                            $groupData['course_id'] = $course->id;
                            $group = $date->courseGroups()->create($groupData);

                            if (isset($groupData['subgroups'])) {
                                foreach ($groupData['subgroups'] as &$subgroup) {
                                    $subgroup['course_id'] = $course->id;
                                    $subgroup['course_date_id'] = $date->id;
                                }

                                $group->courseSubgroups()->createMany($groupData['subgroups']);
                            }
                        }
                    }
                }
            }
            DB::commit();
            return $this->sendResponse($course,'Curso creado con éxito');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('An error occurred while creating the course: ' . $e->getMessage());
        }

    }

    /**
     * @OA\Put(
     *      path="/admin/courses/{id}",
     *      summary="updateCourse",
     *      tags={"Admin"},
     *      description="Update Course",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Course",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
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
    public function update($id, Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        try {
            $emailGroups = [];
            $courseData = $request->all();
            $course = Course::findOrFail($id); // Suponiendo que tienes el ID del curso que deseas editar

            if(!empty($courseData['image'])) {
                $base64Image = $request->input('image');

                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]);
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        $this->sendError('base64_decode failed');
                    }
                } else {
                    $this->sendError('did not match data URI with image data');
                }

                $imageName = 'course/image_'.time().'.'.$type;
                Storage::disk('public')->put($imageName, $imageData);
                $courseData['image'] = url(url(Storage::url($imageName)));
            } else {
                $courseData = $request->except('image');
            }

            DB::beginTransaction();
            // Actualiza los campos principales del curso
            $course->update($courseData);

            // Sincroniza las fechas
            if (isset($courseData['course_dates'])) {
                $updatedCourseDates = [];
                foreach ($courseData['course_dates'] as $dateData) {
                    // Verifica si existe 'id' antes de usarlo
                    if (isset($dateData['active']) && $dateData['active'] === false && isset($dateData['id'])) {
                        $date = CourseDate::findOrFail($dateData['id']);
                        $bookingUsersCount = $date->bookingUsers()->count();

                        if ($bookingUsersCount > 0) {
                            DB::rollback();
                            return $this->sendError('Date has bookings and cannot be deactivated');
                        }
                    }
                    if (isset($dateData['date']) && isset($dateData['id'])) {
                        $date = CourseDate::find($dateData['id']);
                        if ($date) {
                            $providedDate = $dateData['date'];

                            // Verificar si la fecha ya está en el formato 'Y-m-d'
                            if (strpos($providedDate, 'T') !== false) {
                                // Convierte la fecha del formato 'Y-m-d\TH:i:s.u\Z' a 'Y-m-d'
                                $providedDate = date_create_from_format('Y-m-d\TH:i:s.u\Z', $providedDate);

                                if ($providedDate) {
                                    $providedDate = $providedDate->format('Y-m-d');
                                }
                            }

                            $modelDate = $date->date->format('Y-m-d');

                            if ($providedDate && $providedDate !== $modelDate) {

                                $bookingUsers = $date->bookingUsers;
                                foreach ($bookingUsers as $bookingUser) {
                                    $clientEmail = $bookingUser->booking->clientMain->email;
                                    $bookingId = $bookingUser->booking_id;

                                    $bookingUser->update(['date' => $providedDate]);

                                    if (array_key_exists($clientEmail, $emailGroups)) {
                                        // Verificar si el booking ID ya está en el grupo del correo electrónico
                                        if (!in_array($bookingId, $emailGroups[$clientEmail])) {
                                            // Si no está, agregarlo al grupo del correo electrónico
                                            $emailGroups[$clientEmail][] = $bookingId;
                                        }
                                    } else {
                                        // Si el correo electrónico no está en el array, crear un nuevo grupo
                                        $emailGroups[$clientEmail] = [$bookingId];
                                    }
                                }
                            }
                        }
                    }

                    $dateId = isset($dateData['id']) ? $dateData['id'] : null;
                    $date = $course->courseDates()->updateOrCreate(['id' => $dateId], $dateData);
                    $updatedCourseDates[] = $date->id;

                    if (isset($dateData['course_groups'])) {
                        $updatedCourseGroups = [];
                        foreach ($dateData['course_groups'] as $groupData) {
                            // Verifica si existe 'id' antes de usarlo
                            $groupId = isset($groupData['id']) ? $groupData['id'] : null;
                            $group = $date->courseGroups()->updateOrCreate(['id' => $groupId], $groupData);
                            $updatedCourseGroups[] = $group->id;

                            if (isset($groupData['course_subgroups'])) {
                                foreach ($groupData['course_subgroups'] as $subgroupData) {
                                    // Preparar los datos de subgroup
                                    $subgroupData['course_id'] = $course->id;
                                    $subgroupData['course_date_id'] = $date->id;

                                    // Verifica si existe 'id' antes de usarlo
                                    $subgroupId = isset($subgroupData['id']) ? $subgroupData['id'] : null;
                                    if ($subgroupId) {
                                        $group->courseSubgroups()->updateOrCreate(['id' => $subgroupId], $subgroupData);
                                    } else {
                                        $group->courseSubgroups()->create($subgroupData);
                                    }
                                }
                                // Considera si necesitas borrar subgrupos aquí
                            }
                        }
                        $date->courseGroups()->whereNotIn('id', $updatedCourseGroups)->delete();
                    }
                }
                $course->courseDates()->whereNotIn('id', $updatedCourseDates)->delete();
            }
            DB::commit();

            // Ahora, recorre el array de grupos de correo electrónico y envía correos
            foreach ($emailGroups as $clientEmail => $bookingIds) {
                foreach ($bookingIds as $bookingId) {
                    // Obtener el booking asociado a este correo electrónico y booking ID
                    $booking = Booking::with('clientMain')->find($bookingId);

                    // Envía el correo electrónico aquí usando Laravel Mail


                    dispatch(function () use ($school, $booking, $clientEmail) {
                        // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                        try {
                            Mail::to($clientEmail)->send(new BookingInfoUpdateMailer($school,
                                $booking, $booking->clientMain));
                        } catch (\Exception $ex) {
                            \Illuminate\Support\Facades\Log::debug('Admin/COurseController BookingInfoUpdateMailer: ' .
                                $ex->getMessage());
                        }
                    })->afterResponse();
                }
            }

            return $this->sendResponse($course, 'Course updated successfully');
        }  catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('An error occurred while updating the course: ' . $e->getMessage());
        }

    }

}
