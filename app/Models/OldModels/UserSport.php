<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class UserSport
 *
 * @property int $id
 * @property int $user_id
 * @property int $sport_id
 * @property int|null $school_id
 * @property int $degree_id
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property Sport $sport
 * @property School $school
 * @property Degree $degree
 *
 * @package App\Models
 */
class UserSport extends Model
{
    use SoftDeletes;

    protected $table = 'user_sports';

    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'sport_id',
        'school_id',
        'degree_id',
        'salary_level',
        'allow_adults',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'bool'
    ];


    /**
     * Relations
     */

	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function sport()
	{
		return $this->belongsTo(Sport::class);
	}

    public function school()
	{
		return $this->belongsTo(School::class);
	}

    public function degree()
	{
		return $this->belongsTo(Degree::class);
	}


    /**
     * Get an User's full list of Sports, with his Degree on each of them,
     * from the viewpoint of a certain School.
     *
     * @param int $userID
     * @param int $schoolID
     * @return UserSport[]
     */
    public static function listByUserAndSchool($userID, $schoolID)
    {
        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
                    ->join('degrees_school_sport', function($join) use ($schoolID)
                    {
                        $join->on('user_sports.sport_id', '=', 'degrees_school_sport.sport_id');
                        $join->on('user_sports.degree_id', '=', 'degrees_school_sport.degree_id');
                        $join->where('degrees_school_sport.school_id', '=', $schoolID);
                    })
                    ->join('degrees AS D', 'degrees_school_sport.degree_id', '=', 'D.id')
                    ->where('user_sports.user_id', '=', $userID)
                    ->where('user_sports.school_id', '=', $schoolID)
                    ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                            'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                            'D.id AS degree_id', 'D.league')
                    ->selectRaw('IFNULL(degrees_school_sport.name, "") AS level, IFNULL(degrees_school_sport.annotation, "") AS annotation')
                    ->get();
    }

    /**
     * Get a User's full list of Sports, grouped by higher degree.
     *
     * @param $userID
     * @param $schoolID
     * @return mixed
     */
    public static function listByUser($userID, $schoolID)
    {
        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
            ->join('degrees_school_sport', function($join) use ($schoolID)
            {
                $join->on('user_sports.sport_id', '=', 'degrees_school_sport.sport_id');
                $join->on('user_sports.degree_id', '=', 'degrees_school_sport.degree_id');
                $join->where('degrees_school_sport.school_id', '=', $schoolID);
            })
            ->join('degrees AS D', 'degrees_school_sport.degree_id', '=', 'D.id')
            ->where('user_sports.user_id', '=', $userID)
            //->where('user_sports.school_id', '=', $schoolID)
            ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                'D.id AS degree_id', 'D.league')
            ->selectRaw('IFNULL(degrees_school_sport.name, "") AS level, IFNULL(degrees_school_sport.annotation, "") AS annotation')
            ->get();
    }

    /**
     * Get an User's full list of Sports, with his current and next Degree on each of them,
     * from the viewpoint of a certain School.
     *
     * @param int $userID
     * @param int $schoolID
     * @return UserSport[]
     */
    public static function listWithNextDegreeByUserAndSchool($userID, $schoolID)
    {
        $school_sports = SchoolSports::where("school_id",$schoolID)->pluck('sport_id')->toArray();

        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
                    ->whereIn('S.id', $school_sports)
                    ->join('degrees_school_sport', function($join) use ($schoolID)
                    {
                        $join->on('user_sports.sport_id', '=', 'degrees_school_sport.sport_id');
                        $join->on('user_sports.degree_id', '=', 'degrees_school_sport.degree_id');
                        $join->where('degrees_school_sport.school_id', '=', $schoolID);
                    })
                    ->join('degrees AS D', 'degrees_school_sport.degree_id', '=', 'D.id')
                    ->where('user_sports.user_id', '=', $userID)
                    ->where('user_sports.school_id', '=', $schoolID)
                    ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                            'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                            'D.id AS degree_id', 'D.league')
                    ->selectRaw('IFNULL(degrees_school_sport.name, "") AS level, IFNULL(degrees_school_sport.annotation, "") AS annotation')

                    ->get();
    }

    /**
     * Get an User's full list of Sports, with the lowest degree.
     *
     * TODO: Optimizar consulta.
     */
    public static function listByUserWithoutSchool($userID)
    {
        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
                    ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                            'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                            DB::raw('(select league from degrees where id = 1) as league'),
                            DB::raw('(select id from degrees where id = 1) as degree_id'),
                            DB::raw('(select level from degrees where id = 1) as level'),
                            DB::raw('(select progress from degrees where id = 1) as progress')
                            )
                    ->where('user_sports.user_id', '=', $userID)
                    ->orderBy('is_default', 'desc')
                    ->get();
    }

    /**
     * @deprecated
     *   --> $this->listByUserAndSchool()
     *
     * Get an User's full list of Sports, with his Degree on each of them.
     *
     * TODO
     * TBD
     * What about the different Degrees lists per School (table degrees_school_type) ??
     * Because this user MIGHT BE LINKED TO SEVERAL SCHOOLS OR NONE !!!
     *
     * @param int $userID
     * @return UserSport[]
     */
    public static function __deprecated_listByUser($userID)
    {
        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
                    ->join('degrees AS D', 'user_sports.degree_id', '=', 'D.id')
                    ->where('user_id', '=', $userID)
                    ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                            'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                            'D.id AS degree_id', 'D.league', 'D.level')
                    ->get();
    }


    /**
     * Get an User's default Sport, with his Degree on it.
     *
     * @param int $userID
     * @return UserSport|null
     */
    public static function getDefaultByUser($userID, $schoolID = null)
    {
        return self::join('sports AS S', 'user_sports.sport_id', '=', 'S.id')
                ->join('degrees_school_sport AS DSS', function($join) use ($schoolID)
                {
                    $join->on('user_sports.sport_id', '=', 'DSS.sport_id');
                    $join->on('user_sports.degree_id', '=', 'DSS.degree_id');
                    $schoolID ? $join->where('DSS.school_id', $schoolID) : $join->whereNull('DSS.school_id');

                })
                ->join('degrees AS D', 'DSS.degree_id', '=', 'D.id')
                ->where('user_sports.user_id', '=', $userID)
                ->where('user_sports.school_id', '=', $schoolID)
                ->select('user_sports.sport_id AS id', 'user_sports.is_default',
                        'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                        'D.id AS degree_id', 'D.league')
                ->selectRaw('IFNULL(DSS.name, "") AS level, IFNULL(DSS.annotation, "") AS annotation')
                ->orderBy('is_default', 'desc')
                ->first();
    }


    /**
     * Get an User's Degree at a certain Sport - just the ID (no name etc)
     * 2022-11-30 --> must be fully changed to different degree+objectives history PER SCHOOL
     *
     * @param int $userID
     * @param int $sportID
     * @param int $schoolID
     * @return int -1 if that User still hasn't that Sport
     */
    public static function getCurrentDegreeIDByUserAndSportAndSchool($userID, $sportID, $schoolID)
    {
        $us = self::where('user_id', '=', $userID)
                    ->where('sport_id', '=', $sportID)
                    ->where('school_id', '=', $schoolID)
                    ->first();

        return $us ? $us->degree_id : -1;
    }


    /**
     * Get an User's Degree at a certain Sport for a certain SchoolType - whole details (including name etc)
     * 2022-11-30 --> must be fully changed to different degree+objectives history PER SCHOOL
     *
     * @param int $userID
     * @param int $sportID
     * @param int $schoolID
     * @return Degree|null
     */
    public static function getCurrentDegreeByUserAndSportAndSchool($userID, $sportID, $schoolID)
    {
        $school = School::find($schoolID);

        if (!$school)
        {
            return null;
        }
        else
        {
            $degreeID = self::getCurrentDegreeIDByUserAndSportAndSchool($userID, $sportID, $schoolID);
            if ($degreeID < 1)
            {
                return null;
            }
            else
            {
                return Degree::join('degrees_school_sport AS DSS', 'degrees.id', '=', 'DSS.degree_id')
                        ->selectRaw('degrees.id, degrees.color, degrees.progress, degrees.league, DSS.annotation, DSS.name')
                        ->where('DSS.school_id', '=', $schoolID)
                        ->where('DSS.sport_id', '=', $sportID)
                        ->where('DSS.degree_id', '=', $degreeID)
                        ->first();
            }
        }
    }


    /**
     * Imagine a new User, who has NO Degree set for a certain Sport.
     * Now he books into a Subgroup from a Collective Course: the Course is meant
     * for a certain Sport, and the Subgroup belongs to a Group with a certain Degree.
     * Set that User to that Degree.
     * N.B: anyone can book into any Degree, either below of over his current one,
     * so this only serves to set a FAKE value instead of NO value.
     *
     * 2022-11-30 --> must be fully changed to different degree+objectives history PER SCHOOL
     */
    public static function setCurrentDegreeIDByUserAndSubgroup($userID, $subgroupID)
    {
        // Get related Sport
        $subgroupData = CourseGroupsSubgroups2::find($subgroupID);
        $groupData = $subgroupData ? $subgroupData->group : null;
        $courseData = $groupData ? $groupData->course : null;

        if ($courseData)
        {
            // Check desired User has NO Degree there
            if (self::getCurrentDegreeIDByUserAndSportAndSchool($userID, $courseData->sport_id, $courseData->school_id) == -1)
            {
                // Set him Group's Degree
                self::create([
                    'user_id' => $userID,
                    'sport_id' => $courseData->sport_id,
                    'school_id' => $courseData->school_id,
                    'degree_id' => $groupData->degree_id
                ]);
            }
        }
    }

    /**
     * Añade el degree con ID 1, si no tiene nivel en el deporte.
     *
     * @param $userID
     * @param $sportID
     * @param $schoolID
     * @return void
     */
    public static function setFirstDegreeIDByUserAndSportAndSchool($userID, $sportID, $schoolID)
    {
        if (self::getCurrentDegreeIDByUserAndSportAndSchool($userID, $sportID, $schoolID) == -1)
        {
            self::create([
                'user_id' => $userID,
                'sport_id' => $sportID,
                'school_id' => $schoolID,
                'degree_id' => 1
            ]);
        }
    }


    /**
     * Until 2022-12 "user_sports" table held just one value per User+Sport
     * i.e. "I have, for Ski, degree 7"
     * Then it was splitted to one value per School
     * i.e. "I have, for Ski, degree 7 at SchoolA but 6 at SchoolB"
     *
     * Copy those old values to each of User's Schools.
     */
    public static function cloneOldValuesToSchools()
    {
        foreach (UserSport::select('user_id')->distinct()->pluck('user_id')->toArray() as $userID)
        {
            $hisSchools = UserSchools::where('user_id', $userID)->pluck('school_id')->toArray();

            foreach (UserSport::whereNull('school_id')->where('user_id', $userID)->get() as $oldValue)
            {
                foreach ($hisSchools as $schoolID)
                {
                    UserSport::firstOrCreate([
                        'user_id' => $userID,
                        'school_id' => $schoolID,
                        'sport_id' => $oldValue->sport_id
                    ], [
                        'degree_id' => $oldValue->degree_id
                    ]);
                }
            }
        }
    }
}
