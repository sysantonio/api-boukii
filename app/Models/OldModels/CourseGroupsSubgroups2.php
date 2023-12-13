<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\Fake\Calendar;

/**
 * Class CourseGroupsSubgroups2
 *
 * @property int $id
 * @property int $course_group2_id
 * @property int|null $monitor_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property CourseGroups2 $group
 * @property User|null $monitor
 * @property Collection|BookingUsers2[] $users
 *
 * @package App\Models
 */
class CourseGroupsSubgroups2 extends Model
{
	use SoftDeletes;
	protected $table = 'course_groups_subgroups2';

	protected $casts = [
		'course_group2_id' => 'int',
		'monitor_id' => 'int',
		'max_participants' => 'int'
	];

	protected $connection = 'old';

protected $fillable = [
		'course_group2_id',
		'monitor_id',
		'max_participants',
        'created_at',
        'updated_at'
	];

    protected $hidden = ['deleted_at'];


    /**
     * Relations
     */

	public function group()
	{
		return $this->belongsTo(CourseGroups2::class, 'course_group2_id');
	}

	public function monitor()
	{
		return $this->belongsTo(User::class, 'monitor_id');
	}

    public function subgroups_dates()
    {
        return $this->hasMany(SubgroupMonitorDate::class, 'subgroup_id');
    }

	public function users()
	{
		return $this->hasMany(BookingUsers2::class, 'course_groups_subgroup2_id');
	}



    /**
     * Get "this" Subgroup position among all its siblings from the same Group
     * i.e. whether it was the first Subgroup created there, or the second, or third...
     *
     * @return int
     */
    public function computePosition()
    {
        return CourseGroupsSubgroups2::where('course_group2_id', '=', $this->course_group2_id)
                    ->where('id', '<', $this->id)
                    ->count() + 1;
    }


    /**
     * Imagine "this" Subgroup gets assigned to a Monitor.
     * But he's already assigned to any other Subgroup within the same Course.
     * Or to a Subgroup from another Course whose dates overlap with this one.
     * Or to any Booking for "loose" Course whose date overlaps with this Subgroup.
     * As of 2022-11-09 UNASSIGN all those duplicates: keep him only at this Subgroup.
     *
     * Compare with App\Models\Course2->checkCourseSubgroupsMonitors()
     * and App\Models\BookingUsers2->avoidMonitorOverlap()
     *
     * TODO TBD what if "this" Subgroup overlaps with a NWD ?
     * Keep the Subgroup assignation and delete the NWD ? Discard the assignation ??
     */
    public function avoidMonitorOverlap()
    {
        if ($this->monitor)
        {
            // Pick root Course info
            $course = $this->group ? $this->group->course : null;
            if ($course)
            {
                // Clean sibling Subgroups
                $groupsID = CourseGroups2::where('course2_id', '=', $course->id)->pluck('id')->toArray();
                CourseGroupsSubgroups2::whereIn('course_group2_id', $groupsID)
                        ->where('id', '<>', $this->id)
                        ->where('monitor_id', '=', $this->monitor_id)
                        ->update(['monitor_id' => null]);


                // Pick root Course's dates
                $course->loadMissing(['course_dates', 'school']);
                $datesList = $course->toArray()['dates'];

                if (count($datesList) > 0)
                {
                    foreach ($datesList as $d)
                    {
                        // Build calendar
                        $startTime = Carbon::parse($d['date'] . ' ' . $d['hour']);
                        $endTime = Carbon::parse($d['date'] . ' ' . $d['hour_end']);

                        $calendar = new Calendar($startTime, $endTime);
                        $calendar->setSchool($course->school);
                        $calendar->setMonitor($this->monitor);


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
                                    BookingUsers2::where('id', '=', $box->detail->id)
                                                ->update(['monitor_id' => null]);
                                }
                            }
                        }


                        // Check overlap: collective courses
                        $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                        if (count($collectiveData) > 0)
                        {
                            foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                            {
                                if ($box->detail->id != $this->id)
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
                }
            }
        }
    }


    /**
     * Imagine "this" Subgroup gets assigned to a Monitor.
     * But he's already assigned to any other Subgroup within the same Course.
     * Or to a Subgroup from another Course whose dates overlap with this one.
     * Or to any Booking for "loose" Course whose date overlaps with this Subgroup.
     * Or it's a NWD.
     * As of 2022-11-16 JUST RETURN a true/false and Controller will decide.
     *
     * Compare with $this->avoidMonitorOverlap()
     *
     * @return boolean
     */
    public function checkMonitorOverlap()
    {
        if ($this->monitor)
        {
            // Pick root Course info
            $course = $this->group ? $this->group->course : null;
            if ($course)
            {
                // Check sibling Subgroups
                $groupsID = CourseGroups2::where('course2_id', '=', $course->id)->pluck('id')->toArray();
                if (CourseGroupsSubgroups2::whereIn('course_group2_id', $groupsID)
                        ->where('id', '<>', $this->id)
                        ->where('monitor_id', '=', $this->monitor_id)
                        ->count() > 0)
                {
                    return true;
                }


                // Pick root Course's dates
                $course->loadMissing(['course_dates', 'school']);
                $datesList = $course->toArray()['dates'];

                if (count($datesList) > 0)
                {
                    foreach ($datesList as $d)
                    {
                        // Build calendar
                        $startTime = Carbon::parse($d['date'] . ' ' . $d['hour']);
                        $endTime = Carbon::parse($d['date'] . ' ' . $d['hour_end']);

                        $calendar = new Calendar($startTime, $endTime);
                        $calendar->setSchool($course->school);
                        $calendar->setMonitor($this->monitor);


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
                                    return true;
                                }
                            }
                        }


                        // Check overlap: collective courses
                        $collectiveData = $calendar->getCollectiveCourses('subgroup')->get();
                        if (count($collectiveData) > 0)
                        {
                            foreach ($calendar->toItems(Calendar::TYPE_COLLECTIVE_COURSE, $collectiveData) as $box)
                            {
                                if ($box->detail->id != $this->id)
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


                        // Check overlap: NWDs
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
                }
            }
        }

        // Nothing seems to overlap, so:
        return false;
    }
}
