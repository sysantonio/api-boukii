<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\Fake\Calendar;
use Illuminate\Support\Facades\DB;

/**
 * Class Course2
 *
 * @property int $id
 * @property int $course_supertype_id
 * @property int $course_type_id
 * @property int $sport_id
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property float $price
 * @property string $currency
 * @property string $image
 * @property int $max_participants
 * @property string $duration
 * @property bool $duration_flexible
 * @property Carbon $date_start
 * @property Carbon $date_end
 * @property int $school_id
 * @property int $station_id
 * @property bool $confirm_attendance
 * @property bool $online
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property School $school
 * @property Station $station
 * @property Sport $sport
 * @property CourseSupertype $course_supertype
 * @property CourseType $course_type
 * @property Collection|CourseDates2[] $course_dates
 * @property Collection|PriceRange[] $priceRanges
 * @property Collection|CourseGroups2[] $groups
 *
 * @package App\Models
 */
class Course2 extends Model
{
    use SoftDeletes;
    protected $table = 'courses2';

    protected $casts = [
        'course_supertype_id' => 'int',
        'course_type_id' => 'int',
        'sport_id' => 'int',
        'price' => 'float',
        'max_participants' => 'int',
        'duration_flexible' => 'bool',
        'school_id' => 'int',
        'station_id' => 'int',
        'confirm_attendance' => 'bool',
        'online' => 'bool',
        'active' => 'bool'
    ];

    protected $dates = [];

    protected $connection = 'old';

protected $fillable = [
        'course_supertype_id',
        'course_type_id',
        'sport_id',
        'name',
        'image',
        'short_description',
        'description',
        'price',
        'currency',
        'max_participants',
        'duration',
        'duration_flexible',
        'date_start',
        'date_end',
        'date_start_res',
        'date_end_res',
        'day_start_res',
        'day_end_res',
        'hour_min',
        'hour_max',
        'school_id',
        'station_id',
        'confirm_attendance',
        'online',
        'active',
        'group_id',
        'created_at',
        'updated_at'
    ];

    protected $hidden = ['image'];


    /**
     * Relations
     */

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function course_supertype()
    {
        return $this->belongsTo(CourseSupertype::class);
    }

    public function course_type()
    {
        return $this->belongsTo(CourseType::class);
    }

    public function global_course()
    {
        return $this->belongsTo(CourseGlobal::class, 'group_id');
    }

    public function course_dates()
    {
        return $this->hasMany(CourseDates2::class, 'course2_id')
            ->orderBy('date', 'asc')->orderBy('hour', 'asc');
    }

    public function groups()
    {
        return $this->hasMany(CourseGroups2::class, 'course2_id')
            ->orderBy('course_groups2.degree_id', 'asc');
    }

    public function priceRanges()
    {
        return $this->hasMany(PriceRange::class, 'course_id');
    }


    /**
     * Admin Restrictions
     */

    public static function canCreate()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_COURSE)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_CREATE, $restrictions));
        }

        return true;
    }

    public static function canView()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_COURSE)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_SHOW, $restrictions));
        }

        return true;
    }

    public static function canEdit()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_COURSE)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_EDIT, $restrictions));
        }

        return true;
    }

    public static function canDelete()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_COURSE)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_DELETE, $restrictions));
        }

        return true;
    }

    /**
     * Helpers
     */

    public function getDurationInMinutes()
    {
        $from = Carbon::createFromTimeString('00:00:00');
        $to = Carbon::parse($this->duration);
        return $from->diffInMinutes($to);
    }

    public function getSubtitle()
    {
        // TODO TBD As of 2022-10 all Admin messages are in French, not user's lang
        $subtitle = __('bookings.courseType.' . $this->course_type_id);
        if ($this->sport)
        {
            $subtitle .= ' ' . $this->sport->name;
        }

        return $subtitle;
    }


    public function getIcon()
    {
        $ci = CourseIcon::where('course_type_id', $this->course_type_id)->where('sport_id', $this->sport_id)->first();
        return $ci ? $ci->icon : '';
    }


    /**
     * Convert "this" Course fields to an array.
     * Note that as of 2022-10:
     *   - Frontend prefers bools ("confirm_attendance", "active") as integers.
     *   - Start & End dates both on Y-m-d format and also d/m/Y.
     *   - Duration as a full string and also separated hours - minutes - seconds.
     *   - For retrocompatibility, Supertype converted to 2 separate fields.
     *   - If it's a "definite" course, include also its dates.
     */




    /**
     * Return an array telling which dates a certain School has any Course.
     * Filter params:
     *  - SchoolID, required
     *  - SportID, optional (0=any)
     *  - CourseSupertypeID, optional (0=any)
     *  - Recommended age, optional and just for "definite" courses (1=any)
     *  - Degree, optional and just for "definite" courses (0=any)
     *  - StationID, optional (0=any)
     */
    public static function searchDatesWithCourses($schoolID, $sportID = 0, $courseSupertypeID = 0, $recommendedAge = 1, $degreeID = 0, $stationID = 0)
    {
        $list = [];

        // As of 2022-10-26, dates range from "today" to 6 months; prefill it with falses
        $carbonFrom = Carbon::now()->setTime(0, 0, 0);
        $dateFrom = $carbonFrom->toDateString();
        $carbonTo = Carbon::now()->addMonths(6)->lastOfMonth()->setTime(23, 59, 59);
        $dateTo = $carbonTo->toDateString();

        $carbonTemp = $carbonFrom->clone();
        while ($carbonTemp->lte($carbonTo))
        {
            $list[$carbonTemp->toDateString()] = [
                'date' => $carbonTemp->toDateString(),
                'collective' => 0,
                'private' => 0
            ];
            $carbonTemp->addDay();
        }

        // 1. For "definite" courses: days where they start (i.e. its min value at course_dates2)
        if ($courseSupertypeID == 0 || $courseSupertypeID == CourseSupertype::ID_DEFINITE)
        {
            $query = Course2::join('course_groups2 AS CG2', 'courses2.id', '=', 'CG2.course2_id')
                ->join('course_dates2 AS CD2', 'courses2.id', '=', 'CD2.course2_id')
                ->where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_DEFINITE)
                ->where('active', '=', 1)
                ->whereNull('CG2.deleted_at')
                ->whereNull('CD2.deleted_at')
                ->selectRaw('courses2.id, MIN(CD2.Date) AS date_start')
                ->groupBy('CD2.course2_id')
                ->havingRaw('(date_start BETWEEN ? AND ?)', [$dateFrom, $dateTo]);

            // N.B: join with course_groups2 to ensure it has at least one Group.

            // Optional filters: by sport; by recommended age (currently hardcoded; 1=any); by degree; by station.
            // As of 2022-11-21 no further filter by price, nor duration,
            // nor available places (because we don't know how many people will book now;
            // nor which Group they'll pick @see $this->searchCoursesAtDate() )
            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);
            }

            if ($recommendedAge > 1)
            {
                $query->where('CG2.recommended_age', '=', $recommendedAge);
            }

            if ($degreeID > 0)
            {
                $query->where('CG2.degree_id', '=', $degreeID);
            }

            if ($stationID > 0)
            {
                $query->where('courses2.station_id', '=', $stationID);
            }

            foreach ($query->get() as $c)
            {
                $list[ $c->date_start ]['collective'] = 1;
            }
        }

        // 2. For "loose" courses: days comprised between their "date_start" and "date_end"
        if ($courseSupertypeID == 0 || $courseSupertypeID == CourseSupertype::ID_LOOSE)
        {
            $query = Course2::where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_LOOSE)
                ->where('active', '=', 1)
                ->whereRaw('((date_start BETWEEN ? AND ?) OR ' .
                    '(date_end BETWEEN ? AND ?) OR ' .
                    '(date_start <= ? AND date_end >= ?))', [
                    $dateFrom, $dateTo,
                    $dateFrom, $dateTo,
                    $dateFrom, $dateTo,
                ])
                ->select('date_start', 'date_end');

            // Optional filters: by sport; by station. No more by now.
            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);
            }

            if ($stationID > 0)
            {
                $query->where('station_id', '=', $stationID);
            }

            foreach ($query->get() as $c)
            {
                // Pick its dates inside desired range
                $courseFrom = Carbon::parse($c->date_start);
                $courseTo = Carbon::parse($c->date_end);

                while ($courseFrom->lte($courseTo))
                {
                    if ($courseFrom->gte($carbonFrom) && $courseFrom->lte($carbonTo) )
                    {
                        $list[ $courseFrom->toDateString() ]['private'] = 1;
                    }

                    $courseFrom->addDay();
                }
            }
        }

        return array_values($list);
    }


    /**
     * Return an array of Courses from a certain School at a certain date.
     * Filter params:
     *  - SchoolID, required
     *  - Date, required
     *  - SportID, required
     *  - CourseSupertypeID, required
     *  - Recommended age, optional and just for "definite" courses (1=any)
     *  - Degree, optional and just for "definite" courses (0=any)
     *  - StationID, optional (0=any)
     */
    public static function searchCoursesAtDate($schoolID, $whichDayStart = null, $whichDayEnd = null, $sportID = 0,
                                               $courseSupertypeID, $recommendedAge = 1, $degreeID = [], $stationID = 0)
    {
        $list = [];
        $query = null;

        // 1. For "definite" courses: must start on desired date (i.e. its min value at course_dates2)
        if ($courseSupertypeID == CourseSupertype::ID_DEFINITE)
        {
            $query = Course2::join('course_groups2 AS CG2', 'courses2.id', '=', 'CG2.course2_id')
                ->join('course_dates2 AS CD2', 'courses2.id', '=', 'CD2.course2_id')
                ->where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_DEFINITE)
                ->where('active', '=', 1)
                ->whereNull('CG2.deleted_at')
                ->whereNull('CD2.deleted_at')
                ->selectRaw('courses2.*')
                ->groupBy('CD2.course2_id');

            // N.B: join with course_groups2 to ensure it has at least one Group.

            // Optional filters: by recommended age (currently hardcoded; 1=any); by degree; by station.
            // As of 2022-11-21 no further filter by price, nor duration, etc.
            if ($recommendedAge > 1)
            {
                $query->where('CG2.recommended_age', '=', $recommendedAge);
            }

            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);
            }

            if (!empty($degreeID))
            {
                $degreeIDs = array_map('intval', $degreeID); // Convierte todos los elementos del array a enteros
                $query->whereIn('CG2.degree_id', $degreeIDs);
            }

            if ($stationID > 0)
            {
                $query->where('courses2.station_id', '=', $stationID);
            }

            if ($whichDayStart && $whichDayEnd)
            {
                $query->whereBetween('CD2.date', [$whichDayStart, $whichDayEnd]);
            } else {
                $query->where('CD2.date', '>=', DB::raw('date(now())'));
            }


            $degreesList = [];
            foreach (Sport::get() as $s)
            {
                $degreesList[$s->id] = [];
                foreach (DegreeSchoolSport::listBySchoolAndSport($schoolID, $s->id) as $d)
                {
                    $degreesList[$s->id][$d->degree_id] = $d;
                }
            }

           /* foreach ($query->orderBy('created_at', 'desc')->get()->groupBy('group_id') as $key=>$courseGroup) {
                if(!$key) {
                    foreach ($courseGroup as $course) {

                        $list[] = Course2::generateCourseData($course, $recommendedAge, $degreesList);
                    }
                } else {
                    $i = 0;
                    $coursesGrouped=[];
                    foreach ($courseGroup as $course) {
                        if($i !== 0) {
                            $coursesGrouped[] = Course2::generateCourseData($course, $recommendedAge, $degreesList);
                        }
                        $i ++;
                    }
                    $courseGruped = Course2::generateCourseData($courseGroup[0], $recommendedAge, $degreesList);
                    $courseGruped['courses'] = $coursesGrouped;
                    $list[] = $courseGruped;
                }
            }*/
            foreach ($query->orderBy('name', 'asc')->get() as $c)
            {
                $courseDetails = $c->toArray();

                // Pick all its Groups at desired age/degree
                $queryG = CourseGroups2::where('course_groups2.course2_id', '=', $c->id);
                if ($recommendedAge > 1)
                {
                    $queryG->where('recommended_age', '=', $recommendedAge);
                }

                if ($degreeID > 0)
                {
                    $queryG->where('degree_id', '=', $degreeID);
                }

                $courseDetails['groups'] = [];

                foreach ($queryG->orderBy('degree_id', 'asc')->get() as $group)
                {
                    // Check Group has at least one place available: either on its existing Subgroups,
                    // or could create a new Subgroup.
                    // N.B: just _one_ because as of 2022-11 Groupal Bookings don't exist anymore,
                    // users book place-by-place; but we CAN'T control if somebody else picked
                    // "the last one" at his Frontend Shopping Cart - must recheck at BookingController->createBooking()
                    $placesLeft = false;
                    $subgroupCount = CourseGroupsSubgroups2::where('course_group2_id', '=', $group->id)->count();
                    if ($subgroupCount < $group->teachers_max)
                    {
                        $placesLeft = true;
                    }
                    else
                    {
                        $placesTotal = $subgroupCount * $courseDetails['max_participants'];
                        $placesLeft = (BookingUsers2::countByGroup($group->id) < $placesTotal);
                    }

                    if ($placesLeft)
                    {
                        $groupDetails = $group->toArray();

                        // Include its Degree info
                        $whichDegree = $degreesList[ $courseDetails['sport_id'] ][ $groupDetails['degree_id'] ] ?? null;
                        $groupDetails['league'] = $whichDegree ? $whichDegree->degree->league : '';
                        $groupDetails['annotation'] = $whichDegree ? ($whichDegree->annotation ?? '') : '';
                        $groupDetails['level'] = $whichDegree ? ($whichDegree->name ?? '') : '';
                        $groupDetails['color'] = $whichDegree ? $whichDegree->degree->color : '';

                        $courseDetails['groups'][] = $groupDetails;
                    }
                }

                if (count($courseDetails['groups']) > 0)
                {
                    $list[] = $courseDetails;
                }
            }


        }

        // 2. For "loose" courses: desired date is between their "date_start" and "date_end"
        // TODO
        // TBD
        // What about course's "max_participants" ??
        // (it was meant for groupal bookings, ex. 1 booking with 3 people, but that doesn't exist anymore
        // so now 1000 people can book this course - requiring 1000 monitors)
        // Cf. restriction on Group places
        else if ($courseSupertypeID == CourseSupertype::ID_LOOSE)
        {
            $query = Course2::where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_LOOSE)
                ->where('active', '=', 1);


            // Optional filters: by station.
            if ($whichDayStart && $whichDayEnd)
            {
                $query->whereRaw('( ( date_start <= ? AND date_end >= ? ) OR ( date_start >= ? AND date_start <= ?))',
                    [date($whichDayStart),date($whichDayStart),date($whichDayStart),date($whichDayEnd)]);
            } else {
                $query->where('date_end', '>=', DB::raw('date(now())'));
            }
            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);
            }
            if ($stationID > 0)
            {
                $query->where('station_id', '=', $stationID);
            }

            // Obtén la fecha actual
            $currentDate = date('Y-m-d');
            // Verificar si el curso NO está en una fecha reservable si los campos no son nulos
            $query->where(function ($query) use ($currentDate) {
                $query->where(function ($query) use ($currentDate) {
                    $query->whereNull('date_start_res')
                        ->orWhereNull('date_end_res')
                        ->orWhere(function ($query) use ($currentDate) {
                            $query->where('date_start_res', '<=', $currentDate)
                                ->where('date_end_res', '>=', $currentDate);
                        });
                });
            });

            foreach ($query->orderBy('name', 'asc')->get() as $c)
            {
                $courseDetails = $c->toArray();
                $courseDetails['groups'] = [];
                $list[] = $courseDetails;
            }
        }

        return $list;
    }

    public static function searchCoursesIframeAtDate($schoolID, $whichDayStart = null, $whichDayEnd = null, $sportID = 0,
                                               $courseSupertypeID, $recommendedAge = 1, $degreeID = [], $stationID = 0)
    {
        $list = [];
        $query = null;

        // 1. For "definite" courses: must start on desired date (i.e. its min value at course_dates2)
        if ($courseSupertypeID == CourseSupertype::ID_DEFINITE)
        {
            $query = Course2::join('course_groups2 AS CG2', 'courses2.id', '=', 'CG2.course2_id')
                ->join('course_dates2 AS CD2', 'courses2.id', '=', 'CD2.course2_id')
                ->where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_DEFINITE)
                ->where('active', '=', 1)
                ->whereNull('CG2.deleted_at')
                ->whereNull('CD2.deleted_at')
                ->selectRaw('courses2.*');

            // N.B: join with course_groups2 to ensure it has at least one Group.

            // Optional filters: by recommended age (currently hardcoded; 1=any); by degree; by station.
            // As of 2022-11-21 no further filter by price, nor duration, etc.
            if ($recommendedAge > 1)
            {
                $query->where('CG2.recommended_age', '=', $recommendedAge);
            }

            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);
            }

            if (!empty($degreeID))
            {
                $degreeIDs = array_map('intval', $degreeID); // Convierte todos los elementos del array a enteros
                $query->whereIn('CG2.degree_id', $degreeIDs);
            }

            if ($stationID > 0)
            {
                $query->where('courses2.station_id', '=', $stationID);
            }

            if ($whichDayStart && $whichDayEnd)
            {
                $query->whereBetween('CD2.date', [$whichDayStart, $whichDayEnd]);
            } else {
                $query->where('CD2.date', '>=', DB::raw('date(now())'));
            }


            $degreesList = [];
            foreach (Sport::get() as $s)
            {
                $degreesList[$s->id] = [];
                foreach (DegreeSchoolSport::listBySchoolAndSport($schoolID, $s->id) as $d)
                {
                    $degreesList[$s->id][$d->degree_id] = $d;
                }
            }

            /* foreach ($query->orderBy('created_at', 'desc')->get()->groupBy('group_id') as $key=>$courseGroup) {
                 if(!$key) {
                     foreach ($courseGroup as $course) {

                         $list[] = Course2::generateCourseData($course, $recommendedAge, $degreesList);
                     }
                 } else {
                     $i = 0;
                     $coursesGrouped=[];
                     foreach ($courseGroup as $course) {
                         if($i !== 0) {
                             $coursesGrouped[] = Course2::generateCourseData($course, $recommendedAge, $degreesList);
                         }
                         $i ++;
                     }
                     $courseGruped = Course2::generateCourseData($courseGroup[0], $recommendedAge, $degreesList);
                     $courseGruped['courses'] = $coursesGrouped;
                     $list[] = $courseGruped;
                 }
             }*/
            $query1 = Course2::with('station')
                ->whereIn('group_id', $query->pluck('group_id'));
            foreach ($query1->orderBy('name', 'asc')->get() as $c)
            {
                $courseDetails = $c->toArray();
                $courseDetails['groups'] = [];
                $list[] = $courseDetails;

            }


        }

        // 2. For "loose" courses: desired date is between their "date_start" and "date_end"
        // TODO
        // TBD
        // What about course's "max_participants" ??
        // (it was meant for groupal bookings, ex. 1 booking with 3 people, but that doesn't exist anymore
        // so now 1000 people can book this course - requiring 1000 monitors)
        // Cf. restriction on Group places
        else if ($courseSupertypeID == CourseSupertype::ID_LOOSE)
        {
            $query = Course2::where('school_id', '=', $schoolID)
                ->where('course_supertype_id', '=', CourseSupertype::ID_LOOSE)
                ->where('active', '=', 1);

            // Optional filters: by station.
            if ($whichDayStart && $whichDayEnd)
            {

                $query->whereRaw('( ( date_start <= ? AND date_end >= ? ) OR ( date_start >= ? AND date_start <= ?))',
                    [date($whichDayStart),date($whichDayStart),date($whichDayStart),date($whichDayEnd)]);

            } else {

                $query->where('date_end', '>=', DB::raw('date(now())'));

            }
            if ($sportID > 0)
            {
                $query->where('sport_id', '=', $sportID);

            }
            if ($stationID > 0)
            {
                $query->where('station_id', '=', $stationID);
            }

            // Obtén la fecha actual
            $currentDate = date('Y-m-d');
            // Verificar si el curso NO está en una fecha reservable si los campos no son nulos
            $query->where(function ($query) use ($currentDate) {
                $query->where(function ($query) use ($currentDate) {
                    $query->whereNull('date_start_res')
                        ->orWhereNull('date_end_res')
                        ->orWhere(function ($query) use ($currentDate) {
                            $query->where('date_start_res', '<=', $currentDate)
                                ->where('date_end_res', '>=', $currentDate);
                        });
                });
            });

            foreach ($query->orderBy('name', 'asc')->get() as $c)
            {
                $courseDetails = $c->toArray();
                $courseDetails['groups'] = [];
                $list[] = $courseDetails;
            }
        }

        return $list;
    }


    public static function generateCourseData($c, $recommendedAge, $degreeID) {
        $courseDetails = $c->toArray();

        if($courseDetails['group_id']) {

            $groupedCourses = Course2::where('group_id', $courseDetails['group_id'])->get();
            /*$groupedCourses = $groupedCourses->reject(function ($value, $key) use ($courseDetails) {
                return $value->id == $courseDetails['id'];
            })->values();*/

            $courseDetails['courses'] = $groupedCourses;
        }

        // Pick all its Groups at desired age/degree
        $queryG = CourseGroups2::where('course_groups2.course2_id', '=', $c->id);
        if ($recommendedAge > 1)
        {
            $queryG->where('recommended_age', '=', $recommendedAge);
        }

        if ($degreeID > 0)
        {
            $queryG->where('degree_id', '=', $degreeID);
        }

        $courseDetails['groups'] = [];

        foreach ($queryG->orderBy('degree_id', 'asc')->get() as $group)
        {
            // Check Group has at least one place available: either on its existing Subgroups,
            // or could create a new Subgroup.
            // N.B: just _one_ because as of 2022-11 Groupal Bookings don't exist anymore,
            // users book place-by-place; but we CAN'T control if somebody else picked
            // "the last one" at his Frontend Shopping Cart - must recheck at BookingController->createBooking()
            $placesLeft = false;
            $subgroupCount = CourseGroupsSubgroups2::where('course_group2_id', '=', $group->id)->count();
            if ($subgroupCount < $group->teachers_max)
            {
                $placesLeft = true;
            }
            else
            {
                $placesTotal = $subgroupCount * $courseDetails['max_participants'];
                $placesLeft = (BookingUsers2::countByGroup($group->id) < $placesTotal);
            }

            if ($placesLeft)
            {
                $groupDetails = $group->toArray();

                // Include its Degree info
                $whichDegree = $degreesList[ $courseDetails['sport_id'] ][ $groupDetails['degree_id'] ] ?? null;
                $groupDetails['league'] = $whichDegree ? $whichDegree->degree->league : '';
                $groupDetails['annotation'] = $whichDegree ? ($whichDegree->annotation ?? '') : '';
                $groupDetails['level'] = $whichDegree ? ($whichDegree->name ?? '') : '';
                $groupDetails['color'] = $whichDegree ? $whichDegree->degree->color : '';

                $courseDetails['groups'][] = $groupDetails;
            }
        }

        return $courseDetails;

    }


    /**
     * Imagine a "definite" Course with X Subgroups, each of them with an assigned Monitor.
     * Then Course's dates/hours/duration change.
     * Maybe some of those Monitors are not available at the new schedule.
     * As of 2022-11-09 UNASSIGN the Subgroup - so Admin should pick another Monitor.
     *
     * Compare with App\Http\Requests\PlannerRequest->withValidator() + getOverlapBox()
     * and App\Models\CourseGroupsSubgroups2->avoidMonitorOverlaps()
     */
    public function checkCourseSubgroupsMonitors()
    {
        if ($this->course_supertype_id == CourseSupertype::ID_DEFINITE)
        {
            // Pick all its dates
            $this->loadMissing(['course_dates', 'school']);
            $datesList = $this->toArray()['dates'];

            if (count($datesList) > 0)
            {
                // Pick each Subgroup with assigned Monitor
                foreach ($this->groups as $group)
                {
                    foreach ($group->subgroups as $subgroup)
                    {
                        if ($subgroup->monitor_id != null)
                        {
                            // Check if he's available at each scheduled date
                            $monitorAvailable = true;
                            foreach ($datesList as $d)
                            {
                                // Build calendar
                                $startTime = Carbon::parse($d['date'] . ' ' . $d['hour']);
                                $endTime = Carbon::parse($d['date'] . ' ' . $d['hour_end']);

                                $calendar = new Calendar($startTime, $endTime);
                                $calendar->setSchool($this->school);
                                $calendar->setMonitor($subgroup->monitor);


                                // Check overlap: private courses
                                $privateData = $calendar->getPrivateCourses()->get();
                                if (count($privateData) > 0)
                                {
                                    foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                                    {
                                        // Adjust seconds to allow start from other end
                                        $startTime2 = Carbon::parse($box->from)->addSecond();
                                        $endTime2 = Carbon::parse($box->to)->subSecond();

                                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                                        {
                                            $monitorAvailable = false;
                                            break(2);
                                        }
                                    }
                                }


                                // Check overlap: collective courses
                                $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                                if (count($collectiveData) > 0)
                                {
                                    foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                                    {
                                        if ($box->detail->id != $subgroup->id)
                                        {
                                            $startTime2 = Carbon::parse($box->from)->addSecond();
                                            $endTime2 = Carbon::parse($box->to)->subSecond();

                                            if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                                                $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                                            {
                                                $monitorAvailable = false;
                                                break(2);
                                            }
                                        }
                                    }
                                }


                                // Check overlap: NWD
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
                                            $monitorAvailable = false;
                                            break(2);
                                        }
                                    }
                                }
                            }

                            // So if he's not available, unassign the Subgroup
                            if (!$monitorAvailable)
                            {
                                $subgroup->monitor_id = null;
                                $subgroup->save();
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Duplicate "this" Course:
     *   - Its basic data.
     *   - If it's "definite", also its dates, groups and subgroups (without Monitors nor booked Users)
     */
    public function duplicate($isGrouped = 0)
    {
        // Duplicar el curso actual
        $newCourse = $this->replicate();
        $newCourse->name = $this->name . ' - Copy';
        $newCourse->save();

        if ($this->global_course && !$isGrouped) {
            // Duplicar el global_course si existe
            $newGlobalCourse = $this->global_course->replicate();
            $newGlobalCourse->save();

            // Actualizar la group_id del nuevo curso al ID del nuevo global_course
            $newCourse->group_id = $newGlobalCourse->id;
            $newCourse->save();

            // Buscar cursos con la misma group_id y duplicarlos
            $groupedCourses = Course2::where('group_id', $this->group_id)->get();

            foreach ($groupedCourses as $course) {
                // No dupliques el curso original nuevamente
                if ($course->id != $this->id) {
                    $course->duplicate($newGlobalCourse->id);
                }
            }
        } elseif ($isGrouped) {
            // Si es un curso agrupado, actualiza la group_id con el valor proporcionado
            $newCourse->group_id = $isGrouped;
            $newCourse->save();
        }

        // Duplicar las fechas del curso
        foreach ($this->priceRanges as $priceRange) {
            $newPrice = $priceRange->replicate();
            $newPrice->course_id = $newCourse->id;
            $newPrice->save();
        }

        // Duplicar las fechas del curso
        foreach ($this->course_dates as $date) {
            $newDate = $date->replicate();
            $newDate->course2_id = $newCourse->id;
            $newDate->save();
        }

        // Duplicar los grupos y subgrupos
        foreach ($this->groups as $group) {
            $newGroup = $group->replicate();
            $newGroup->course2_id = $newCourse->id;
            $newGroup->save();

            foreach ($group->subgroups as $subgroup) {
                $newSubgroup = $subgroup->replicate();
                $newSubgroup->course_group2_id = $newGroup->id;
                $newSubgroup->save();
            }
        }
    }


    /**
     * From 2022-11-21, Schools can choose in which StationID is each Course.
     * Fill that new "station_id" field on existing Courses, with School's first one.
     */
    public static function fillStationID()
    {
        foreach (Course2::whereNull('station_id')
                     ->select('school_id')->distinct()
                     ->pluck('school_id') as $schoolID)
        {
            $schoolData = School::with('stations')->find($schoolID);
            if ($schoolData && count($schoolData->stations) > 0)
            {
                Course2::withTrashed()
                    ->whereNull('station_id')
                    ->where('school_id', '=', $schoolID)
                    ->update(['station_id' => $schoolData->stations->first()->id]);
            }
        }
    }

    /**
     * Genera un array con las horas para reservar.
     *
     * @param $hour_start
     * @param $hour_end
     * @return array
     */
    public static function getDayTimes($hour_start, $hour_end) {

        $start_time = strtotime($hour_start);
        $end_time = strtotime($hour_end);

        $hour_range = array();
        $interval = 5 * 60; // 5 minutes in seconds

        for ($i = $start_time + $interval - ($start_time % $interval); $i < $end_time; $i += $interval) {
            $hour_range[] = date("H:i", $i);
        }

        return $hour_range;
    }

    /**
     * Genera un array con tramos de horas de 15 minutos.
     *
     * @return array
     */
    public static function getQuarterHourRange() {
        $hour_range = array();
        $interval = 15 * 60; // 15 minutes in seconds

        for ($i = strtotime('00:15'); $i <= strtotime('07:00'); $i += $interval) {
            $hour_range[] = date("H:i", $i);
        }

        return $hour_range;
    }
}
