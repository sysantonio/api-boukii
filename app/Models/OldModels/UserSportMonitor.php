<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Clase para registrar los deportes que el monitor marca desde
 * la app. Solo para tener un registro visual.
 */
class UserSportMonitor extends Model
{
    use SoftDeletes;

    protected $table = 'user_sports_monitor';

    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'sport_id',
        'school_id',
        'degree_id'
    ];

    /**
     * Get an User's full list of Sports, with his Degree on each of them.
     * from the viewpoint of a certain School.
     *
     * @param int $userID
     * @param int $schoolID
     * @return UserSport[]
     */
    public static function listByUserAndSchool($userID, $schoolID)
    {

        return self::join('sports AS S', 'user_sports_monitor.sport_id', '=', 'S.id')
            ->join('degrees_school_sport', function($join) use ($schoolID)
            {
                $join->on('user_sports_monitor.sport_id', '=', 'degrees_school_sport.sport_id');
                $join->on('user_sports_monitor.degree_id', '=', 'degrees_school_sport.degree_id');
                $join->where('degrees_school_sport.school_id', '=', $schoolID);
            })
            ->join('degrees AS D', 'degrees_school_sport.degree_id', '=', 'D.id')
            ->join("sport_types AS ST", "ST.id", "=", "S.sport_type")
            ->where('user_sports_monitor.user_id', '=', $userID)
            ->where('user_sports_monitor.school_id', '=', NULL)
            ->select('user_sports_monitor.sport_id AS id',
                'S.name AS name', 'S.icon_selected AS icon', 'S.sport_type AS sport_type',
                'D.id AS degree_id', 'D.league', 'D.color', "ST.name as type_name")
            ->selectRaw('IFNULL(degrees_school_sport.name, "") AS level, IFNULL(degrees_school_sport.annotation, "") AS annotation')
            ->get();
    }
}
