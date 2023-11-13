<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="CourseGroup",
 *      required={"course_id","course_date_id","degree_id","teachers_min","teachers_max","auto"},
 *      @OA\Property(
 *          property="observations",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="auto",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
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
 */class CourseGroup extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'course_groups';

    public $fillable = [
        'course_id',
        'course_date_id',
        'degree_id',
        'age_min',
        'age_max',
        'recommended_age',
        'teachers_min',
        'teachers_max',
        'observations',
        'teacher_min_degree',
        'auto'
    ];

    protected $casts = [
        'observations' => 'string',
        'auto' => 'boolean'
    ];

    public static array $rules = [
        'course_id' => 'required',
        'course_date_id' => 'required',
        'degree_id' => 'required',
        'age_min' => 'nullable',
        'age_max' => 'nullable',
        'recommended_age' => 'nullable',
        'teachers_min' => 'required',
        'teachers_max' => 'required',
        'observations' => 'nullable|string|max:65535',
        'teacher_min_degree' => 'nullable',
        'auto' => 'required|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function teacherMinDegree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'teacher_min_degree');
    }

    public function courseDate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseDate::class, 'course_date_id');
    }

    public function degree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'degree_id');
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_group_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'course_group_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
