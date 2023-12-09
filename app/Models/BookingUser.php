<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="BookingUser",
 *      required={"school_id","booking_id","client_id","price","currency","course_date_id","attended"},
 *           @OA\Property(
 *           property="school_id",
 *           description="School ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="booking_id",
 *           description="Booking ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="client_id",
 *           description="Client ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="course_subgroup_id",
 *           description="Course Subgroup ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="course_id",
 *           description="Course ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="course_date_id",
 *           description="Course Date ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="degree_id",
 *           description="Degree ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="course_group_id",
 *           description="Course Group ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="monitor_id",
 *           description="Monitor ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="hour_start",
 *           description="Start Hour",
 *           type="string",
 *           nullable=true,
 *           format="time"
 *       ),
 *       @OA\Property(
 *           property="hour_end",
 *           description="End Hour",
 *           type="string",
 *           nullable=true,
 *           format="time"
 *       ),
 *      @OA\Property(
 *          property="price",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="date",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="attended",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="color",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *            property="status",
 *            description="Status of the booking user",
 *            type="integer",
 *            example=1
 *        ),
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
class BookingUser extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'booking_users';

    public $fillable = [
        'school_id',
        'booking_id',
        'client_id',
        'price',
        'currency',
        'course_subgroup_id',
        'course_id',
        'course_date_id',
        'degree_id',
        'course_group_id',
        'monitor_id',
        'date',
        'hour_start',
        'hour_end',
        'attended',
        'status',
        'color'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'currency' => 'string',
        'date' => 'date',
        'attended' => 'boolean',
        'status' => 'integer',
        'color' => 'string'
    ];

    public static array $rules = [
        'school_id' => 'required',
        'booking_id' => 'required',
        'client_id' => 'required',
        'price' => 'required|numeric',
        'currency' => 'required|string|max:3',
        'course_subgroup_id' => 'nullable',
        'course_id' => 'nullable',
        'course_date_id' => 'required',
        'degree_id' => 'nullable',
        'course_group_id' => 'nullable',
        'monitor_id' => 'nullable',
        'date' => 'nullable',
        'hour_start' => 'nullable',
        'hour_end' => 'nullable',
        'attended' => 'nullable',
        'status' => 'numeric',
        'color' => 'nullable|string|max:45',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }


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

    public function courseSubGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseSubgroup::class, 'course_group_id');
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Booking::class, 'booking_id');
    }

    public function courseDate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseDate::class, 'course_date_id');
    }

    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class, 'monitor_id');
    }

    public function bookingUserExtras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUserExtra::class, 'booking_user_id');
    }

    public function scopeByMonitor($query, $monitor)
    {
        return $query->where('monitor_id', $monitor);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
