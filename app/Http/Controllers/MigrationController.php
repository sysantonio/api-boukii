<?php

namespace App\Http\Controllers;


use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\ClientSport;
use App\Models\ClientsSchool;
use App\Models\ClientsUtilizer;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\CourseGroup;
use App\Models\CourseSubgroup;
use App\Models\Degree;
use App\Models\DegreesSchoolSportGoal;
use App\Models\Language;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorSportAuthorizedDegree;
use App\Models\MonitorSportsDegree;
use App\Models\MonitorsSchool;
use App\Models\OldModels\Bookings2;
use App\Models\OldModels\Course2;
use App\Models\OldModels\CourseGroups2;
use App\Models\OldModels\CourseGroupsSubgroups2;
use App\Models\OldModels\DegreeSchoolSport;
use App\Models\OldModels\DegreeSchoolSportGoals;
use App\Models\OldModels\Evaluation;
use App\Models\OldModels\SchoolColor;
use App\Models\OldModels\SchoolSalaryLevels;
use App\Models\OldModels\SchoolSports;
use App\Models\OldModels\StationSchools;
use App\Models\OldModels\Task;
use App\Models\OldModels\TaskCheck;
use App\Models\OldModels\User;
use App\Models\OldModels\UserGroups;
use App\Models\OldModels\UserNwd;
use App\Models\OldModels\UserSport;
use App\Models\OldModels\UserSportAuthorizedDegrees;
use App\Models\OldModels\Voucher;
use App\Models\OldModels\VoucherLog;
use App\Models\School;
use App\Models\SchoolSalaryLevel;
use App\Models\SchoolSport;
use App\Models\SchoolUser;
use App\Models\ServiceType;
use App\Models\Sport;
use App\Models\SportType;
use App\Models\Station;
use App\Models\StationsSchool;
use App\Models\VouchersLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class MigrationController extends AppBaseController
{

    public function __construct()
    {

    }

    public function migrateAll(Request $request): JsonResponse
    {

        $this->migrateInitalData($request);
        $this->migrateClients($request);
        $this->migrateMonitors($request);
        $this->migrateUsersSchools($request);
        $this->migrateCourses($request);
        $this->migrateBookings($request);

        return $this->sendResponse('Datos totales guardados correctamente', 200);
    }

    public function migrateInitalData(Request $request): JsonResponse
    {

        $langugages = DB::connection('old')->table('languages')->get();
        DB::statement('ALTER TABLE languages AUTO_INCREMENT = 1;');
        foreach ($langugages as $langugage) {
            $newLangugage = new Language((array)$langugage);
            $newLangugage->save();

        }

        $oldSportsTypes = DB::connection('old')->table('sport_types')->get();
        DB::statement('ALTER TABLE sport_types AUTO_INCREMENT = 1;');
        foreach ($oldSportsTypes as $oldSportsType) {


            $newSportType = new SportType((array)$oldSportsType);
            $newSportType->save();
            //  return $this->sendResponse($newSport, 200);
        }


        $oldSports = DB::connection('old')->table('sports')->get();
        DB::statement('ALTER TABLE sports AUTO_INCREMENT = 1;');
        foreach ($oldSports as $oldSport) {
            $newSport = new Sport((array)$oldSport);
            $newSport->save();
            //  return $this->sendResponse($newSport, 200);
        }

        $oldServiceTypes = DB::connection('old')->table('service_type')->get();
        foreach ($oldServiceTypes as $oldServiceType) {
            $newServiceType = new ServiceType((array)$oldServiceType);
            $newServiceType->save();
            //   return $this->sendResponse($newServiceType, 200);
        }


        $oldStations = DB::connection('old')->table('stations')->get()->toArray();
        DB::statement('ALTER TABLE stations AUTO_INCREMENT = 1;');
        foreach ($oldStations as $oldStation) {
            // Reemplaza con la obtención real de tu modelo
            $newStation = new Station([
                'name' => $oldStation->name,
                'address' => $oldStation->address,
                'cp' => $oldStation->cp,
                'city' => $oldStation->city,
                'country' => $oldStation->country_id,
                'province' => $oldStation->province_id,
                'latitude' => $oldStation->latitude,
                'longitude' => $oldStation->longitude,
                'image' => $oldStation->image,
                'map' => $oldStation->map,
                'num_hanger' => $oldStation->percha,
                'num_chairlift' => $oldStation->telesilla,
                'num_cabin' => $oldStation->cabina,
                'num_cabin_large' => $oldStation->cabina_grande,
                'num_fonicular' => $oldStation->fonicular,
                'show_details' => $oldStation->show_details,
                'active' => $oldStation->active,
                'old_id' => $oldStation->id  // Asumiendo que quieres guardar el antiguo ID en un campo 'old_id'
            ]);

            $newStation->save();
            //  return $this->sendResponse($newStation, 200);
        }

        $oldSchools = DB::connection('old')->table('schools')->get();
        $oldDegrees = \App\Models\OldModels\Degree::all();

        foreach ($oldSchools as $oldSchool) {
            DB::statement('ALTER TABLE schools AUTO_INCREMENT = 1;');
            $newSchool = new School((array)$oldSchool);
            $newSchool->save();
            $oldDegreesSchool = DegreeSchoolSport::where('school_id', $oldSchool->id)->get();
            foreach ($oldDegreesSchool as $oldDegreeSchool) {
                $newDegree = new Degree();
                $oldDegree = $oldDegrees->firstWhere('id', $oldDegreeSchool->degree_id);
                $newDegree->school_id = $newSchool['id'];
                $newDegree->sport_id = $oldDegreeSchool->sport_id;
                $newDegree->annotation = $oldDegreeSchool->annotation;
                $newDegree->name = $oldDegreeSchool->name ?? '';
                $newDegree->league = $oldDegree->league;
                $newDegree->level = $oldDegree->level;
                $newDegree->degree_order = $oldDegree->degree_order;
                $newDegree->progress = $oldDegree->progress;
                $newDegree->color = $oldDegree->color;
                $newDegree->age_min = 1;
                $newDegree->age_max = 99;
                $newDegree->save();

                $oldGoals =
                    DegreeSchoolSportGoals::where('degrees_school_sport_id', $oldDegreeSchool->id)->get()->toArray();
                foreach ($oldGoals as $oldGoal) {
                    $newGoal = new DegreesSchoolSportGoal((array)$oldGoal);
                    $newGoal->degree_id = $newDegree->id;
                    //  $newGoal->school_id = $newSchool['id'];
                    $newGoal->save();
                    // return $this->sendResponse($newGoal, 200);
                }

                //return $this->sendResponse($oldGoals, 200);
            }
            $oldSchoolColors = SchoolColor::where('school_id', $oldSchool->id)->get()->toArray();

            foreach ($oldSchoolColors as $oldSchoolColor) {
                $newSchoolColor = new \App\Models\SchoolColor($oldSchoolColor);
                $newSchoolColor->school_id = $newSchool['id'];
                $newSchoolColor->save();
                //  return $this->sendResponse($newSchoolColor, 200);
            }

            $oldSchoolSalaryLevels = SchoolSalaryLevels::where('school_id', $oldSchool->id)->get()->toArray();

            foreach ($oldSchoolSalaryLevels as $oldSchoolSalaryLevel) {
                $newSchoolSalaryLevel = new SchoolSalaryLevel($oldSchoolSalaryLevel);
                $newSchoolSalaryLevel->school_id = $newSchool['id'];
                $newSchoolSalaryLevel->save();
                // return $this->sendResponse($newSchoolSalaryLevel, 200);
            }

            $oldSchoolsStations = StationSchools::where('school_id', $oldSchool->id)->get()->toArray();

            foreach ($oldSchoolsStations as $oldSchoolsStation) {
                $newSchoolStation = new StationsSchool($oldSchoolsStation);
                $newSchoolStation->school_id = $newSchool['id'];
                $newSchoolStation->save();
                // return $this->sendResponse($newSchoolStation, 200);
            }


            $oldSchoolSports = SchoolSports::where('school_id', $oldSchool->id)->get()->toArray();

            foreach ($oldSchoolSports as $oldSchoolSport) {
                $newSchoolSport = new SchoolSport($oldSchoolSport);
                $newSchoolSport->school_id = $newSchool['id'];
                $newSchoolSport->save();
                //  return $this->sendResponse($newSchoolSport, 200);
            }

            $oldTasks = Task::where('school_id', $oldSchool->id)->get()->toArray();

            foreach ($oldTasks as $oldTask) {
                $newTask = new \App\Models\Task($oldTask);
                $newTask['school_id'] = $newSchool['id'];

                $newTask->save();
                $oldTasksChecks = TaskCheck::where('task_id', $oldTask['id'])->get()->toArray();
                foreach ($oldTasksChecks as $oldTasksCheck) {
                    $newTasksCheck = new \App\Models\TaskCheck($oldTasksCheck);
                    $newTasksCheck->task_id = $newTask['id'];
                    $newTasksCheck->save();

                }
                $newSchoolStation->save();

            }

        }
        return $this->sendResponse('Datos iniciales guardados correctamente', 200);
    }

    public function migrateClients(Request $request): JsonResponse
    {
        $clientTypeId = 2;

        //Listamos todos los usuarios con tipo de cliente:

        $oldUsers = DB::connection('old')->table('users')->where('user_type', $clientTypeId)
            ->get();

        foreach ($oldUsers as $oldUser) {
            // Reemplaza con la obtención real de tu modelo
            $oldClient = User::withTrashed()->find($oldUser->id); // Reemplaza con la obtención real de tu modelo
            $newClient = new Client($oldClient->toArray());
            $newClient->country = $oldClient->country_id;
            $newClient->old_id = $oldClient->id;

            $user = new \App\Models\User($oldClient->toArray());
            $user->type = 2;
            $user->save();
            $newClient->user_id = $user->id;
            $newClient->save();
            //$user->save();
            $oldClient->load('schools');

            foreach ($oldClient->schools as $school) {
                $clientSchool = new ClientsSchool(['client_id' => $newClient->id, 'school_id' => $school->id]);
                $clientSchool->save();
            }

            $oldDegrees = \App\Models\OldModels\Degree::all();
            $oldUsersSports = UserSport::where('user_id', $oldUser->id)->whereNotNull('school_id')->get();


            foreach ($oldUsersSports as $oldUserSport) {
                $newClientSport = new ClientSport($oldUserSport->toArray());
                $oldDegree = $oldDegrees->firstWhere('id', $oldUserSport->degree_id);
                $newDegree = Degree::where('degree_order', $oldDegree->degree_order)
                    ->where('school_id', $oldUserSport->school_id)->where('sport_id', $oldUserSport->sport_id)
                    ->first();
                $newClientSport->degree_id = $newDegree->id;
                $newClientSport->client_id = $newClient->id;

                $newClientSport->save();
            }
        }

        $oldUtilizersMain = UserGroups::all();

        foreach ($oldUtilizersMain as $oldUtilizers) {
            $clientId = Client::where('old_id', $oldUtilizers->user_main_id)->first();
            $secondaryId = Client::where('old_id', $oldUtilizers->user_secondary_id)->first();
            $newClientUtilizer = new ClientsUtilizer(['main_id' => $clientId->id, 'client_id' => $secondaryId->id]);
            $newClientUtilizer->save();
        }

        $oldVouchers = Voucher::all();

        foreach ($oldVouchers as $oldVoucher) {
            $client = Client::where('old_id', $oldVoucher->user_id)->first();
            $newVoucher = new \App\Models\Voucher([
                'code' => $oldVoucher->code,
                'quantity' => $oldVoucher->quantity,
                'remaining_balance' => $oldVoucher->remaining_balance,
                'payed' => $oldVoucher->payed,
                'client_id' => $client->id,
                'school_id' => $oldVoucher->school_id,
                'payrexx_reference' => $oldVoucher->payrexx_reference,
                'payrexx_transaction' => $oldVoucher->payrexx_transaction,
                'old_id' => $oldVoucher->id
            ]);

            $newVoucher->save();


        }

        /** EVALUACIONES NO PARECE NECESARIO, HAY 40 VACIAS */
        /*$oldEvalutations = Evaluation::all();

        foreach ($oldEvalutations as $oldEvalutation) {
            $mainId = Client::where('old_id', $oldEvalutation->user_id)->select('id');
            $degreeId = Degree::where('old_id', $oldEvalutation->degrees_school_sport_id)->select('id');
            $newEvaluation = new \App\Models\Evaluation(['main_id' => $mainId, 'degree_id' => $degreeId,
                'observations' => $oldEvalutation->observations]);
            //$newEvaluation->save();
        }*/


        return $this->sendResponse('Imported clients correctly', 200);

    }

    public function migrateMonitors(Request $request): JsonResponse
    {
        $clientTypeId = 3;

        //Listamos todos los usuarios con tipo de cliente:

        $oldUsers = DB::connection('old')->table('users')->where('user_type', $clientTypeId)->get();

        foreach ($oldUsers as $oldUser) {
            // Reemplaza con la obtención real de tu modelo
            $oldMonitor = User::find($oldUser->id); // Reemplaza con la obtención real de tu modelo
            $newMonitor = new Monitor($oldMonitor->toArray());
            $newMonitor->country = $oldMonitor->country_id;
            $newMonitor->old_id = $oldMonitor->id;

            $user = new \App\Models\User($oldMonitor->toArray());
            $user->type = $clientTypeId;
            $user->save();
            $newMonitor->user_id = $user->id;
            if ($oldMonitor->birth_date == '0000-00-00' || $oldMonitor->birth_date < '1900-01-01') {
                // Establecer la fecha de nacimiento a null o a una fecha válida predeterminada
                $newMonitor->birth_date = '1971-01-01'; // O alguna fecha válida predeterminada
            }
            $newMonitor->save();
            $oldMonitor->load('schools');

            foreach ($oldMonitor->schools as $school) {
                $monitorSchool = new MonitorsSchool(['monitor_id' => $newMonitor->id, 'school_id' => $school->id]);
                $monitorSchool->save();
            }

            $oldDegrees = \App\Models\OldModels\Degree::all();
            $oldUsersSports = UserSport::where('user_id', $oldUser->id)->whereNotNull('school_id')->get();

            foreach ($oldUsersSports as $oldUserSport) {
                $newMonitorSport = new MonitorSportsDegree($oldUserSport->toArray());
                $oldDegree = $oldDegrees->firstWhere('id', $oldUserSport->degree_id);
                $newDegree = Degree::where('degree_order', $oldDegree->degree_order)
                    ->where('school_id', $oldUserSport->school_id)->where('sport_id', $oldUserSport->sport_id)
                    ->first();
                if ($newDegree) {
                    $newMonitorSport->degree_id = $newDegree->id;
                    $newMonitorSport->monitor_id = $newMonitor->id;
                } else {
                    Log::channel('migration')->info('Error no existe el degree en comparacion', $oldDegree);
                }
                $newMonitorSport->save();
                $oldMonitorSportAuthorizedDegrees =
                    UserSportAuthorizedDegrees::where('user_sport_id', $oldUserSport->id)->get();
                foreach ($oldMonitorSportAuthorizedDegrees as $oldMonitorSportAuthorizedDegree) {
                    $newMonitorSportAuthorizedDegree =
                        new MonitorSportAuthorizedDegree(['monitor_sport_id' => $newMonitorSport->id]);
                    $degree = DegreeSchoolSport::find($oldMonitorSportAuthorizedDegree->degree_id);
                    $oldDegree = $oldDegrees->firstWhere('id', $degree->degree_id);
                    $newDegree = Degree::where('degree_order', $oldDegree->degree_order)
                        ->where('school_id', $oldUserSport->school_id)->where('sport_id', $oldUserSport->sport_id)
                        ->first();
                    if ($newDegree) {
                        $newMonitorSportAuthorizedDegree->degree_id = $newDegree->id;
                        $newMonitorSportAuthorizedDegree->save();
                    } else {
                        Log::channel('migration')
                            ->info('Error no existe el degree en comparacion para el montior sport auth', $oldDegree);
                    }
                }
            }
        }

        $oldMonitorNwds = UserNwd::all();

        foreach ($oldMonitorNwds as $oldMonitorNwd) {
            $newMonitor = Monitor::where('old_id', $oldMonitorNwd->user_id)->first();
            $newMonitorNwd = new MonitorNwd($oldMonitorNwd->toArray());
            $newMonitorNwd->monitor_id = $newMonitor->id;
            $newMonitorNwd->save();

        }
        return $this->sendResponse('Imported monitors correctly', 200);
    }

    public function migrateUsersSchools(Request $request): JsonResponse
    {
        $clientTypeId = 1;

        //Listamos todos los usuarios con tipo de cliente:

        $oldUsers = DB::connection('old')->table('users')->where('user_type', $clientTypeId)
            ->get();

        foreach ($oldUsers as $oldUser) {
            $oldClient = User::find($oldUser->id); // Reemplaza con la obtención real de tu modelo
            $user = new \App\Models\User($oldClient->toArray());
            $user->type = $clientTypeId;
            $user->save();

            $oldClient->load('schools');

            foreach ($oldClient->schools as $school) {
                $clientSchool = new SchoolUser(['user_id' => $user->id, 'school_id' => $school->id]);
                $clientSchool->save();
            }
        }
        return $this->sendResponse('Imported users schools correctly', 200);
    }

    public function migrateCourses(Request $request): JsonResponse
    {

        $oldDegrees = \App\Models\OldModels\Degree::all();
        $fechaInicio = Carbon::createFromDate(null, 9, 1);

        $oldCoursesCollectives = Course2::where('created_at', '>', $fechaInicio)
            ->where('deleted_at', null)
            ->whereNotNull('group_id')
            ->with(['global_course', 'course_dates', 'groups.subgroups'])
            ->get()
            ->groupBy('group_id');

        Log::info('Iniciando migración de cursos colectivos');

        foreach ($oldCoursesCollectives as $collective) {
            try {
                $newCourse = new Course($collective[0]->toArray());
                if ($collective[0]->global_course) {
                    $newCourse->date_start = $collective[0]->global_course->date_start_global;
                    $newCourse->date_end = $collective[0]->global_course->date_end_global;
                    $newCourse->name = $collective[0]->global_course->name_global;
                    $newCourse->short_description = $collective[0]->global_course->short_description_global;
                    $newCourse->description = $collective[0]->global_course->description_global;
                    $newCourse->school_id = $collective[0]->school_id;
                    $newCourse->course_type = $collective[0]->course_type_id;
                    $newCourse->is_flexible = $collective[0]->duration_flexible;
                    $newCourse->old_id = $collective[0]->group_id;
                    $base64Image = $collective[0]->image;
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                        $imageData = base64_decode($imageData);
                        $imageName = 'image_' . time() . '.' . $type[1];
                        Storage::disk('public')->put($imageName, $imageData);
                        $newCourse->image = Storage::disk('public')->url($imageName);

                    }
                    $newCourse->save();
                    foreach ($collective[0]->global_course->dates_global as $index => $course_date) {

                        $newCourseDate = new CourseDate($course_date);
                        $startTime =
                            Carbon::createFromFormat('Y-m-d H:i', $course_date['date'] . ' ' . $course_date['hour']);
                        $endTime = $startTime->copy()->addSeconds(strtotime($course_date['duration']) - strtotime('TODAY'));
                        $newCourseDate->hour_end = $endTime->format('H:i');
                        $newCourseDate->hour_start = $course_date['hour'];
                        $newCourseDate->course_id = $newCourse->id;
                        $newCourseDate->save();

                        if (!$collective->has($index)) {
                            $groups = $collective[0]->groups;
                        } else {
                            $groups = $collective[$index]->groups;
                        }

                        foreach ($groups as $group) {
                            $newGroup = new CourseGroup($group->toArray());
                            $newGroup->course_id = $newCourse->id;
                            $newGroup->course_date_id = $newCourseDate->id;
                            $oldDegree = $oldDegrees->firstWhere('id', $group->degree_id);
                            $newDegree = Degree::where('degree_order', $oldDegree->degree_order)
                                ->where('school_id', $newCourse->school_id)->where('sport_id', $newCourse->sport_id)
                                ->first();
                            $oldTeacherDegree = $oldDegrees->firstWhere('id', $group->teacher_min_degree);
                            $newTeacherDegree = Degree::where('degree_order', $oldTeacherDegree->degree_order)
                                ->where('school_id', $newCourse->school_id)->where('sport_id', $newCourse->sport_id)
                                ->first();
                            $newGroup->degree_id = $newDegree->id;
                            $newGroup->old_id = $group->id;
                            $newGroup->teacher_min_degree = $newTeacherDegree->id;

                            $newGroup->save();
                            foreach ($group->subgroups as $subgroup) {
                                $newSubgroup = new CourseSubgroup($subgroup->toArray());
                                $newSubgroup->course_id = $newCourse->id;
                                $newSubgroup->course_date_id = $newCourseDate->id;
                                $newSubgroup->course_group_id = $newGroup->id;
                                $newSubgroup->degree_id = $newGroup->degree_id;
                                $newSubgroup->old_id = $subgroup->id;
                                $newSubgroup->monitor_id =
                                    Monitor::where('old_id', $subgroup->monitor_id)->first()->id ?? null;
                                $newSubgroup->save();
                            }


                        }

                        $newCourseDate->save();
                        //return $this->sendResponse($newCourseDate, 200);
                    }
                }
            } catch (\Exception $e) {
                Log::channel('migration')->error("Error: " . $e->getMessage());
            }
        }

        Log::info('Migración de cursos colectivos completada');
        Log::info('Iniciando migración de cursos privados');
        $oldCoursesPrive = Course2::where('created_at', '>', $fechaInicio)
            ->where('deleted_at', null)
            ->whereNull('group_id')
            ->with(['priceRanges', 'course_dates'])
            ->get();

        foreach ($oldCoursesPrive as $prive) {
            try {
                $newCourse = new Course($prive->toArray());
                $newCourse->course_type = $prive->course_type_id;
                $newCourse->settings =
                    json_encode(['weekDays' => $this->getWeekDayAvailability($prive->day_start_res, $prive->day_end_res)]);
                $newCourse->is_flexible = $prive->duration_flexible;
                $newCourse->price_range = json_encode($prive->priceRanges, true);
                $newCourse->old_id = $prive->id;
                $base64Image = $prive->image;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $imageName = 'image_' . time() . '.' . $type[1];
                    Storage::disk('public')->put($imageName, $imageData);
                    $newCourse->image = Storage::disk('public')->url($imageName);

                }
                $newCourse->save();


                $diasSemana = [
                    "sunday" => Carbon::SUNDAY,
                    "monday" => Carbon::MONDAY,
                    "tuesday" => Carbon::TUESDAY,
                    "wednesday" => Carbon::WEDNESDAY,
                    "thursday" => Carbon::THURSDAY,
                    "friday" => Carbon::FRIDAY,
                    "saturday" => Carbon::SATURDAY,
                ];

                // Comprobar si day_start_res y day_end_res están definidos y no están vacíos
                if (isset($prive["day_start_res"], $prive["day_end_res"]) && $prive["day_start_res"] !== "" &&
                    $prive["day_end_res"] !== "") {
                    $dayStart = $diasSemana[strtolower($prive["day_start_res"])];
                    $dayEnd = $diasSemana[strtolower($prive["day_end_res"])];
                } else {
                    // Si no están definidos, incluir todos los días de la semana
                    $dayStart = Carbon::SUNDAY;
                    $dayEnd = Carbon::SATURDAY;
                }

                // Crear un rango de días incluidos
                if ($dayStart <= $dayEnd) {
                    $includedDays = range($dayStart, $dayEnd);
                } else {
                    // Si el rango cruza el fin de semana
                    $includedDays = range($dayStart, Carbon::SATURDAY);
                    $includedDays = array_merge($includedDays, range(Carbon::SUNDAY, $dayEnd));
                }

                $dateStart = Carbon::createFromFormat('Y-m-d', $prive['date_start']);
                $dateEnd = Carbon::createFromFormat('Y-m-d', $prive['date_end']);

                $period = CarbonPeriod::create($dateStart, $dateEnd);

                foreach ($period as $date) {

                    $isActive = in_array($date->dayOfWeek, $includedDays);
                    $hour_min = $prive->hour_min !== null && $prive->hour_min != 'null' ? $prive->hour_min : '9:00';
                    $hour_max = $prive->hour_max !== null && $prive->hour_max != 'null' ? $prive->hour_max : '17:00';
                    $newCourseDate = new CourseDate([
                        'date' => $date->format('Y-m-d'),
                        'hour_start' => $hour_min . ':00',
                        'hour_end' => $hour_max . ':00',
                        'course_id' => $newCourse->id,
                        'active' => $isActive
                    ]);
                    $newCourseDate->save();
                }
            } catch (\Exception $e) {
                Log::channel('migration')->error("Error: " . $e->getMessage());
            }
        }
        Log::info('Migración de cursos privados completada');
        return $this->sendResponse('Cursos importados con exito', 200);
    }

    private function getWeekDayAvailability($dayStart, $dayEnd)
    {
        $weekDays = [
            'sunday' => false,
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
            'saturday' => false,
        ];

        $dayStartIndex = array_search(strtolower($dayStart), array_keys($weekDays));
        $dayEndIndex = array_search(strtolower($dayEnd), array_keys($weekDays));

        if ($dayStartIndex === false || $dayEndIndex === false) {
            // Retorna un error o maneja la situación en que los días no son válidos
            return $weekDays;
        }

        $currentIndex = $dayStartIndex;
        do {
            $currentDay = array_keys($weekDays)[$currentIndex];
            $weekDays[$currentDay] = true;
            $currentIndex = ($currentIndex + 1) % count($weekDays);
        } while ($currentIndex != ($dayEndIndex + 1) % count($weekDays));

        return $weekDays;
    }


    public function migrateBookings(Request $request): JsonResponse
    {

        //$oldDegrees = \App\Models\OldModels\Degree::all();
        $fechaInicio = Carbon::createFromDate(null, 9, 9);
        $oldBookings = Bookings2::withTrashed()->where('created_at', '>', $fechaInicio)
            ->whereHas('booking_users')
            ->with(['booking_users.course', 'booking_users.subgroup.group.course'])
            ->get();

       // $bookingsUsersWithoutCourse = [];

        foreach ($oldBookings as $oldBooking) {
            $newBooking = new Booking($oldBooking->toArray());
            $newBooking->old_id = $oldBooking->id;
            if ($oldBooking->deleted_at) {
                $newBooking->status = 3;
            }
            $bookingUsers = $oldBooking->booking_users; // Asumiendo que esto es una colección

            $allDeleted = $bookingUsers->every(function ($bookingUser) {
                return $bookingUser->deleted_at !== null;
            });

            $someDeleted = $bookingUsers->contains(function ($bookingUser) {
                return $bookingUser->deleted_at !== null;
            });

            if ($allDeleted) {
                $newBooking->status = 3; // Todos están eliminados
            } elseif ($someDeleted && !$oldBooking->deleted_at) {
                $newBooking->status = 2; // Algunos están eliminados
            }

            $newBooking->save();
            Log::channel('migration')->info('Empezamos con la :'. $oldBooking->id);

            foreach ($oldBooking->booking_users as $index => $oldBookingUser) {
                if (!$oldBooking->deleted_at && $oldBookingUser->deleted_at) {
                    $newBooking->status = 2;
                    $newBooking->deleted_at = null;
                    $newBooking->save();
                }

                if ($oldBookingUser->course_groups_subgroup2_id) {
                    //Collectivos
                    $newBookingUser = new BookingUser($oldBookingUser->toArray());
                    if ($oldBookingUser->deleted_at) {
                        $newBookingUser->status = 2;
                        $newBookingUser->deleted_at = null;
                    }
                    $newBookingUser->booking_id = $newBooking->id;
                    $course_subgroup = CourseSubgroup::where('old_id', $oldBookingUser->course_groups_subgroup2_id)
                        ->first();
                    if (!$course_subgroup) {
                        Log::channel('migration')->warning('Reserva sin subgrupo inicial: id', $oldBookingUser->toArray());
                        $oldCourseSubGroup = CourseGroupsSubgroups2::find($oldBookingUser->course_groups_subgroup2_id);
                        if(!$oldCourseSubGroup) {
                            Log::channel('migration')->info('Reserva sin subgrupo: id', $oldBookingUser->toArray());
                            Log::channel('migration')->info('Id Subgrupo antiguo id:'. $oldBookingUser->course_groups_subgroup2_id);
                            continue;
                        } else {
                            $course_subgroup = CourseSubgroup::where('old_id', $oldCourseSubGroup->id)
                                ->first();
                        }
                        $oldCourseGroup = CourseGroups2::find($oldCourseSubGroup->course_group2_id);
                        if(!$oldCourseGroup) {
                            Log::channel('migration')->info('Reserva sin grupo: id', $oldBookingUser->toArray());
                            Log::channel('migration')->info('Id Subgrupo antiguo id:'. $oldBookingUser->course_groups_subgroup2_id);
                            continue;
                        }
                        $oldCourse = Course2::find($oldCourseGroup->course2_id);
                       // Log::channel('migration')->warning('Reserva sin subgrupo inicial: course', $oldCourse->toArray());

                        if(!$oldCourse) {
                            Log::channel('migration')->info('Reserva sin curso: id', $oldBookingUser->toArray());
                            Log::channel('migration')->info('Id Subgrupo antiguo id:'. $oldBookingUser->course_groups_subgroup2_id);
                            Log::channel('migration')->info('Id Grupo antiguo id:'. $oldCourseSubGroup->course_group2_id);
                            Log::channel('migration')->info('Id Curso antiguo id:'. $oldCourseGroup->course2_id);
                            continue;
                        }
                    }
                    $monitor = Monitor::where('old_id', $oldBookingUser->monitor_id)
                        ->first();
                    if ($monitor) {
                        $newBookingUser->monitor_id = $monitor->id;
                    }
                    $courseDate =
                        CourseDate::find($course_subgroup->course_date_id);
                    $newBookingUser->course_subgroup_id = $course_subgroup->id;
                    $newBookingUser->school_id = $newBooking->school_id;
                    $newBookingUser->course_group_id = $course_subgroup->course_group_id;
                    $newBookingUser->course_id = $course_subgroup->course_id;
                    $newBookingUser->degree_id = $course_subgroup->degree_id;
                    $newBookingUser->course_date_id = $course_subgroup->course_date_id;
                    $newBookingUser->date = $courseDate->date;
                    $newBookingUser->hour_start = $courseDate->hour_start;
                    $newBookingUser->hour_end = $courseDate->hour_end;

                    $newBookingUser->client_id = Client::where('old_id', $oldBookingUser->user_id)->first()->id;
                    $newBookingUser->save();
                } else {
                    //PRIVADOS

                    $newBookingUser = new BookingUser($oldBookingUser->toArray());
                    $course = Course::where('old_id', $oldBookingUser->course2_id)
                        ->first();
                    if (!$course) {
                        $newBooking->delete();
                        Log::channel('migration')->warning('Reserva privada sin course: id', $oldBookingUser->toArray());
                        //return $this->sendResponse($newBookingUser, 200);
                        continue;
                    }

                    if ($oldBookingUser->deleted_at) {
                        $newBookingUser->status = 2;
                    }
                    $courseDate =
                        CourseDate::where('course_id', $course->id)->where('date', $oldBookingUser->date)
                            ->first();
                    if (!$courseDate) {
                        Log::channel('migration')->warning('Reserva sin cursedate: id', $oldBookingUser->toArray());
                        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $oldBookingUser['date'] . ' ' . $oldBookingUser['hour']);

                        list($hours, $minutes, $seconds) = explode(':', $oldBookingUser['duration']);
                        $durationInSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

                        $endTime = $startTime->copy()->addSeconds($durationInSeconds);
                        $courseDate = new CourseDate([
                            'course_id' => $course->id,
                            'date' => $oldBookingUser->date,
                            'hour_start' => $oldBookingUser['hour'],
                            'hour_end' => $endTime->format('H:i')
                        ]);
                        $courseDate->save();
                        Log::channel('migration')->info('Creada nueva cursedate que no encaja', $courseDate->toArray());
                    }

                    $newBookingUser->school_id = $newBooking->school_id;

                    $monitor = Monitor::where('old_id', $oldBookingUser->monitor_id)
                        ->first();
                    if ($monitor) {
                        $newBookingUser->monitor_id = $monitor->id;
                    }
                    $newBookingUser->course_id = $course->id;
                    $newBookingUser->booking_id = $newBooking->id;

                    $newBookingUser->client_id = Client::where('old_id', $oldBookingUser->user_id)->first()->id;
                    $newBookingUser->course_date_id = $courseDate->id;
                    $startTime = Carbon::createFromFormat('Y-m-d H:i:s',
                        $oldBookingUser['date'] . ' ' . $oldBookingUser['hour']);
                    $endTime = $startTime->copy()
                        ->addSeconds(strtotime($oldBookingUser['duration']) - strtotime('TODAY'));
                    $newBookingUser->hour_end = $endTime->format('H:i:s');
                    $newBookingUser->hour_start = $oldBookingUser['hour'];



                    $newBookingUser->save();

                    //return $this->sendResponse($oldBookingUser, 200);

                }

            }

            /*$oldVouchersLogs = VoucherLog::where('booking_id', $oldBooking->id)->get();

            foreach ($oldVouchersLogs as $oldVouchersLog) {
                $newVoucher = \App\Models\Voucher::where('old_id', $oldVouchersLog->voucher_id)->first();
                $newVoucherLog = new VouchersLog([
                    'voucher_id' => $newVoucher->id,
                    'booking_id' => $newBooking->id,
                    'amount' => $oldVouchersLog->amount,
                ]);
                $newVoucherLog->save();
            }*/

        }


        return $this->sendResponse('Bookings imported correctly', 200);

    }

}
