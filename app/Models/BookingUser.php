<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="BookingUser",
 *      required={"booking_id","client_id","price","currency","course_date_id","attended"},
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
 */class BookingUser extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'booking_users';

    public $fillable = [
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
        'color'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'currency' => 'string',
        'date' => 'date',
        'attended' => 'boolean',
        'color' => 'string'
    ];

    public static array $rules = [
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
        'attended' => 'required|boolean',
        'color' => 'nullable|string|max:45',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
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
        return $this->hasMany(\App\Models\BookingUserExtra::class, 'boouking_user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
