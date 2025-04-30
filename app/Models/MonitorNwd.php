<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="MonitorNwd",
 *      required={"monitor_id","start_date","end_date","full_day"},
 *      @OA\Property(
 *           property="monitor_id",
 *           description="ID of the monitor",
 *           type="integer",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *           property="school_id",
 *           description="ID of the school",
 *           type="integer",
 *           nullable=true
 *       ),
 *      @OA\Property(
 *           property="station_id",
 *           description="ID of the station",
 *           type="integer",
 *           nullable=true
 *       ),
 *      @OA\Property(
 *           property="start_date",
 *           description="Start date of the non-working duration",
 *           type="string",
 *           format="date",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *           property="end_date",
 *           description="End date of the non-working duration",
 *           type="string",
 *           format="date",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *           property="start_time",
 *           description="Start time of the non-working duration",
 *           type="string",
 *           format="time",
 *           nullable=true
 *       ),
 *      @OA\Property(
 *           property="end_time",
 *           description="End time of the non-working duration",
 *           type="string",
 *           format="time",
 *           nullable=true
 *       ),
 *      @OA\Property(
 *          property="full_day",
 *          description="Indicates if the non-working duration is for a full day",
 *          type="boolean",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *           property="default",
 *           description="Indicates if the non-working is default",
 *           type="boolean",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *          property="description",
 *          description="Description of the non-working duration",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="color",
 *          description="Color code for representing the non-working duration",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="user_nwd_subtype_id",
 *          description="ID of the user NWD subtype",
 *          type="integer",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *           property="price",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="number",
 *           format="number"
 *       ),
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
class MonitorNwd extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitor_nwd';

    public $fillable = [
        'monitor_id',
        'school_id',
        'station_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'full_day',
        'default',
        'description',
        'color',
        'user_nwd_subtype_id',
        'price'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'full_day' => 'boolean',
        'default' => 'boolean',
        'description' => 'string',
        'color' => 'string',
    ];

    public static array $rules = [
        'monitor_id' => 'required',
        'school_id' => 'nullable',
        'station_id' => 'nullable',
        'start_date' => 'required',
        'end_date' => 'required',
        'start_time' => 'nullable',
        'end_time' => 'nullable',
        'full_day' => 'required|boolean',
        'default' => 'nullable',
        'price' => 'nullable',
        'description' => 'nullable|string|max:65535',
        'color' => 'nullable|string|max:45',
        'user_nwd_subtype_id' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class, 'monitor_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function station(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'station_id');
    }

    // En BookingUser.php
    public function scopeOnlyWeekends($query)
    {
        return $query->whereRaw('WEEKDAY(start_date) IN (5, 6)');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
