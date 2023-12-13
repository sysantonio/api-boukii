<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use DateInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\Fake\Calendar;

/**
 * Class BookingUsers2
 *
 * @property int $id
 * @property int $booking2_id
 * @property int $user_id
 * @property float $price
 * @property string $currency
 * @property int|null $course_groups_subgroup2_id
 * @property int|null $course2_id
 * @property int|null $monitor_id
 * @property Carbon|null $date
 * @property Carbon|null $hour
 * @property Carbon|null $duration
 * @property bool $attended
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Courses2|null $course
 * @property User|null $monitor
 * @property User $user
 * @property CourseGroupsSubgroups2|null $subgroup
 * @property Bookings2 $booking
 *
 * @package App\Models
 */
class BookingUsers2 extends Model
{
	use SoftDeletes;
	protected $table = 'booking_users2';

	protected $casts = [
		'booking2_id' => 'int',
		'user_id' => 'int',
		'price' => 'float',
		'course_groups_subgroup2_id' => 'int',
		'course2_id' => 'int',
		'monitor_id' => 'int',
		'attended' => 'bool'
	];

	protected $dates = [
		'date',
		'hour',
        'duration'
	];

	protected $connection = 'old';

protected $fillable = [
		'booking2_id',
		'user_id',
		'price',
		'currency',
		'course_groups_subgroup2_id',
		'course2_id',
		'monitor_id',
		'date',
		'hour',
        'duration',
		'attended',
		'color',
        'created_at',
        'updated_at'
	];


    /**
     * Relations
     */

	public function course()
	{
		return $this->belongsTo(Course2::class, 'course2_id');
	}

    public function monitor()
	{
		return $this->belongsTo(User::class, 'monitor_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function subgroup()
	{
		return $this->belongsTo(CourseGroupsSubgroups2::class, 'course_groups_subgroup2_id');
	}

	public function booking()
	{
		return $this->belongsTo(Bookings2::class, 'booking2_id');
	}



    /**
     * Helpers
     */


    public function getDurationInMinutes()
    {
        // Some "loose" courses allow to pick any duration
    	if($this->duration) {
	        $from = Carbon::createFromTimeString('00:00:00');
	        $to = Carbon::parse($this->duration);
	        return $from->diffInMinutes($to);
    	} else {
    		// If there is not... get from the course
    		return $this->course->getDurationInMinutes();
    	}
    }


    public function getRelatedCourseTitle()
    {
        $title = '';
        $this->loadMissing(['course.global_course', 'subgroup']);

        // If booked a "Loose" Course, just pick its name
        if ($this->course)
        {
            $title = $this->course->name;
        }
        // For "Definite" pick root Course name plus Group level
        else if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            $title = $group->course->global_course->name_global;

            $degreesList = [];
            foreach (DegreeSchoolSport::listBySchoolAndSport($this->booking->school_id, $group->course->sport_id) as $d)
            {
                $degreesList[$d->degree_id] = $d;
            }

            $whichDegree = $degreesList[ $group->degree_id ] ?? null;

            if ($whichDegree)
            {
                $title .= ', ' . ($whichDegree->annotation ?? '') . ' ' . ($whichDegree->name ?? '');
            }
        }

        return $title;
    }

    public function getRelatedGroupId()
    {
        $group_id = 0;
        $this->loadMissing(['course', 'subgroup']);

        // For "Definite" pick root Course name plus Group level
      if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            $group_id = $group->course->group_id;
        }

        return $group_id;
    }

    public function getRelatedPrice($days)
    {
        $price = 0;
        $this->loadMissing(['course.global_course', 'subgroup']);

        // For "Definite" pick root Course name plus Group level
        if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            $definitive = $group->course->global_course->is_definitive;

            if($definitive) {
                $price = floatval($group->course->global_course->price_global);
            } else {
                $priceRange = PriceRange::where('time_interval', $days)
                    ->where('group_id', $group->course->group_id)
                    ->where('percentage', '!=', 0)
                    ->first();
                if ($priceRange) {
                    // Si existe $priceRange, calcula el descuento basado en el precio del $priceRange.
                    $descuento = $priceRange->price / 100; // Suponiendo que el descuento se almacena como un porcentaje (ejemplo: 20% se almacena como 20)
                    $precioConDescuento = $group->course->global_course->price_global -
                        ($group->course->global_course->price_global * $descuento);
                    $price = $precioConDescuento * $days;
                }
            }
        }

        return $price;
    }

    public function getRelatedCourseDates()
    {
        $datesList = [];
        $this->loadMissing(['course', 'subgroup']);

        // If booked a "Loose" Course, just pick its date+hour+duration
        if ($this->course)
        {
            $dateStart = Carbon::parse($this->date->toDateString() . ' ' . $this->hour->toTimeString());
            $durationCarbon = Carbon::parse($this->duration);
            $durationHours = $durationCarbon->format('H');
            $durationMinutes = $durationCarbon->format('i');
            $durationSeconds = $durationCarbon->format('s');
            $dateEnd = $dateStart->clone()->addHours($durationHours)
                ->addMinutes($durationMinutes)
                ->addSeconds($durationSeconds);

            $datesList[] = $dateStart->format('d/m/Y H:i') . ' - ' . $dateEnd->format('H:i');
        }
        // For "Definite" pick root Course name plus Group level
        else if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            $course = $group->course;
            $courseDates = $course ? $course->global_course->dates_global : [];



            foreach ($courseDates as $c)
            {
                list($hours, $minutes) = explode(':', $c['duration']);
                //dd(explode(':', $course->global_course->dates_global[0]['duration']));
                $startDate = Carbon::createFromFormat('Y-m-d g:i', $c['date']. ' '. $c['hour']);

                $startDate->add(new DateInterval("PT{$hours}H{$minutes}M00S"));

            // Obtener la nueva fecha y hora
                $newDate = $startDate->format('H:i');
                $datesList[] = $c['date'] . ' ' . $c['hour'] .
                    ' - '.  $newDate;
            }
        }


        return $datesList;
    }

    public function getRelatedBookingCourseDates()
    {
        $datesList = [];
        $this->loadMissing(['course.global_course', 'subgroup']);

        // If booked a "Loose" Course, just pick its date+hour+duration
        if ($this->course)
        {
            $dateStart = Carbon::parse($this->date->toDateString() . ' ' . $this->hour->toTimeString());
            $durationCarbon = Carbon::parse($this->duration);
            $durationHours = $durationCarbon->format('H');
            $durationMinutes = $durationCarbon->format('i');
            $durationSeconds = $durationCarbon->format('s');
            $dateEnd = $dateStart->clone()->addHours($durationHours)
                                    ->addMinutes($durationMinutes)
                                    ->addSeconds($durationSeconds);

            $datesList[] = $dateStart->format('d/m/Y H:i') . ' - ' . $dateEnd->format('H:i');
        }
        // For "Definite" pick root Course name plus Group level
        else if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            $course = $group->course;
            $courseDates = $course ? $course->course_dates : [];
            $c = $courseDates[0];

            list($hours, $minutes) = explode(':', $course->global_course->dates_global[0]['duration']);
            //dd(explode(':', $course->global_course->dates_global[0]['duration']));
            $startDate = $c['hour'];

            $startDate->add(new DateInterval("PT{$hours}H{$minutes}M00S"));

// Obtener la nueva fecha y hora
            $newDate = $startDate->format('H:i');
            $datesList = [$c['date']->format('d/m/Y') . ' ' . $c['hour']->format('H:i') .
                ' - '.  $newDate];

        }

        return $datesList;
    }

    public function getRelatedMonitorName()
    {
        $name = '';
        $this->loadMissing(['course', 'subgroup']);

        // If booked a "Loose" Course, just pick assigned Monitor (if any)
        $monitorData = null;
        if ($this->course)
        {
            $monitorData = $this->monitor;
        }
        // For "Definite" pick Subgroup's assigned Monitor (if any)
        else if ($this->subgroup)
        {
            $monitorData = $this->subgroup->monitor;
        }

        return $monitorData
                    ? ($monitorData->first_name . ' ' . $monitorData->last_name)
                    : 'n/d';
    }


    public static function countBySubgroup($subgroupID)
    {
        return self::where('course_groups_subgroup2_id', $subgroupID)->count();
    }


    public static function countByGroup($groupID)
    {
        $subgroupsID = CourseGroupsSubgroups2::where('course_group2_id', $groupID)->pluck('id')->toArray();

        return (count($subgroupsID) == 0)
                ? 0
                :self::whereIn('course_groups_subgroup2_id', $subgroupsID)->count();
    }


    /**
     * Imagine "this" BookingUser, for a "loose" Course, gets assigned to a Monitor.
     * But he's already assigned to any other BookingUser whose date+time overlap with this one.
     * Or to any Subgroup from a "definite" Course whose dates overlap with this BookingUser.
     * As of 2022-11-09 UNASSIGN all those duplicates: keep him only at this BookingUser.
     *
     * Compare with App\Models\Course2->checkCourseSubgroupsMonitors()
     * and App\Models\CourseGroupsSubgroups2->avoidMonitorOverlap()
     *
     * TODO TBD what if "this" BookingUser overlaps with a NWD ?
     * Keep the BookingUser assignation and delete the NWD ? Discard the assignation ??
     */
    public function avoidMonitorOverlap()
    {
        if ($this->monitor && $this->date && $this->hour)
        {
            // Build calendar
            $startTime = Carbon::parse($this->date->toDateString() . ' ' . $this->hour->toTimeString());
            $endTime = $startTime->clone()->addMinutes($this->getDurationInMinutes());

            $calendar = new Calendar($startTime, $endTime);
            $calendar->setSchool($this->booking->school);
            $calendar->setMonitor($this->monitor);


            // Check overlap: private courses
            $privateData = $calendar->getPrivateCourses()->get();
            if (count($privateData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                {
                    if ($box->detail->id != $this->id)
                    {
                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            BookingUsers2::where('id', '=', $box->detail->id)
                                        ->update(['monitor_id' => null]);
                        }
                    }
                }
            }


            // Check overlap: collective courses
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
                        CourseGroupsSubgroups2::where('id', '=', $box->detail->id)
                                ->update(['monitor_id' => null]);
                    }
                }
            }
        }
    }


    /**
     * Imagine "this" BookingUser, for a "loose" Course, gets assigned to a Monitor.
     * But he's already assigned to any other BookingUser whose date+time overlap with this one.
     * Or to any Subgroup from a "definite" Course whose dates overlap with this BookingUser.
     * Or it's a NWD.
     * As of 2022-11-16 JUST RETURN a true/false and Controller will decide.
     *
     * Compare with $this->avoidMonitorOverlap()
     * and App\Models\CourseGroupsSubgroups2->avoidMonitorOverlap()
     *
     * @return boolean
     */
    public function checkMonitorOverlap()
    {
        if ($this->monitor && $this->date && $this->hour)
        {
            // Build calendar
            $startTime = Carbon::parse($this->date->toDateString() . ' ' . $this->hour->toTimeString());
            $endTime = $startTime->clone()->addMinutes($this->getDurationInMinutes());

            $calendar = new Calendar($startTime, $endTime);
            $calendar->setSchool($this->booking->school);
            $calendar->setMonitor($this->monitor);


            // Check overlap: private courses
            $privateData = $calendar->getPrivateCourses()->get();
            if (count($privateData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                {
                    if ($box->detail->id != $this->id)
                    {
                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            return true;
                        }
                    }
                }
            }


            // Check overlap: collective courses
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
                        return true;
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
                        return true;
                    }
                }
            }
        }

        // Nothing seems to overlap, so:
        return false;
    }


    /**
     * Imagine "this" BookingUser, either for a "definite" or "loose" Course,
     * gets created for a Client User (who will attend it).
     * But he already has any other BookingUser whose date+time overlap with this one.
     * As of 2022-11-16 JUST RETURN a true/false and Controller will decide.
     *
     * Compare with $this->avoidMonitorOverlap()
     * and App\Models\CourseGroupsSubgroups2->avoidMonitorOverlap()
     *
     * @return boolean
     */
    public function checkClientOverlap()
    {
        // If "this" BookingUser is meant for a "definite/collective" Course, he chose a Subgroup;
        // check he hasn't already booked at any other Subgroup within that Course
        if ($this->subgroup)
        {
            $group = $this->subgroup->group;
            if ($group)
            {
                $siblingGroupsID = CourseGroups2::where('course2_id', '=', $group->course2_id)->pluck('id')->toArray();
                $siblingSubgroupsID = CourseGroupsSubgroups2::whereIn('course_group2_id', $siblingGroupsID)->pluck('id')->toArray();

                if (BookingUsers2::where('user_id', '=', $this->user_id)
                                ->whereIn('course_groups_subgroup2_id', $siblingSubgroupsID)
                                ->count() > 1)
                {
                    return true;
                }
            }
        }


        // Pick dates to check:
        //   - If "this" BookingUser is for a "loose/private" Course, he chose a date+hour+duration
        //   - If it's for a "definite/collective" Course, he chose a Subgroup and must check its N dates
        $thisType = null;
        $thisID = -1;
        $datesToCheck = [];

        if ($this->date && $this->hour)
        {
            $thisType = Calendar::TYPE_PRIVATE_COURSE;
            $thisID = $this->id;

            $startTime = Carbon::parse($this->date->toDateString() . ' ' . $this->hour->toTimeString());
            $endTime = $startTime->clone()->addMinutes($this->getDurationInMinutes());

            $datesToCheck[] = [
                'start' => $startTime,
                'end' => $endTime
            ];
        }
        else if ($this->subgroup)
        {
            $course = $this->subgroup->group ? $this->subgroup->group->course : null;
            if ($course)
            {
                $thisType = Calendar::TYPE_COLLECTIVE_COURSE;
                $thisID = $this->course_groups_subgroup2_id;

                $courseDates = $course->toArray()['dates'];
                foreach ($courseDates as $d)
                {
                    $startTime = Carbon::parse($d['date'] . ' ' . $d['hour']);
                    $endTime = Carbon::parse($d['date'] . ' ' . $d['hour_end']);

                    $datesToCheck[] = [
                        'start' => $startTime,
                        'end' => $endTime
                    ];
                }
            }
        }

        // Now, for each of those dates:
        foreach ($datesToCheck as $d)
        {
            // Build calendar
            $startTime = $d['start'];
            $endTime = $d['end'];

            $calendar = new Calendar($startTime, $endTime);
            $calendar->setClient($this->user, false);
            $calendar->setSchool($this->booking->school);
            // TODO TBD this user might have booked at _several_ Schools,
            // but "this" School can't/shouldn't know about it


            // Check overlap: private courses
            $privateData = $calendar->getPrivateCourses()->get();
            if (count($privateData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                {
                    if ($thisType != Calendar::TYPE_PRIVATE_COURSE || $thisID != $box->detail->id)
                    {
                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            return true;
                        }
                    }
                }
            }


            // Check overlap: collective courses
            $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
            if (count($collectiveData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                {
                    if ($thisType != Calendar::TYPE_COLLECTIVE_COURSE || $thisID != $box->detail->id)
                    {
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            return true;
                        }
                    }
                }
            }
        }

        // Nothing seems to overlap, so:
        return false;
    }
}
