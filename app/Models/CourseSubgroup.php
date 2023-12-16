<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="CourseSubgroup",
 *      required={"course_id","course_date_id","degree_id","course_group_id"},
 *      @OA\Property(
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
 *           property="course_group_id",
 *           description="ID of the course group",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="monitor_id",
 *           description="ID of the assigned monitor",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="max_participants",
 *           description="Maximum number of participants",
 *           type="integer",
 *           nullable=true
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
class CourseSubgroup extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'course_subgroups';

    public $fillable = [
        'course_id',
        'course_date_id',
        'degree_id',
        'course_group_id',
        'monitor_id',
        'old_id',
        'max_participants'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'course_id' => 'required',
        'course_date_id' => 'required',
        'degree_id' => 'required',
        'course_group_id' => 'required',
        'monitor_id' => 'nullable',
        'max_participants' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function degree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'degree_id');
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function courseGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseGroup::class, 'course_group_id');
    }

    public function courseDate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseDate::class, 'course_date_id');
    }

    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class, 'monitor_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_subgroup_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
