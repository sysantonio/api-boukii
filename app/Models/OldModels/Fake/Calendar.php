<?php

namespace App\Models\OldModels\Fake;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Models\OldModels\BookingUsers2;
use App\Models\OldModels\CourseDates2;
use App\Models\OldModels\CourseGroupsSubgroups2;
use App\Models\OldModels\CourseType;
use App\Models\OldModels\DegreeSchoolSport;
use App\Models\OldModels\School;
use App\Models\OldModels\User;
use App\Models\OldModels\UserNwd;
use App\Models\OldModels\UserGroups;
use App\Models\OldModels\UserSport;
use App\Models\OldModels\Task;

class Calendar
{

    const TYPE_PRIVATE_COURSE = 'private_course';
    const TYPE_COLLECTIVE_COURSE = 'collective_course';
    const TYPE_NWD = 'nwd';
    const TYPE_TASK = 'task';

    private $start;
    private $end;

    private $filterSchool;
    private $filterMonitor;
    private $filterClients;
    private $filterSports;

    function __construct(Carbon $start = null, Carbon $end = null)
    {
        $this->start = $start ?? Carbon::today();
        $this->end = $end ?? $this->start;
    }

    public function merged(...$data)
    {
        $merged = new Collection;
        foreach ($data as $value) {
            $merged = $merged->merge($value);
        }
        return $merged;
    }

    public function setStart(Carbon $start)
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd(Carbon $end)
    {
        $this->end = $end;
        return $this;
    }

    public function setSchool(School $school)
    {
        $this->filterSchool = $school;
        return $this;
    }

    public function setMonitor(?User $monitor)
    {
        $this->filterMonitor = $monitor;
        return $this;
    }
    public function getMonitor()
    {
        return $this->filterMonitor;
    }

    public function setClient(User $client, $includeSecondaries = true)
    {
        if ($includeSecondaries)
        {
            $this->filterClients = UserGroups::where('user_main_id', $client->id)->pluck('user_secondary_id');
        }
        else
        {
            $this->filterClients = new Collection;
        }

        $this->filterClients->push($client->id);

        return $this;
    }
    public function getClient()
    {
        return $this->filterClients;
    }

    public function setSports($sportsID)
    {
        $this->filterSports = $sportsID;
        return $this;
    }

    public function toItems($type, Collection $source, $verboseDetails = false, $monitor = null , $client = null)
    {
        return $source->map(function($data) use ($type, $verboseDetails, $monitor , $client) {
            return $this->toItem($type, $data, $verboseDetails, $monitor , $client);
        });
    }

    private function buildItem($type, Carbon $date, Carbon $start, Carbon $end, $detail = null)
    {
        return (object)[
            'type' => $type,
            'date' => $date->toDateString(),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'duration' => $start->diffInMinutes($end),
            'from' => $date->setTimeFrom($start)->toDateTimeString(),
            'to' => $date->setTimeFrom($end)->toDateTimeString(),
            'detail' => $detail,
        ];
    }

    public function toItem($type, $data, $verboseDetails = false, $monitor = null, $client = null)
    {
        switch ($type)
        {
            case self::TYPE_PRIVATE_COURSE:
            {
                // Item's date & time (set by booking's duration)
                $start = $data->date->copy()->setTimeFrom($data->hour);
                $end = $start->copy()->addMinutes($data->getDurationInMinutes());
                // Details about course
                $course = $data->course;

                $monitorData = User::find($data->monitor_id);

                $details = [
                    'id' => $data->id, // booking_users2.id
                    'color' => $data->color,
                    'course_id' => $course->id,
                    'name' => $course->name,
                    'subtitle' => $course->getSubtitle(),
                    'type' => $course->course_type_id,
                    'color_nwd' => $data->color,
                    'sport' => [
                        'id' => $course->sport_id,
                        'name' => $course->sport->name,
                        'icon' => $course->sport->icon_selected
                    ],
                    'school_id' => $course->school_id,
                    'station' => [
                        'id' => $course->station->id,
                        'name' => $course->station->name
                    ],
                    'monitor' => [
                        'id' => $monitorData ? $monitorData->id : 0,
                        'first_name' => $monitorData ? $monitorData->first_name : '',
                        'last_name' => $monitorData ? $monitorData->last_name : '',
                        'image' => $monitorData ? $monitorData->image : ''
                    ]
                ];

                // Optional verbose details about the Client who booked it
                if ($verboseDetails)
                {
                    $details['price'] = $data->price;
                    $details['currency'] = $data->currency;

                    $userData = User::find($data->user_id);
                    $degreeData = $userData
                                    ? UserSport::getCurrentDegreeByUserAndSportAndSchool($data->user_id, $course->sport->id, $course->school_id)
                                    : null;

                    $details['degree'] = [
                        'league' => $degreeData ? $degreeData->league : '',
                        'name' => $degreeData ? ($degreeData->name ?? '') : '',
                        'annotation' => $degreeData ? ($degreeData->annotation ?? '') : '',
                        'level' => $degreeData ? ($degreeData->name ?? '') : '',
                        'color' => $degreeData ? $degreeData->color : ''
                    ];

                    $details['user'] = [
                        'first_name' => $userData ? $userData->first_name : '',
                        'last_name' => $userData ? $userData->last_name : '',
                        'years_old' => $userData ? $userData->getYearsOld() : 0,
                        'language_code' => $userData ? $userData->getFirstLanguageCode() : '',
                        'image' => $userData ? $userData->getImageUrl() : ''
                    ];
                }

                return $this->buildItem(
                    $type,
                    $data->date,
                    $start,
                    $end,
                    (object)$details
                );

                break;
            }
            case self::TYPE_COLLECTIVE_COURSE:
            {
                // Item's date & time (set by root course's duration)
                $course = $data->course;
                $start = $data->date->copy()->setTimeFrom($data->hour);
                $end = $start->copy()->addMinutes($course->getDurationInMinutes());

                // Details about course
                /* $grouping = [
                    'groups' => $data->groups,
                    'subgroups' => $data->subgroups,
                    'bookings' => $data->bookings,
                ]; */

                $details = [
                    'id' => $data->grouping_id,
                    'color' => $data->color,
                    'course_id' => $course->id,
                    'name' => $course->name,
                    'subtitle' => $course->getSubtitle(),
                    'type' => $course->course_type_id,
                    'sport' => [
                        'id' => $course->sport_id,
                        'name' => $course->sport->name,
                        'icon' => $course->sport->icon_selected
                    ],
                    'school_id' => $course->school_id,
                    'station' => [
                        'id' => $course->station->id,
                        'name' => $course->station->name
                    ]
                    //'grouping' => $grouping,
                ];

                // Optional verbose details about the Subgroup
                if ($verboseDetails && $data->subgroups > 0)
                {
                    $courseDetails = $course->toArray();
                    $details['price'] = $courseDetails['price'];
                    $details['currency'] = $courseDetails['currency'];
                    $details['past_dates'] = $courseDetails['past_dates'];
                    $details['total_dates'] = $courseDetails['total_dates'];

                    $subgroupData = CourseGroupsSubgroups2::with('group')->find($data->grouping_id);
                    $degree_id;
                    $degreeData;

                    //Default
                    $degreeData = ($subgroupData && $subgroupData->group)
                        ? DegreeSchoolSport::findBySchoolAndSportAndDegree($courseDetails['school_id'], $courseDetails['sport_id'], $subgroupData->group->degree_id)
                        : null;

                    if($monitor){
                        $details['subgroup_id'] = DB::select(DB::raw("SELECT course_groups_subgroups2.id FROM course_groups_subgroups2 , course_groups2 where course_groups_subgroups2.course_group2_id = course_groups2.id and course_groups_subgroups2.monitor_id='".$monitor."' and course_groups2.course2_id='".$details['course_id']."'"))[0]->id ?? NULL;

                        $degree_id = DB::select(DB::raw("SELECT course_groups2.degree_id FROM course_groups2 , course_groups_subgroups2 where course_groups_subgroups2.id='".$details['subgroup_id']."' and course_groups_subgroups2.course_group2_id = course_groups2.id"))[0]->degree_id ?? NULL;

                        $degreeData = ($subgroupData && $subgroupData->group)
                        ? DegreeSchoolSport::findBySchoolAndSportAndDegree($courseDetails['school_id'], $courseDetails['sport_id'], $degree_id ?? NULL)
                        : null;
                    }
                    if($client){
                        $subgroup_id = DB::select(DB::raw("SELECT booking_users2.* FROM booking_users2 , course_groups_subgroups2 , course_groups2 where booking_users2.user_id='".$client."' and booking_users2.course_groups_subgroup2_id = course_groups_subgroups2.id and course_groups_subgroups2.course_group2_id = course_groups2.id and course_groups2.course2_id='".$details['course_id']."'"))[0]->course_groups_subgroup2_id ?? NULL;
                        $details['subgroup_id'] = $subgroup_id;
                        $subgroup = null;

                        if ($subgroup_id) {
                            $subgroup = CourseGroupsSubgroups2::with('group', 'subgroups_dates')->find($subgroup_id);
                        }

                        $monitorSubgroup = $subgroup ? User::find($subgroup->monitor_id) : null;

                        $details['monitor'] = [
                            'id' => $monitorSubgroup ? $monitorSubgroup->id : 0,
                            'first_name' => $monitorSubgroup ? $monitorSubgroup->first_name : '',
                            'last_name' => $monitorSubgroup ? $monitorSubgroup->last_name : '',
                            'image' => $monitorSubgroup ? $monitorSubgroup->image : ''
                        ];

                        $degree_id = DB::select(DB::raw("SELECT course_groups2.degree_id FROM course_groups2 , course_groups_subgroups2 where course_groups_subgroups2.id='".$details['subgroup_id']."' and course_groups_subgroups2.course_group2_id = course_groups2.id"))[0]->degree_id ?? NULL;

                        $degreeData = ($subgroupData && $subgroupData->group)
                        ? DegreeSchoolSport::findBySchoolAndSportAndDegree($courseDetails['school_id'], $courseDetails['sport_id'], $degree_id ?? NULL)
                        : null;
                    }


                    $details['degree'] = [
                        'league' => $degreeData ? $degreeData->degree->league : '',
                        'level' => $degreeData ? ($degreeData->name ?? '') : '',
                        'annotation' => $degreeData ? ($degreeData->annotation ?? '') : '',
                        'name' => $degreeData ? ($degreeData->name ?? '') : '',
                        'color' => $degreeData ? $degreeData->degree->color : ''
                    ];

                    $details['count_participants'] = $subgroupData ? BookingUsers2::countBySubgroup($subgroupData->id) : 0;
                    $details['max_participants'] = $subgroupData && $subgroupData->max_participants
                        ? $subgroupData->max_participants : $courseDetails['max_participants'];

                    $details['subgroup_position'] = $subgroupData ? $subgroupData->computePosition() : 0;
                }

                return $this->buildItem(
                    $type,
                    $data->date,
                    $start,
                    $end,
                    (object)$details
                );

                break;
            }
            case self::TYPE_NWD:
            {
                // If it is a full day, adjust to the opening hours of the school
                $start = $data->full_day ? School::getOpeningTimes()->start : $data->start_time;
                $end = $data->full_day ? School::getOpeningTimes()->end : $data->end_time;

                $station = $data->station;

                return $this->buildItem(
                    $type,
                    $data->date,
                    $start,
                    $end,
                    (object)[
                        'id' => $data->id, // user_nwd.id
                        'subtype_id' => $data->user_nwd_subtype_id,
                        'subtitle' => $data->description ?? '',
                        'color_nwd' => $data->color ?? '',
                        'school_id' => $data->school_id,
                        'station' => [
                            'id' => $station ? $station->id : null,
                            'name' => $station ? $station->name : ''
                        ]
                    ]
                );

                break;
            }
            case self::TYPE_TASK:
            {
                // TODO TBD as of 2022-11-15 Tasks have a date+time to start
                // but NOT a duration, and thus NOT an end time ??
                // Set to 1 hour by default, just to keep the same structure as the other items

                $start = $data->date->copy()->setTimeFrom($data->time);
                $end = $start->copy()->addHour();
                $task = Task::with('checks')
                ->where('id', '=', intval($data->id))
                ->first();

                return $this->buildItem(
                    $type,
                    $data->date,
                    $start,
                    $end,
                    (object)[
                        'id' => $data->id, // tasks.id
                        'name' => $data->name ?? '',
                        'checks' => $task->checks->toArray() ?? []
                    ]
                );

                break;
            }
            default:
            {
                return null;
                break;
            }
        }
    }

    public function getPrivateCourses()
    {
        $builder = BookingUsers2::select('booking_users2.*')
            //->join('bookings2 AS b', 'b.id', 'booking_users2.booking2_id')
            ->join('courses2 AS c', function($join) {
                $join->on('c.id', 'booking_users2.course2_id')->whereNull('c.deleted_at');
            })
            ->whereNotNull('booking_users2.course2_id')
            ->whereBetween('booking_users2.date', [$this->start->toDateString(), $this->end->toDateString()])
            ->whereNotNull('booking_users2.hour')
            ->where('c.course_type_id', CourseType::ID_PRIVE);

        // Filter by school
        if ($this->filterSchool)
        {
            //$bus->where('b.school_id', $this->filterSchool->id);
            $builder->where('c.school_id', $this->filterSchool->id);
        }

        // Filter by monitor
        if ($this->filterMonitor)
        {
            $builder->where('booking_users2.monitor_id', $this->filterMonitor->id);
        }

        // Filter by clients
        if ($this->filterClients)
        {
            $builder->whereIn('booking_users2.user_id', $this->filterClients->all());
        }

        // Filter by sports
        if ($this->filterSports)
        {
            $builder->whereIn('c.sport_id', $this->filterSports);
        }

        return $builder;
    }


    public function getCollectiveCourses($groupBy = null)
    {
        $builder = CourseDates2::select(
            'course_dates2.*',
            DB::raw('COUNT(DISTINCT cg.id) AS groups'),
            DB::raw('COUNT(DISTINCT cgs.id) AS subgroups'),
            DB::raw('COUNT(DISTINCT bu.id) AS bookings'),
            DB::raw('CASE WHEN subgroups_monitor_dates.monitor_id IS NOT NULL THEN subgroups_monitor_dates.monitor_id ELSE cgs.monitor_id END AS monitor_id'),
            'cgs.id AS grouping_id'
        )
            ->join('courses2 AS c', function($join) {
                $join->on('c.id', '=', 'course_dates2.course2_id')->whereNull('c.deleted_at');
            })
            ->join('course_groups2 AS cg', function($join) {
                $join->on('cg.course2_id', '=', 'c.id')->whereNull('cg.deleted_at');
            })
            ->join('course_groups_subgroups2 AS cgs', function($join) {
                $join->on('cgs.course_group2_id', '=', 'cg.id')->whereNull('cgs.deleted_at');
            })
            ->join('booking_users2 AS bu', function($join) {
                $join->on('bu.course_groups_subgroup2_id', '=', 'cgs.id')->whereNull('bu.deleted_at');
            })
            ->leftJoin('subgroups_monitor_dates', function($join) {
                $join->on('subgroups_monitor_dates.subgroup_id', '=', 'cgs.id')
                    ->whereRaw('DATE(subgroups_monitor_dates.date) = DATE(?)', [$this->start->toDateString()]);
            })
            ->whereBetween('course_dates2.date', [$this->start->toDateString(), $this->end->toDateString()])
            ->where('c.course_type_id', CourseType::ID_COLLECTIF);
       /* $builder = CourseDates2::select(
            'course_dates2.*',
            DB::raw('COUNT(DISTINCT cg.id) AS groups'),
            DB::raw('COUNT(DISTINCT cgs.id) AS subgroups'),
            DB::raw('COUNT(DISTINCT bu.id) AS bookings'),
        )
            ->addSelect(DB::raw('CASE
                WHEN sm.monitor_id IS NOT NULL THEN sm.monitor_id
                ELSE cgs.monitor_id
            END AS monitor_id'))
            ->join('courses2 AS c', function($join) {
                $join->on('c.id', 'course_dates2.course2_id')->whereNull('c.deleted_at');
            })
            ->join('course_groups2 AS cg', function($join) {
                $join->on('cg.course2_id', 'c.id')->whereNull('cg.deleted_at');
            })
            ->join('course_groups_subgroups2 AS cgs', function($join) {
                $join->on('cgs.course_group2_id', 'cg.id')->whereNull('cgs.deleted_at');
            })
            ->join('booking_users2 AS bu', function($join) {
                $join->on('bu.course_groups_subgroup2_id', 'cgs.id')->whereNull('bu.deleted_at');
            })
            ->leftJoin('subgroups_monitor_dates AS sm', function($join) {
                // Unir subgroups_monitor_dates por fecha y subgrupo
                $join->on('sm.subgroup_id', 'cgs.id')
                    ->whereRaw('DATE(sm.date) = DATE('.$this->start->toDateString().')');
            })
            ->whereBetween('course_dates2.date', [$this->start->toDateString(), $this->end->toDateString()])
            ->where('c.course_type_id', CourseType::ID_COLLECTIF);*/

        // Grouping
        switch ($groupBy)
        {
            case 'group':
            {
                $builder->addSelect('cg.id AS grouping_id');
                $builder->groupBy('cg.id'); // By course group
                break;
            }
            case 'subgroup':
            {
                $builder->addSelect('cgs.id AS grouping_id');
                $builder->groupBy('cgs.id'); // By course subgroup
                break;
            }
            case 'booking':
            {
                $builder->addSelect('bu.id AS grouping_id');
                $builder->groupBy('bu.id'); // By booking
                break;
            }
            case 'course':
            {
                $builder->addSelect('c.id AS grouping_id');
                $builder->groupBy('c.id'); // By course
                break;
            }
            default:
            {
                $builder->addSelect('course_dates2.id AS grouping_id');
                $builder->groupBy('course_dates2.id'); // By course date
                break;
            }
        }

        // Having bookings
        // Options to disable without bookings:
        // 1. Change having operator to ">"
        // 2. Change in query: the LEFT JOIN to JOIN on booking_users2 table
        $builder->having('bookings', '>=', 0);

        // Filter by school
        if ($this->filterSchool)
        {
            $builder->where('c.school_id', $this->filterSchool->id);
        }

        // Filter by monitor
        if  ($this->filterMonitor)
        {
            $builder->where('cgs.monitor_id', $this->filterMonitor->id);
        }

        // Filter by clients
        if ($this->filterClients)
        {
            $builder->whereIn('bu.user_id', $this->filterClients->all());
        }

        // Filter by sports
        if ($this->filterSports)
        {
            $builder->whereIn('c.sport_id', $this->filterSports);
        }

        return $builder;
    }

    public function getMonitorNwd()
    {
        $nwds = new Collection;

        if (!$this->filterMonitor)
        {
            return $nwds;
        }

        $startString = $this->start->toDateString();
        $endString = $this->end->toDateString();

        $builder = UserNwd::where('user_id', $this->filterMonitor->id)
            ->whereRaw('((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (? BETWEEN start_date AND end_date) OR (? BETWEEN start_date AND end_date))', [
                $startString, $endString,
                $startString, $endString,
                $startString,
                $endString
            ]);

        // As of 2022-11, Monitors may be linked to several Schools
        // so when searching from a School Admin, pick only NWDs created by "that" School,
        // or created by Monitor from App for all his Schools
        // @see /Controllers/Admin/Monitor/NwdController->store() + /Controllers/Apps/Teach/NwdController->store()
        if ($this->filterSchool)
        {
            $builder->whereRaw('(school_id IS NULL OR school_id = ?)', [$this->filterSchool->id]);
        }

        foreach ($builder->orderBy('start_date', 'desc')->get() as $nwd)
        {
            $start = ($this->start->lte($nwd->start_date) ? $nwd->start_date : $this->start)->copy();
            $end = ($this->end->gte($nwd->end_date) ? $nwd->end_date : $this->end)->copy();

            if ($end->gt($start))
            {
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $nwdCloned = clone $nwd;
                    $nwdCloned->setAttribute('date', $date);
                    $nwds->push($nwdCloned);
                }
            }
            else
            {
                $nwd->setAttribute('date', $start);
                $nwds->push($nwd);
            }
        }

        return $nwds;
    }


    public function getSchoolTasks()
    {
        if (!$this->filterSchool)
        {
            return new Collection;
        }

        return Task::where('school_id', $this->filterSchool->id)
                    ->whereBetween('date', [$this->start->toDateString(), $this->end->toDateString()])
                    ->orderBy('date', 'desc')->orderBy('time', 'desc')
                    ->get();
    }
}
