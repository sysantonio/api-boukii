<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\Fake\Calendar;

class UserNwd extends Model
{

    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_nwd';

    protected $casts = [
        'user_id' => 'integer',
        'school_id' => 'integer',
        'station_id' => 'integer',
        'user_nwd_subtype_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'full_day' => 'bool',
        'description' => 'string',
        'color' => 'string',
        'created_at' => 'datetime',
    ];

    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'school_id',
        'station_id',
        'user_nwd_subtype_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'full_day',
        'color',
        'description',
    ];

    /**
     * Relations
     */

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function school()
	{
		return $this->belongsTo(School::class, 'school_id');
	}

    public function station()
	{
		return $this->belongsTo(Station::class, 'station_id');
	}




    /**
     * Imagine "this" NWD is created for a Monitor (or edited to new dates).
     * But he already has another NWD whose dates overlap with this one.
     * Or he's assigned to any Course whose dates overlap with this NWD.
     * As of 2022-11-17 JUST RETURN what happens and Controller will decide.
     *
     * @return string explaing the overlap (empty-string if no overlap)
     */
    public function checkMonitorOverlap($filterBySchool = null)
    {
        // Set current user's language for message
        $defaultLocale = config('app.fallback_locale');
        $myUser = \Auth::user();
        $userLang = Language::find( $myUser->language1_id );
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        // For each of "this" NWD date & times
        $period = CarbonPeriod::create($this->start_date, $this->end_date);
        foreach ($period as $date)
        {
            $startTime = $date->copy()->setTimeFrom($this->full_day ? School::getOpeningTimes()->start : $this->start_time);
            $endTime = $date->copy()->setTimeFrom($this->full_day ? School::getOpeningTimes()->end : $this->end_time);

            // Build calendar
            $calendar = new Calendar($startTime, $endTime);
            $calendar->setMonitor($this->user);
            if ($filterBySchool)
            {
                $calendar->setSchool($filterBySchool);
            }
            else if ($this->school)
            {
                $calendar->setSchool($this->school);
            }


            // Check overlap: other NWDs
            $nwdData = $calendar->getMonitorNwd();
            if (count($nwdData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_NWD, $nwdData) as $box)
                {
                    if ($box->detail->id != $this->id)
                    {
                        // Adjust seconds to allow start from other end
                        $startTime2 = Carbon::parse($box->from)->addSecond();
                        $endTime2 = Carbon::parse($box->to)->subSecond();

                        if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                            $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                        {
                            return __('nwds.nwdOverlap');
                        }
                    }
                }
            }


            // Check overlap: private courses
            $privateData = $calendar->getPrivateCourses()->get();
            if (count($privateData) > 0)
            {
                foreach ($calendar->toItems(Calendar::TYPE_PRIVATE_COURSE, $privateData) as $box)
                {
                    $startTime2 = Carbon::parse($box->from)->addSecond();
                    $endTime2 = Carbon::parse($box->to)->subSecond();

                    if ($startTime2->between($startTime, $endTime) || $endTime2->between($startTime, $endTime) ||
                        $startTime->between($startTime2, $endTime2) || $endTime->between($startTime2, $endTime2))
                    {
                        return __('nwds.courseOverlap');
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
                        return __('nwds.courseOverlap');
                    }
                }
            }
        }


        // Nothing seems to overlap, so:
        return '';
    }


    /**
     * Imagine that a Monitor had created a certain NON Working Day
     * ex. "NOT 05-dec to 11-dec from 09:00 to 14:00"
     * Then he WILL work on a part of it
     * ex. "YES 09-dec from 10:00 to 12:00"
     * This function "drills a hole" into initial NWD so it ends being several
     * different lines leaving desired gap
     * ex. "NOT 05-dec to 08-dec from 09:00 to 14:00"
     *   + "NOT 09-dec to 09-dec from 09:00 to 10:00"
     *   + "NOT 09-dec to 09-dec from 12:00 to 14:00"
     *   + "NOT 10-dec to 11-dec from 09:00 to 14:00"
     */
    public static function drillYwd($monitorUser, $drillDate, $drillStartTime, $drillEndTime)
    {
        // 1. Check if there's a NWD to drill
        $drillDay = Carbon::parse($drillDate);
        $drillStart = $drillDay->clone()->setTimeFrom($drillStartTime);
        $drillEnd = $drillDay->clone()->setTimeFrom($drillEndTime);
        $calendar = new Calendar($drillStart, $drillEnd);
        $calendar->setMonitor($monitorUser);

        $nwdData = $calendar->getMonitorNwd();
        if (count($nwdData) > 0)
        {
            $originalNWD = $nwdData[0];
            unset($originalNWD->date);

            // a. If original NWD spans over several days,
            // split into three: before-drill + drill-day + after-drill
            if ($originalNWD->start_date != $originalNWD->end_date)
            {
                if ($originalNWD->start_date->lessThan($drillDay))
                {
                    self::create([
                        'user_id' => $originalNWD->user_id,
                        'school_id' => $originalNWD->school_id,
                        'station_id' => $originalNWD->station_id,
                        'user_nwd_subtype_id' => $originalNWD->user_nwd_subtype_id,
                        'start_date' => $originalNWD->start_date,
                        'end_date' => $drillDay->clone()->subDay()->toDateString(),
                        'start_time' => $originalNWD->start_time,
                        'end_time' => $originalNWD->end_time,
                        'full_day' => $originalNWD->full_day,
                        'description' => $originalNWD->description,
                        'color' => $originalNWD->color
                    ]);
                }

                if ($originalNWD->end_date->greaterThan($drillDay))
                {
                    self::create([
                        'user_id' => $originalNWD->user_id,
                        'school_id' => $originalNWD->school_id,
                        'station_id' => $originalNWD->station_id,
                        'user_nwd_subtype_id' => $originalNWD->user_nwd_subtype_id,
                        'start_date' => $drillDay->clone()->addDay()->toDateString(),
                        'end_date' => $originalNWD->end_date,
                        'start_time' => $originalNWD->start_time,
                        'end_time' => $originalNWD->end_time,
                        'full_day' => $originalNWD->full_day,
                        'description' => $originalNWD->description,
                        'color' => $originalNWD->color
                    ]);
                }

                $originalNWD->start_date = $drillDay->toDateString();
                $originalNWD->end_date = $drillDay->toDateString();
                $originalNWD->save();

                // And call this function again - which now only should apply over that last drill-day portion
                self::drillYwd($monitorUser, $drillDate, $drillStartTime, $drillEndTime);
            }

            // b. If original NWD only spans over one day
            else
            {
                $originalStartTime = $originalNWD->full_day
                                        ? School::getOpeningTimes()->start->format('H:i:s')
                                        : $originalNWD->start_time->format('H:i:s');
                $originalEndTime = $originalNWD->full_day
                                        ? School::getOpeningTimes()->end->format('H:i:s')
                                        : $originalNWD->end_time->format('H:i:s');
                $originalStart = $originalNWD->start_date->setTimeFrom($originalStartTime);
                $originalEnd = $originalNWD->start_date->setTimeFrom($originalEndTime);

                // b.1. Drill is bigger than original -> delete original
                if ($drillStart->lessThanOrEqualTo($originalStart) && $drillEnd->greaterThanOrEqualTo($originalEnd))
                {
                    $originalNWD->delete();
                }
                // b.2. Drill is at the beginning of original -> cut original start to drill end
                else if ($drillStart->lessThanOrEqualTo($originalStart) && $drillEnd->greaterThan($originalStart))
                {
                    $originalNWD->start_time = $drillEndTime;
                    $originalNWD->end_time = $originalEndTime;
                    $originalNWD->full_day = false;
                    $originalNWD->save();
                }
                // b.3. Drill is at the end of original -> cut original end to drill start
                else if ($drillEnd->greaterThanOrEqualTo($originalEnd) && $drillStart->lessThan($originalEnd))
                {
                    $originalNWD->start_time = $originalStartTime;
                    $originalNWD->end_time = $drillStartTime;
                    $originalNWD->full_day = false;
                    $originalNWD->save();
                }
                // b.4. Drill is at the middle of original -> cut original end to drill start,
                // and create another NWD from drill end to original end
                else if ($drillStart->greaterThan($originalStart) && $drillEnd->lessThan($originalEnd))
                {
                    $originalNWD->start_time = $originalStartTime;
                    $originalNWD->end_time = $drillStartTime;
                    $originalNWD->full_day = false;
                    $originalNWD->save();

                    self::create([
                        'user_id' => $originalNWD->user_id,
                        'school_id' => $originalNWD->school_id,
                        'station_id' => $originalNWD->station_id,
                        'user_nwd_subtype_id' => $originalNWD->user_nwd_subtype_id,
                        'start_date' => $drillDay->toDateString(),
                        'end_date' => $drillDay->toDateString(),
                        'start_time' => $drillEndTime,
                        'end_time' => $originalEndTime,
                        'full_day' => false,
                        'description' => $originalNWD->description,
                        'color' => $originalNWD->color
                    ]);
                }
            }
        }
    }
}
