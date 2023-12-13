<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Degree
 *
 * @property int $id
 * @property string $name
 * @property string $league
 * @property string $level
 * @property int $degree_order
 * @property int $progress
 * @property string $color
 *
 * @package App\Models
 */
class Degree extends Model
{
    protected $table = 'degrees';

    protected $connection = 'old';
    public $timestamps = false;

    /**
     * @deprecated
     *   --> DegreeSchoolSport->listBySchoolAndSport()
     *      Because until 2022-11 all Sports had the same Degrees, but not anymore
     *
     * Given a School from a certain type, get its full list of Degrees.
     *
     * @param int $schoolID
     * @return Collection|Degree[]
     */
    public static function __deprecated_listFromSchool($schoolID)
    {
        $school = School::find($schoolID);

        if (!$school)
        {
            return new Collection;
        }
        else
        {
            return Degree::join('degrees_school_type AS DST', 'degrees.id', '=', 'DST.degree_id')
                        ->selectRaw('degrees.id, degrees.color, degrees.progress, DST.league, DST.level, DST.name')
                        ->where('DST.school_type_id', '=', $school->type_id)
                        ->orderBy('degrees.id', 'asc')
                        ->get();
        }
    }
}
