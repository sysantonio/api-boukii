<?php

namespace App\Models\OldModels;

//use Illuminate\Database\Eloquent\Model;
use App\Models\OldModels\BookingUsers2;
use App\Models\OldModels\Course2;
use App\Models\OldModels\CourseDates2;
use App\Models\OldModels\CourseGroups2;
use App\Models\OldModels\CourseGroupsSubgroups2;
use App\Models\OldModels\DegreeSchoolSport;
use App\Models\OldModels\UserSportAuthorizedDegrees;
use App\Models\OldModels\UserType;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\OldModels\Fake\Calendar;


class User extends Authenticatable
{

    use HasFactory, SoftDeletes, Notifiable;

    protected $casts = [
        'active' => 'bool',
    ];

    protected $connection = 'old';

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'telephone',
        'address',
        'cp',
        'city',
        'province_id',
        'country_id',
        'language1_id',
        'language2_id',
        'language3_id',
        'image',
        'user_type',
        'user_collective',
        'active',
    ];

    /**
     * JWT
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function markLogout()
    {
        $this->logout = true;
        $this->save();
    }

    public function unmarkLogout()
    {
        $this->logout = false;
        $this->save();
    }

    public function checkLogout()
    {
        return ($this->logout);
    }

    /**
     * Relations
     */

    public function schools()
    {
        return $this->belongsToMany(School::class, 'user_schools', 'user_id', 'school_id')->withPivot('active_school');
    }

    public function notes()
    {
        return $this->hasMany(UserObservations::class, 'user_id');
    }


    public function restrictions()
    {
        return $this->hasMany(AdminRestriction::class, 'user_id');
    }

    public function subgroups_dates()
    {
        return $this->hasMany(SubgroupMonitorDate::class, 'monitor_id');
    }


    /**
     * Admin Restrictions
     */

    public static function canCreate()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_CLIENT)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_CREATE, $restrictions));
        }

        return true;
    }

    public static function canView()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_CLIENT)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_SHOW, $restrictions));
        }

        return true;
    }

    public static function canEdit()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_CLIENT)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_EDIT, $restrictions));
        }

        return true;
    }

    public static function canDelete()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_CLIENT)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_DELETE, $restrictions));
        }

        return true;
    }

    public static function canCreateMonitor()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_TEACHER)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_CREATE, $restrictions));
        }

        return true;
    }

    public static function canViewMonitor()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_TEACHER)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_SHOW, $restrictions));
        }

        return true;
    }

    public static function canEditMonitor()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_TEACHER)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_EDIT, $restrictions));
        }

        return true;
    }

    public static function canDeleteMonitor()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_TEACHER)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_DELETE, $restrictions));
        }

        return true;
    }

    /**
     * Helpers
     */

    public function getImageUrl()
    {
        if($this->image && $this->image!='') {
            return $this->image;
        } else {
            // Default image
            return asset('assets/images/avatar-empty.png');
        }
    }

    public function getYearsOld()
    {
        if ($this->birth_date)
        {
            return Carbon::parse($this->birth_date)->diff(Carbon::now())->y;
        }
        else
        {
            return 0;
        }
    }

    public function getProvinceName()
    {
        $pr = ($this->province_id) ? Province::find($this->province_id) : null;
        return $pr ? $pr->name : '';
    }

    public function getCountryName()
    {
        $co = ($this->country_id) ? Country::find($this->country_id) : null;
        return $co ? $co->name : '';
    }

    public function getFirstLanguageCode()
    {
        $la = ($this->language1_id) ? Language::find($this->language1_id) : null;
        return $la ? $la->code : '';
    }

    public function getSecondLanguageCode()
    {
        $la = ($this->language2_id) ? Language::find($this->language2_id) : null;
        return $la ? $la->code : '';
    }

    public function getThirdLanguageCode()
    {
        $la = ($this->language3_id) ? Language::find($this->language3_id) : null;
        return $la ? $la->code : '';
    }

    public function getCurrentSchool()
    {
        return UserSchools::where('user_id', $this->id)->latest()->first();
    }

    /**
     * As of 2022-11, Admins from each School can set Monitors (only) a default Station
     */
    public function getDefaultStationBySchool($schoolID)
    {
        if ($this->user_type != UserType::ID_MONITOR)
        {
            return null;
        }
        else
        {
            $hisUS = UserSchools::where('user_id', '=', $this->id)->where('school_id', '=', $schoolID)->first();
            return $hisUS ? Station::find($hisUS->station_id) : null;
        }
    }


    /**
     * As of 2022-12-16 table "users" has an "active" field to avoid login.
     * BUT each School wants his own switch to deactivate an user, namely a Monitor,
     * i.e. that Monitor can login into the app but can't view any data from this School.
     *
     * So in fact we're mostly interested on read/write a "active_school" field at "user_schools" table
     * (while the global "users.active" switch is almost abandoned)
     *
     * @param int $schoolID
     * @return bool
     */
    public function getActiveBySchool($schoolID)
    {
        $hisUS = UserSchools::where('user_id', '=', $this->id)->where('school_id', '=', $schoolID)->first();
        return $hisUS ? $hisUS->active_school : false;
    }
    public function setActiveBySchool($schoolID, $newValue)
    {
        $hisUS = UserSchools::where('user_id', '=', $this->id)->where('school_id', '=', $schoolID)->first();
        if ($hisUS)
        {
            $hisUS->active_school = $newValue;
            $hisUS->status_updated_at = Carbon::now();
            $hisUS->save();
        }
    }


    /**
     * Validation
     */

    public function validate($validator, $user_types = [])
    {
        // Check user is "active" (global switch)
        if (!$this->active)
        {
            return $validator->errors()->add('user_active', 'User is not active');
        }

        // Check user type allowed
        if ($user_types && !in_array($this->user_type, $user_types))
        {
            if ($this->user_type == UserType::ID_VISUALIZER && in_array(UserType::ID_ADMINISTRATOR, $user_types))
            {
                // If user is visualizer and admin is allowed dont send error
            }
            else
            {
                return $validator->errors()->add('user_type', 'User type is not allowed');
            }
        }

        // Extra check by user type
        switch ($this->user_type)
        {
            case UserType::ID_ADMINISTRATOR:
            {
                // As of 2022-12 Admins must be connected to ONE School,
                // and be "active" for that School
                // cf. \App\Models\UserSchools::getAdminSchool()
                $hisUS = UserSchools::where('user_id', '=', $this->id)->where('active_school', '=', 1)->first();
                $hisSchool = $hisUS ? School::find($hisUS->school_id) : null;

                if (!$hisSchool)
                {
                    return $validator->errors()->add('user_school', 'No active school associated');
                }

                // Plus, his School should be connected to 1+ Stations
                if (!$hisSchool->stations()->first())
                {
                    return $validator->errors()->add('user_school_station', 'No station associated to school');
                }

                break;
            }
            // N.B: as of 2022-12-16 Monitors & Clients CAN login even WITHOUT any active School - just shouldn't view any info from those "deactivated" Schools
        }

        return $validator->errors();
    }

    /**
     * Check that desired user exists and is linked to current one
     */
    public function getUserIfBelongsToGroup($userID)
    {
        $ug = UserGroups::where('user_secondary_id', '=', $userID)
            ->where('user_main_id', '=', Auth::id())
            ->first();

        $authenticatedUser = Auth::user();

        return $ug || $authenticatedUser && $authenticatedUser->id === intval($userID) ? User::find($userID) : null;
    }

    public static function listAvailableMonitorsNewIframe($request)
    {


        $dates = array();

        if(isset($request['bookingUserID'])) {
            // editing a reservation
            $booking = BookingUsers2::where('id', $request['bookingUserID'])->first();
            if(isset($booking->course2_id)) {
                // course privé (aquí parece que no se guarda el degree)
                $course = Course2::where('id', $booking->course2_id)->first();
                $sport_id = $course->sport_id;

                $dates = array();
                $dates[] = array('date' => date("Y-m-d", strtotime($booking->date)), 'hour' => date("H:i", strtotime($booking->hour)), 'duration' => date("H:i", strtotime($booking->duration)));

                $date = date("Y-m-d", strtotime($booking->date));
                $hour = date("H:i", strtotime($booking->hour));
                $duration = date("H:i", strtotime($booking->duration));

                $client_id = $booking->user_id;
            }
            if(isset($booking->course_groups_subgroup2_id)) {
                // course collectif
                $subgroup = CourseGroupsSubgroups2::where('id', $booking->course_groups_subgroup2_id)->first();
                $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                // $degree = $group->degree_id;
                $min_degree = $group->teacher_min_degree;

                $course = Course2::where('id', $group->course2_id)->first();
                $sport_id = $course->sport_id;

                $dates = array();
                $courseDates = CourseDates2::where('course2_id', $course->id)->get();
                foreach($courseDates as $cdate)
                {
                    $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
                }

                $courseDate = CourseDates2::where('course2_id', $course->id)->first();
                $date = date("Y-m-d", strtotime($courseDate->date));
                $hour = date("H:i", strtotime($courseDate->hour));
                $duration = date("H:i", strtotime($course->duration));
            }
        }

        if(isset($request['courseGroupID'])) {
            // editing a course
            $group = CourseGroups2::where('id', $request['courseGroupID'])->first();
            $min_degree = $group->teacher_min_degree;

            $course = Course2::where('id', $group->course2_id)->first();
            $sport_id = $course->sport_id;

            $dates = array();
            $courseDates = CourseDates2::where('course2_id', $course->id)->get();
            foreach($courseDates as $cdate)
            {
                $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
            }

        }

        if(isset($request['courseId'])) {
            // editing a course

            $course = Course2::where('id', $request['courseId'])->first();
            $sport_id = $course->sport_id;

            $dates = array();
            $courseDates = CourseDates2::where('course2_id', $course->id)->get();
            foreach($courseDates as $cdate)
            {
                $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
            }


        }



        if(isset($request['sport'])) $sport_id = $request['sport'];
        if(isset($request['degree'])) $degree = $request['degree'];
        if(isset($request['selectedDate'])) $date = $request['selectedDate'];
        if(isset($request['selectedHour'])) $hour = $request['selectedHour'];
        if(isset($request['selectedDuration'])) $duration = $request['selectedDuration'];

        if(isset($request['selectedDate']) && isset($request['selectedHour']) && isset($request['selectedDuration'])) {
            $dates = array();
            $dates[] = array('date' => $request['selectedDate'], 'hour' => $request['selectedHour'], 'duration' => $request['selectedDuration']);
        }

        if(isset($request->selectedClient)) $client_id = $request->selectedClient;

        if(isset($client_id)) {
            $client = User::where('id', $client_id)->first();
            if(isset($client->id)) {
                $langs = array();
                if(!empty($client->language1_id)) $langs[] = $client->language1_id;
                if(!empty($client->language2_id)) $langs[] = $client->language2_id;
                if(!empty($client->language3_id)) $langs[] = $client->language3_id;
            }
        }

        $usersQuery = User::join('user_schools AS US', 'users.id', '=', 'US.user_id')
            ->join('user_sports AS USP', 'users.id', '=', 'USP.user_id')
            ->leftJoin('stations', 'US.station_id', '=', 'stations.id')
            ->where('USP.sport_id', '=', $sport_id)
            ->where('USP.school_id', '=', $request['school'])
            ->where('USP.deleted_at', '=', NULL)
            ->where('US.school_id', '=', $request['school'])
            ->where('US.active_school', '=', 1)
            ->where('users.user_type', '=', UserType::ID_MONITOR)
            ->selectRaw('DISTINCT(users.id), users.*, stations.id AS stationID, IFNULL(stations.name, "") AS stationName, USP.id AS user_sport_id, USP.degree_id, USP.allow_adults')
            ->orderBy('users.first_name', 'ASC')->orderBy('users.last_name', 'ASC');

        $users = $usersQuery->get();

        $monitors = array();

        foreach ($users as $user)
        {
            $blocked = 0;

            $user->active = true;

            $user->station = [
                'id' => $user->stationID,
                'name' => $user->stationName
            ];
            unset($user->stationID, $user->stationName);

            /*
                monitor lang check
            */
            if (isset($langs) && count($langs)>0) {
                $insert = 0;
                if(in_array($user->language1_id, $langs) || in_array($user->language2_id, $langs) || in_array($user->language3_id, $langs)) $insert = 1;
                if($insert == 0) continue;
            }

            /*
                monitor allow_adults check
            */
            if(isset($client->id)) {
                $edad = Carbon::parse($client->birth_date)->age;
                if($edad >=18 && $user->allow_adults == 0) continue;
            }

            /*
                monitor degrees checks
                1. reservation degree
            */
            if(isset($degree)) {
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                if(!in_array($degree, $monitor_degrees)) continue;
            }
            /*
                monitor degrees checks
                2. course min degree
            */
            if(isset($min_degree)) {
                $check = 0;
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                foreach($monitor_degrees as $md)
                {
                    if($md > $min_degree) $check = 1;
                }

                if($check==0) continue;
            }

            /*
                monitor availability checks
                podemos recibir varias fechas (si es un curso colectivo)
            */

            foreach($dates as $date1)
            {
                if($blocked == 1) continue;

                $duration = $date1['duration'];
                $date = $date1['date'];
                $hour = $date1['hour'];

                $from = Carbon::createFromTimeString('00:00:00');
                $to = Carbon::parse($duration);
                $minutes = $from->diffInMinutes($to);

                $startTime = Carbon::parse($date . ' ' . $hour);
                $endTime = $startTime->clone()->addMinutes($minutes);

                $calendar = new Calendar($startTime, $endTime);
                //$calendar->setSchool($mySchool); // No asignamos escuela ya que el monitor puede pertenecer a varias.
                $calendar->setMonitor($user);

                // private courses
                $privateData = $calendar->getPrivateCourses()->get();
                if (count($privateData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                    {
                        if(isset($request->bookingUserID) && $box->detail->id == $request->bookingUserID) continue;

                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }

                    }
                }

                // collective courses
                $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                if (count($collectiveData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                    {
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        // Realizar la comprobación de duración aquí
                        $courseDuration = CarbonInterval::hours($course->duration);

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }
                    }
                }

                // NWD (no working days)
                $nwdData = $calendar->getMonitorNwd();

                if (count($nwdData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_NWD, $nwdData) as $box)
                    {
                        /*$startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            $blocked = 1;
                        }*/

                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        // Realizar la comprobación de duración aquí
                        $courseDuration = CarbonInterval::hours($course->duration);

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }

                    }
                }
            }

            if($blocked == 0) $monitors[] = $user;
        }



        return $monitors;
    }


    public static function listAvailableMonitorsNew($request)
    {


        // Check that current user is the Admin of a certain School
        $mySchool = UserSchools::getAdminSchool();
        if (!$mySchool)
        {

            return [];
        }

        $dates = array();

        if(isset($request['bookingUserID'])) {
            // editing a reservation
            $booking = BookingUsers2::where('id', $request['bookingUserID'])->first();
            if(isset($booking->course2_id)) {
                // course privé (aquí parece que no se guarda el degree)
                $course = Course2::where('id', $booking->course2_id)->first();
                $sport_id = $course->sport_id;

                $dates = array();
                $dates[] = array('date' => date("Y-m-d", strtotime($booking->date)), 'hour' => date("H:i", strtotime($booking->hour)), 'duration' => date("H:i", strtotime($booking->duration)));

                $date = date("Y-m-d", strtotime($booking->date));
                $hour = date("H:i", strtotime($booking->hour));
                $duration = date("H:i", strtotime($booking->duration));

                $client_id = $booking->user_id;
            }
            if(isset($booking->course_groups_subgroup2_id)) {
                // course collectif
                $subgroup = CourseGroupsSubgroups2::where('id', $booking->course_groups_subgroup2_id)->first();
                $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                // $degree = $group->degree_id;
                $min_degree = $group->teacher_min_degree;

                $course = Course2::where('id', $group->course2_id)->first();
                $sport_id = $course->sport_id;

                $dates = array();
                $courseDates = CourseDates2::where('course2_id', $course->id)->get();
                foreach($courseDates as $cdate)
                {
                    $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
                }

                $courseDate = CourseDates2::where('course2_id', $course->id)->first();
                $date = date("Y-m-d", strtotime($courseDate->date));
                $hour = date("H:i", strtotime($courseDate->hour));
                $duration = date("H:i", strtotime($course->duration));
            }
        }

        if(isset($request['courseGroupID'])) {
            // editing a course
            $group = CourseGroups2::where('id', $request['courseGroupID'])->first();
            $min_degree = $group->teacher_min_degree;

            $course = Course2::where('id', $group->course2_id)->first();
            $sport_id = $course->sport_id;

            $dates = array();
            $courseDates = CourseDates2::where('course2_id', $course->id)->get();
            foreach($courseDates as $cdate)
            {
                $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
            }

            if($course->duration_flexible) {

            }

        }

        if(isset($request['courseId'])) {
            // editing a course

            $course = Course2::where('id', $request['courseId'])->first();
            $sport_id = $course->sport_id;

            $dates = array();
            $courseDates = CourseDates2::where('course2_id', $course->id)->get();
            foreach($courseDates as $cdate)
            {
                $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
            }


        }



        if(isset($request['sport'])) $sport_id = $request['sport'];
        if(isset($request['degree'])) $degree = $request['degree'];
        if(isset($request['selectedDate'])) $date = $request['selectedDate'];
        if(isset($request['selectedHour'])) $hour = $request['selectedHour'];
        if(isset($request['selectedDuration'])) $duration = $request['selectedDuration'];

        if(isset($request['selectedDate']) && isset($request['selectedHour']) && isset($request['selectedDuration'])) {
            $dates = array();
            $dates[] = array('date' => $request['selectedDate'], 'hour' => $request['selectedHour'], 'duration' => $request['selectedDuration']);
        }

        if(isset($request->selectedClient)) $client_id = $request->selectedClient;

        if(isset($client_id)) {
            $client = User::where('id', $client_id)->first();
            if(isset($client->id)) {
                $langs = array();
                if(!empty($client->language1_id)) $langs[] = $client->language1_id;
                if(!empty($client->language2_id)) $langs[] = $client->language2_id;
                if(!empty($client->language3_id)) $langs[] = $client->language3_id;
            }
        }

        $usersQuery = User::join('user_schools AS US', 'users.id', '=', 'US.user_id')
            ->join('user_sports AS USP', 'users.id', '=', 'USP.user_id')
            ->leftJoin('stations', 'US.station_id', '=', 'stations.id')
            ->where('USP.sport_id', '=', $sport_id)
            ->where('USP.school_id', '=', $mySchool->id)
            ->where('USP.deleted_at', '=', NULL)
            ->where('US.school_id', '=', $mySchool->id)
            ->where('US.active_school', '=', 1)
            ->where('users.user_type', '=', UserType::ID_MONITOR)
            ->selectRaw('DISTINCT(users.id), users.*, stations.id AS stationID, IFNULL(stations.name, "") AS stationName, USP.id AS user_sport_id, USP.degree_id, USP.allow_adults')
            ->orderBy('users.first_name', 'ASC')->orderBy('users.last_name', 'ASC');

        $users = $usersQuery->get();

        $monitors = array();

        foreach ($users as $user)
        {
            $blocked = 0;

            $user->active = true;

            $user->station = [
                'id' => $user->stationID,
                'name' => $user->stationName
            ];
            unset($user->stationID, $user->stationName);

            /*
                monitor lang check
            */
            if (isset($langs) && count($langs)>0) {
                $insert = 0;
                if(in_array($user->language1_id, $langs) || in_array($user->language2_id, $langs) || in_array($user->language3_id, $langs)) $insert = 1;
                if($insert == 0) continue;
            }

            /*
                monitor allow_adults check
            */
            if(isset($client->id)) {
                $edad = Carbon::parse($client->birth_date)->age;
                if($edad >=18 && $user->allow_adults == 0) continue;
            }

            /*
                monitor degrees checks
                1. reservation degree
            */
            if(isset($degree)) {
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                if(!in_array($degree, $monitor_degrees)) continue;
            }
            /*
                monitor degrees checks
                2. course min degree
            */
            if(isset($min_degree)) {
                $check = 0;
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                foreach($monitor_degrees as $md)
                {
                    if($md > $min_degree) $check = 1;
                }

                if($check==0) continue;
            }

            /*
                monitor availability checks
                podemos recibir varias fechas (si es un curso colectivo)
            */

            foreach($dates as $date1)
            {
                if($blocked == 1) continue;

                $duration = $date1['duration'];
                $date = $date1['date'];
                $hour = $date1['hour'];

                $from = Carbon::createFromTimeString('00:00:00');
                $to = Carbon::parse($duration);
                $minutes = $from->diffInMinutes($to);

                $startTime = Carbon::parse($date . ' ' . $hour);
                $endTime = $startTime->clone()->addMinutes($minutes);

                $calendar = new Calendar($startTime, $endTime);
                //$calendar->setSchool($mySchool); // No asignamos escuela ya que el monitor puede pertenecer a varias.
                $calendar->setMonitor($user);

                // private courses
                $privateData = $calendar->getPrivateCourses()->get();
                if (count($privateData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                    {
                        if(isset($request->bookingUserID) && $box->detail->id == $request->bookingUserID) continue;

                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }

                    }
                }

                // collective courses
                $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                if (count($collectiveData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                    {
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }
                    }
                }

                // NWD (no working days)
                $nwdData = $calendar->getMonitorNwd();

                if (count($nwdData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_NWD, $nwdData) as $box)
                    {
                        /*$startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            $blocked = 1;
                        }*/

                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        // Realizar la comprobación de duración aquí
                        $courseDuration = CarbonInterval::hours($course->duration);

                        if($course->hour_min) {
                            // Comprobar el rango de hour_min y hour_max
                            $minStartTime = Carbon::parse($date . ' ' . $course->hour_min . ':00');
                            $maxStartTime = Carbon::parse($date . ' ' . $course->hour_max . ':00');

                            // Comprobar si hay un intervalo válido de tiempo de $courseDuration entre $minStartTime y $maxStartTime
                            $intervalStart = $minStartTime;
                            $intervalEnd = $intervalStart->clone()->addMinutes($courseDuration->totalMinutes);

                            while ($intervalEnd->lte($maxStartTime)) {
                                $blocked = 1;
                                if($startTime2->diffInMinutes($endTime2) > $courseDuration->totalMinutes) {
                                    break 2;
                                }
                                // Si se encuentra un intervalo válido de tiempo de $courseDuration, el curso no está bloqueado
                                if (!$startTime2->between($intervalStart, $intervalEnd)
                                    && !$endTime2->between($intervalStart, $intervalEnd)) {
                                    $blocked = 0;
                                    break 2; // Salir de los dos bucles, ya que hemos encontrado un intervalo válido
                                }

                                $intervalStart->addMinutes(1); // Incrementar el tiempo en 1 minuto (ajustar según sea necesario)
                                $intervalEnd->addMinutes(1);
                            }
                        } else {
                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                            {
                                $blocked = 1;
                            }
                        }

                    }
                }
            }

            if($blocked == 0) $monitors[] = $user;
        }



        return $monitors;
    }
    public static function listAvailableMonitors($data)
    {
        $mySchool = $data['school'];
        $dates = array();
        if(isset($data['bookingUserID'])) {
            // editing a reservation
            $booking = BookingUsers2::where('id', $data['bookingUserID'])->first();
            if(isset($booking->course2_id)) {
                // course privé
                $course = Course2::where('id', $booking->course2_id)->first();
                $sport_id = $course->sport_id;


                $dates[] = array('date' => date("Y-m-d", strtotime($booking->date)), 'hour' => date("H:i", strtotime($booking->hour)), 'duration' => date("H:i", strtotime($booking->duration)));

                $date = date("Y-m-d", strtotime($booking->date));
                $hour = date("H:i", strtotime($booking->hour));
                $duration = date("H:i", strtotime($booking->duration));

                $client_id = $booking->user_id;
            }

            if(isset($booking->course_groups_subgroup2_id)) {
                // course collectif
                $subgroup = CourseGroupsSubgroups2::where('id', $booking->course_groups_subgroup2_id)->first();
                $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                // $degree = $group->degree_id;
                $min_degree = $group->teacher_min_degree;

                $course = Course2::where('id', $group->course2_id)->first();
                $sport_id = $course->sport_id;

                $dates = array();
                $courseDates = CourseDates2::where('course2_id', $course->id)->get();
                foreach($courseDates as $cdate)
                {
                    $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
                }

                $courseDate = CourseDates2::where('course2_id', $course->id)->first();
                $date = date("Y-m-d", strtotime($courseDate->date));
                $hour = date("H:i", strtotime($courseDate->hour));
                $duration = date("H:i", strtotime($course->duration));
            }
        }

        if(isset($data['courseGroupID'])) {
            // editing a course
            $group = CourseGroups2::where('id', $data['courseGroupID'])->first();
            $min_degree = $group->teacher_min_degree;

            $course = Course2::where('id', $group->course2_id)->first();
            $sport_id = $course->sport_id;

            $dates = array();
            $courseDates = CourseDates2::where('course2_id', $course->id)->get();
            foreach($courseDates as $cdate)
            {
                $dates[] = array('date' => date("Y-m-d", strtotime($cdate->date)), 'hour' => date("H:i", strtotime($cdate->hour)), 'duration' => date("H:i", strtotime($cdate->duration)));
            }

            $courseDate = CourseDates2::where('course2_id', $course->id)->first();
            $date = date("Y-m-d", strtotime($courseDate->date));
            $hour = date("H:i", strtotime($courseDate->hour));
            $duration = date("H:i", strtotime($course->duration));
        }

        if(isset($data['sport'])) $sport_id = $data['sport'];
        if(isset($data['degree'])) $degree = $data['degree'];
        if(isset($data['selectedDate'])) $date = $data['selectedDate'];
        if(isset($data['selectedHour'])) $hour = $data['selectedHour'];
        if(isset($data['selectedDuration'])) $duration = $data['selectedDuration'];

        if(isset($data['selectedDate']) && isset($data['selectedHour']) && isset($data['selectedDuration'])) {
            $dates = array();
            $dates[] = array('date' => $data['selectedDate'], 'hour' => $data['selectedHour'], 'duration' => $data['selectedDuration']);
        }

        if(isset($data['selectedClient'])) $client_id = $data['selectedClient'];
        if(isset($client_id)) {
            $client = User::where('id', $client_id)->first();
            if(isset($client->id)) {
                $langs = array();
                if(!empty($client->language1_id)) $langs[] = $client->language1_id;
                if(!empty($client->language2_id)) $langs[] = $client->language2_id;
                if(!empty($client->language3_id)) $langs[] = $client->language3_id;
            }
        }

        $usersQuery = User::join('user_schools AS US', 'users.id', '=', 'US.user_id')
            ->join('user_sports AS USP', 'users.id', '=', 'USP.user_id')
            ->leftJoin('stations', 'US.station_id', '=', 'stations.id')
            ->where('USP.sport_id', '=', $sport_id)
            ->where('USP.school_id', '=', $mySchool->id)
            ->where('USP.deleted_at', '=', NULL)
            ->where('US.school_id', '=', $mySchool->id)
            ->where('US.active_school', '=', 1)
            ->where('users.user_type', '=', UserType::ID_MONITOR)
            ->selectRaw('DISTINCT(users.id), users.*, stations.id AS stationID, IFNULL(stations.name, "") AS stationName, USP.id AS user_sport_id, USP.degree_id, USP.allow_adults')
            ->orderBy('users.first_name', 'ASC')->orderBy('users.last_name', 'ASC');

        $users = $usersQuery->get();

        $monitors = array();

        foreach ($users as $user)
        {
            $blocked = 0;

            $user->active = true;

            $user->station = [
                'id' => $user->stationID,
                'name' => $user->stationName
            ];
            unset($user->stationID, $user->stationName);

            /*
                monitor lang check
            */
            if (isset($langs) && count($langs)>0) {
                $insert = 0;
                if(in_array($user->language1_id, $langs) || in_array($user->language2_id, $langs) || in_array($user->language3_id, $langs)) $insert = 1;
                if($insert == 0) continue;
            }

            /*
                monitor allow_adults check
            */
            if(isset($client->id)) {
                $edad = Carbon::parse($client->birth_date)->age;
                if($edad >=18 && $user->allow_adults == 0) continue;
            }

            /*
                monitor degrees checks
                1. reservation degree
            */
            if(isset($degree)) {
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                if(!in_array($degree, $monitor_degrees)) continue;
            }

            /*
                monitor degrees checks
                2. course min degree
            */
            if(isset($min_degree)) {
                $check = 0;
                $monitor_degrees = array();
                $monitor_degrees[] = $user->degree_id;
                $auth_degrees = UserSportAuthorizedDegrees::where('user_sport_id', $user->user_sport_id)->get();

                foreach($auth_degrees as $deg)
                {
                    $deg_school = DegreeSchoolSport::where('id', $deg->degree_id)->first();
                    if(isset($deg_school->degree_id) && !in_array($deg_school->degree_id, $monitor_degrees)) $monitor_degrees[] = $deg_school->degree_id;
                }

                foreach($monitor_degrees as $md)
                {
                    if($md > $min_degree) $check = 1;
                }

                if($check==0) continue;
            }

            /*
                monitor availability checks
                podemos recibir varias fechas (si es un curso colectivo)
            */
            foreach($dates as $date1)
            {
                if($blocked == 1) continue;

                $duration = $date1['duration'];
                $date = $date1['date'];
                $hour = $date1['hour'];

                $from = Carbon::createFromTimeString('00:00:00');
                $to = Carbon::parse($duration);
                $minutes = $from->diffInMinutes($to);

                $startTime = Carbon::parse($date . ' ' . $hour);
                $endTime = $startTime->clone()->addMinutes($minutes);

                $calendar = new Calendar($startTime, $endTime);
                $calendar->setSchool($mySchool);
                $calendar->setMonitor($user);

                // private courses
                $privateData = $calendar->getPrivateCourses()->get();
                if (count($privateData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                    {
                        if(isset($request->bookingUserID) && $box->detail->id == $request->bookingUserID) continue;

                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            $blocked = 1;
                        }
                    }
                }

                // collective courses
                $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                if (count($collectiveData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                    {
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            $blocked = 1;
                        }
                    }
                }

                // NWD (no working days)
                $nwdData = $calendar->getMonitorNwd();
                if (count($nwdData) > 0)
                {
                    foreach ($calendar->toItems(Calendar::TYPE_NWD, $nwdData) as $box)
                    {
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            $blocked = 1;
                        }
                    }
                }
            }

            if($blocked == 0) $monitors[] = $user;
        }

        return $monitors;
    }
}
