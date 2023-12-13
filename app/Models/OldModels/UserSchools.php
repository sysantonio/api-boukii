<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSchools
 *
 * @property int $id
 * @property int $school_id
 * @property int $station_id
 * @property bool $active_school
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property School $school
 * @property Station $station
 *
 * @package App\Models
 */
class UserSchools extends Model
{
    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'school_id',
        'station_id',
        'active_school',
        'status_updated_at'
    ];

    protected $casts = [
		'user_id' => 'int',
        'school_id' => 'int',
        'station_id' => 'int',
        'active_school' => 'bool',
        'status_updated_at' => 'datetime'
	];

    /**
     * Relations
     */

	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function school()
	{
		return $this->belongsTo(School::class);
	}

    public function station()
	{
		return $this->belongsTo(Station::class);
	}


    public static function listSchoolIDByUser($userID, $mustBeActiveForSchool = false)
    {
        $query = self::where('user_id', '=', $userID);

        if ($mustBeActiveForSchool)
        {
            $query->where('active_school', '=', 1);
        }

        return self::where('user_id', '=', $userID)->pluck('school_id')->toArray();
    }


    /**
     * If current user has Admin role, pick his School.
     *
     * N.B: as of 2022-10, we assume that Admins are always linked to
     * ONE School only (NOT several),
     * but there might be users with ZERO schools.
     *
     * And as of 2022-12, the School can deactivate him
     * ("active_school" at "user_schools" table -- apart from global "active" at "users" table)
     * so he shouldn't access any data from the School
     *
     * @return School|null
     */
    public static function getAdminSchool()
    {
        $myUser = \Auth::user();
        $myUS = ($myUser && ($myUser->user_type == UserType::ID_ADMINISTRATOR || $myUser->user_type == UserType::ID_VISUALIZER) )
                        ? UserSchools::where('user_id', '=', $myUser->id)->where('active_school', '=', 1)->first()
                        : null;

        return $myUS ? School::find($myUS->school_id) : null;
    }

    public static function getAdminSchoolById($id)
    {
        return $id ? School::find($id) : null;
    }


    /**
     * Check if a certain User with Admin role is linked to a certain School.
     * Optionally, check also whether he's active for that School
     * (i.e. "users_school.active" value, NOT global "users.active" switch)
     *
     * @param int $adminUserID
     * @param int $schoolID
     * @param bool $mustBeActiveForSchool
     * @return boolean
     */
    public static function checkAdminSchool($adminUserID, $schoolID, $mustBeActiveForSchool = false)
    {
        $query = User::select('users.id')
                ->join('user_schools AS US', 'users.id', '=', 'US.user_id')
                ->where('US.school_id', '=', $schoolID)
                ->where('users.id', '=', $adminUserID)
                ->where('users.user_type', '=', UserType::ID_ADMINISTRATOR);

        if ($mustBeActiveForSchool)
        {
            $query->where('US.active_school', '=', 1);
        }

        return $query->exists();
    }


    /**
     * Check if a certain User with Monitor role is linked to a certain School.
     * Optionally, check also whether he's active for that School
     * (i.e. "users_school.active" value, NOT global "users.active" switch)
     *
     * @param int $monitorUserID
     * @param int $schoolID
     * @param bool $mustBeActiveForSchool
     * @return boolean
     */
    public static function checkMonitorSchool($monitorUserID, $schoolID, $mustBeActiveForSchool = false)
    {
        $query = User::select('users.id')
                ->join('user_schools AS US', 'users.id', '=', 'US.user_id')
                ->where('US.school_id', '=', $schoolID)
                ->where('users.id', '=', $monitorUserID)
                ->where('users.user_type', '=', UserType::ID_MONITOR);

        if ($mustBeActiveForSchool)
        {
            $query->where('US.active_school', '=', 1);
        }

        return $query->exists();
    }


    /**
     * Check if a certain User with Client role is linked to a certain School.
     * Optionally, check also whether he's active for that School
     * (i.e. "users_school.active" value, NOT global "users.active" switch)
     *
     * @param int $clientUserID
     * @param int $schoolID
     * @param bool $mustBeActiveForSchool
     * @return boolean
     */
    public static function checkClientSchool($clientUserID, $schoolID, $mustBeActiveForSchool = false)
    {
        $query = User::select('users.id')
                ->join('user_schools AS US', 'users.id', '=', 'US.user_id')
                ->where('US.school_id', '=', $schoolID)
                ->where('users.id', '=', $clientUserID)
                ->where('users.user_type', '=', UserType::ID_CLIENT);

        if ($mustBeActiveForSchool)
        {
            $query->where('US.active_school', '=', 1);
        }

        return $query->exists();
    }


    /**
     * Get the current School for a certain user; as of 2022-12:
     *  - Admins can be linked to ONE School only -> that one
     *      @see \Admin\UserController->store()
     *  - Monitors can be linked to ONE active School only -> that one
     *      @see \Admin\UserController->store() + \Teach\SchoolController->storeMine()
     *      [but this will change 2023-?? to several Schools with a 2-side acceptance TODO TBD]
     *  - Clients might be linked to any number of Schools -> take last one
     *      @see \Admin\UserController->store() + \Auth\LoginController->store() when coming thru a iframe
     *      [or maybe by bookings of courses future/present/past TODO TBD ??? ]
     *
     * Note all of them might be linked to ZERO Schools, thus no current one.
     *
     * @param int $userID
     * @return School|null
     */
    public static function getCurrentSchool($userID)
    {
        $school = null;

        $userData = User::find($userID);
        if ($userData)
        {
            $us = null;
            switch ($userData->user_type)
            {
                case UserType::ID_ADMINISTRATOR:
                case UserType::ID_VISUALIZER:
                {
                    $us = UserSchools::where('user_id', '=', $userData->id)->first();
                    break;
                }
                case UserType::ID_MONITOR:
                {
                    $us = UserSchools::where('user_id', '=', $userData->id)->where('active_school', '=', 1)->first();
                    break;
                }
                case UserType::ID_CLIENT:
                {
                    $us = UserSchools::where('user_id', '=', $userData->id)->orderBy('id', 'desc')->first();
                    break;
                }
            }

            if ($us)
            {
                $school = School::find($us->school_id);
            }
        }

        return $school;
    }
}
