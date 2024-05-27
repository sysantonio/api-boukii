<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Degree",
 *      required={"league","level","name","degree_order","progress","color","sport_id"},
 *       @OA\Property(
 *           property="league",
 *           description="League of the degree",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="level",
 *           description="Level of the degree",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="name",
 *           description="Name of the degree",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *            property="image",
 *            description="Image of the degree",
 *            type="string",
 *            nullable=false
 *        ),
 *       @OA\Property(
 *           property="annotation",
 *           description="Additional annotation, null for unused at this school",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="degree_order",
 *           description="Order of the degree",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="progress",
 *           description="Progress level of the degree",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="color",
 *           description="Color associated with the degree",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *            property="age_min",
 *            description="Minimum age for participants",
 *            type="integer",
 *            nullable=true
 *        ),
 *        @OA\Property(
 *            property="age_max",
 *            description="Maximum age for participants",
 *            type="integer",
 *            nullable=true
 *        ),
 *        @OA\Property(
 *            property="active",
 *            description="Is active",
 *            type="boolean",
 *            nullable=false
 *        ),
 *       @OA\Property(
 *           property="school_id",
 *           description="ID of the school associated with the degree",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="sport_id",
 *           description="ID of the sport associated with the degree",
 *           type="integer",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class Degree extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'degrees';
    use \Awobaz\Compoships\Compoships;
    public $fillable = [
        'league',
        'level',
        'image',
        'name',
        'annotation',
        'degree_order',
        'progress',
        'color',
        'age_min',
        'age_max',
        'active',
        'school_id',
        'sport_id'
    ];

    protected $casts = [
        'league' => 'string',
        'level' => 'string',
        'image' => 'string',
        'name' => 'string',
        'annotation' => 'string',
        'color' => 'string',
        'active' => 'boolean'
    ];

    public static array $rules = [
        'league' => 'required|string|max:255',
        'level' => 'required|string|max:255',
        'image' => 'nullable',
        'name' => 'required|string|max:100',
        'annotation' => 'nullable|string|max:65535',
        'degree_order' => 'required',
        'progress' => 'required',
        'color' => 'required|string|max:10',
        'age_min' => 'nullable',
        'age_max' => 'nullable',
        'active' => 'required|boolean',
        'school_id' => 'nullable',
        'sport_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Sport::class, 'sport_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'degree_id');
    }

    public function courseGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'teacher_min_degree');
    }

    public function courseGroup1s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'degree_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'degree_id');
    }

    public function degreesSchoolSportGoals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\DegreesSchoolSportGoal::class, 'degree_id');
    }

    public function evaluations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Evaluation::class, 'degree_id');
    }

    public function monitorSportAuthorizedDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorSportAuthorizedDegree::class, 'degree_id');
    }

    public function monitorSportsDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorSportsDegree::class, 'degree_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
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
                $d2 = Degree::firstOrCreate([
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
}
