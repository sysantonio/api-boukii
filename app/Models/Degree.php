<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Degree",
 *      required={"league","level","name","degree_order","progress","color","sport_id"},
 *      @OA\Property(
 *          property="league",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="level",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="annotation",
 *          description="null for unused at this school",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="color",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
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
 */class Degree extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'degrees';

    public $fillable = [
        'league',
        'level',
        'name',
        'annotation',
        'degree_order',
        'progress',
        'color',
        'school_id',
        'sport_id'
    ];

    protected $casts = [
        'league' => 'string',
        'level' => 'string',
        'name' => 'string',
        'annotation' => 'string',
        'color' => 'string'
    ];

    public static array $rules = [
        'league' => 'required|string|max:255',
        'level' => 'required|string|max:255',
        'name' => 'required|string|max:100',
        'annotation' => 'nullable|string|max:65535',
        'degree_order' => 'required',
        'progress' => 'required',
        'color' => 'required|string|max:10',
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
}
