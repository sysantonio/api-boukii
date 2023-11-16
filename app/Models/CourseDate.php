<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="CourseDate",
 *      required={"course_id", "date", "hour_start", "hour_end"},
 *      @OA\Property(
 *          property="course_id",
 *          description="ID of the course",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="date",
 *          description="Date of the course session",
 *          type="string",
 *          format="date",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="hour_start",
 *          description="Start hour of the course session",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="hour_end",
 *          description="End hour of the course session",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Update timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class CourseDate extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'course_dates';

    public $fillable = [
        'course_id',
        'date',
        'hour_start',
        'hour_end'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public static array $rules = [
        'course_id' => 'required',
        'date' => 'required',
        'hour_start' => 'required',
        'hour_end' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_date_id');
    }

    public function courseGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'course_date_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'course_date_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
