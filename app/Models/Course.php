<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Course",
 *      required={"course_type","is_flexible","sport_id","school_id","name","short_description","description","price","currency","max_participants","duration","duration_flexible","date_start","date_end","confirm_attendance","active","online"},
 *      @OA\Property(
 *          property="course_type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="is_flexible",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="short_description",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="price",
 *          description="If duration_flexible, per 15min",
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
 *          property="duration_flexible",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="date_start",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_end",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_start_res",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_end_res",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="confirm_attendance",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="online",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="translations",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="price_range",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="discounts",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="settings",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="sport_id",
 *           description="Sport ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="school_id",
 *           description="School ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="station_id",
 *           description="Station ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="max_participants",
 *           description="Maximum number of participants",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="duration",
 *           description="Duration of the course",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="hour_min",
 *           description="Minimum hour for the course",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="hour_max",
 *           description="Maximum hour for the course",
 *           type="string",
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
 */class Course extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'courses';

    public $fillable = [
        'course_type',
        'is_flexible',
        'sport_id',
        'school_id',
        'station_id',
        'name',
        'short_description',
        'description',
        'price',
        'currency',
        'max_participants',
        'duration',
        'duration_flexible',
        'date_start',
        'date_end',
        'date_start_res',
        'date_end_res',
        'hour_min',
        'hour_max',
        'confirm_attendance',
        'active',
        'online',
        'image',
        'translations',
        'price_range',
        'discounts',
        'settings'
    ];

    protected $casts = [
        'is_flexible' => 'boolean',
        'name' => 'string',
        'short_description' => 'string',
        'description' => 'string',
        'price' => 'decimal:2',
        'currency' => 'string',
        'duration' => 'string',
        'duration_flexible' => 'boolean',
        'date_start' => 'date',
        'date_end' => 'date',
        'date_start_res' => 'date',
        'date_end_res' => 'date',
        'hour_min' => 'string',
        'hour_max' => 'string',
        'confirm_attendance' => 'boolean',
        'active' => 'boolean',
        'online' => 'boolean',
        'image' => 'string',
        'translations' => 'string',
        'price_range' => 'json',
        'discounts' => 'string',
        'settings' => 'string'
    ];

    public static array $rules = [
        'course_type' => 'required',
        'is_flexible' => 'required|boolean',
        'sport_id' => 'required',
        'school_id' => 'required',
        'station_id' => 'nullable',
        'name' => 'required|string|max:65535',
        'short_description' => 'required|string|max:65535',
        'description' => 'required|string|max:65535',
        'price' => 'required|numeric',
        'currency' => 'required|string|max:3',
        'max_participants' => 'required',
        'duration' => 'required',
        'duration_flexible' => 'required|boolean',
        'date_start' => 'required',
        'date_end' => 'required',
        'date_start_res' => 'nullable',
        'date_end_res' => 'nullable',
        'hour_min' => 'nullable|string|max:255',
        'hour_max' => 'nullable|string|max:255',
        'confirm_attendance' => 'required|boolean',
        'active' => 'required|boolean',
        'online' => 'required|boolean',
        'image' => 'nullable|string',
        'translations' => 'nullable|string',
        'price_range' => 'nullable|string',
        'discounts' => 'nullable|string',
        'settings' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Sport::class, 'sport_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function station(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'station_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_id');
    }

    public function courseDates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseDate::class, 'course_id');
    }

    public function courseExtras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseExtra::class, 'course_id');
    }

    public function courseGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'course_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'course_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
