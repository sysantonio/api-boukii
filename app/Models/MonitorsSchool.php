<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="MonitorsSchool",
 *      required={"monitor_id","school_id"},
 *       @OA\Property(
 *           property="monitor_id",
 *           description="ID of the monitor",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="school_id",
 *           description="ID of the school",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="station_id",
 *           description="ID of the station",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="status_updated_at",
 *           description="Timestamp when the status was last updated",
 *           type="string",
 *           format="date-time",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="accepted_at",
 *           description="Timestamp when the monitor was accepted at the school",
 *           type="string",
 *           format="date-time",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="active_school",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="boolean",
 *       ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
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
 *          property="deleted_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class MonitorsSchool extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitors_schools';

    public $fillable = [
        'monitor_id',
        'school_id',
        'station_id',
        'active_school',
        'status_updated_at',
        'accepted_at'
    ];

    protected $casts = [
        'active_school' => 'boolean',
        'status_updated_at' => 'datetime',
        'accepted_at' => 'datetime'
    ];

    public static array $rules = [
        'monitor_id' => 'required',
        'school_id' => 'required',
        'station_id' => 'nullable',
        'active_school' => 'nullable',
        'updated_at' => 'nullable',
        'created_at' => 'nullable',
        'deleted_at' => 'nullable',
        'status_updated_at' => 'nullable',
        'accepted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function station(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'station_id');
    }

    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class, 'monitor_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
