<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="CourseGroup",
 *      required={"course_id","course_date_id","degree_id","teachers_min","teachers_max","auto"},
 *       @OA\Property(
 *           property="course_id",
 *           description="ID of the course",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="course_date_id",
 *           description="ID of the course date",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="degree_id",
 *           description="ID of the degree",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="age_min",
 *           description="Minimum age for participants",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="age_max",
 *           description="Maximum age for participants",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="recommended_age",
 *           description="Recommended age for participants",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="teachers_min",
 *           description="Minimum number of teachers",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="teachers_max",
 *           description="Maximum number of teachers",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="teacher_min_degree",
 *           description="Minimum degree required for teachers",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="observations",
 *           description="Observations about the course group",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="auto",
 *           description="Indicates whether group assignment is automatic",
 *           type="boolean",
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
class CourseGroup extends Model
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
