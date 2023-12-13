<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DegreeSchoolSport
 *
 * @property int $id
 * @property int|null $school_id
 * @property int $sport_id
 * @property int $degree_id
 * @property string|null $annotation
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Degree $degree
 * @property School|null $school
 * @property Sport $sport
 * @property Collection|DegreeSchoolSportGoals[] $goals
 *
 * @package App\Models
 */
class DegreeSchoolSport extends Model
{
	use SoftDeletes;
	protected $table = 'degrees_school_sport';

	protected $casts = [
		'school_id' => 'int',
		'sport_id' => 'int',
		'degree_id' => 'int'
	];

	protected $connection = 'old';

protected $fillable = [
		'school_id',
		'sport_id',
		'degree_id',
        'annotation',
		'name'
	];


    /**
     * Relations
     */

	public function degree()
	{
		return $this->belongsTo(Degree::class);
	}

	public function school()
	{
		return $this->belongsTo(School::class);
	}

	public function sport()
	{
		return $this->belongsTo(Sport::class);
	}

	public function goals()
	{
		return $this->hasMany(DegreeSchoolSportGoals::class, 'degrees_school_sport_id', 'id');
	}


    /**
     * Search methods
     */


    /**
     * List of default degrees for a certain sport.
     */
    public static function listDefaultBySport($sportID, $includeGoals = false)
    {
        $relations = ['degree'];
        if ($includeGoals)
        {
            $relations[] = 'goals';
        }

        return self::with($relations)
                    ->whereNull('school_id')
                    ->where('sport_id', '=', $sportID)
                    ->orderBy('degree_id', 'asc')
                    ->get();
    }


    /**
     * List of a School's Degrees for a certain sport.
     */
    public static function listBySchoolAndSport($schoolID, $sportID, $includeGoals = false)
    {
        $relations = ['degree'];
        if ($includeGoals)
        {
            $relations[] = 'goals';
        }

        $list = self::with($relations)
                    ->where('school_id', '=', $schoolID)
                    ->where('sport_id', '=', $sportID)
                    ->orderBy('degree_id', 'asc')
                    ->get();

        // If that School still hasn't defined his Degrees, clone from Default list and retry
        // 2022-12-19: clone with EMPTY names and NO goals
        if (count($list) > 0)
        {
            return $list;
        }
        else
        {
            $defaultList = self::listDefaultBySport($sportID, true);
            foreach ($defaultList as $d)
            {
                $d2 = DegreeSchoolSport::firstOrCreate([
                        'degree_id' => $d->degree_id,
                        'school_id' => $schoolID,
                        'sport_id' => $d->sport_id
                    ], [
                        // 'name' => $d->name
                        'name' => null
                    ]);

                /*
                if ($d2->wasRecentlyCreated)
                {
                    foreach ($d->goals as $g)
                    {
                        DegreeSchoolSportGoals::create([
                            'degrees_school_sport_id' => $d2->id,
                            'name' => $g->name
                        ]);
                    }
                }
                */
            }

            return self::listBySchoolAndSport($schoolID, $sportID, $includeGoals);
        }
    }


    /**
     * Get a School's Degree for a certain sport.
     */
    public static function findBySchoolAndSportAndDegree($schoolID, $sportID, $degreeID)
    {
        return self::with('degree')
                    ->where('school_id', '=', $schoolID)
                    ->where('sport_id', '=', $sportID)
                    ->where('degree_id', '=', $degreeID)
                    ->first();
    }
}
