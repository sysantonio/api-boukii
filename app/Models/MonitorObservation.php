<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="MonitorObservation",
 *      required={"general","notes","historical","monitor_id","school_id"},
 *      @OA\Property(
 *          property="general",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="notes",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="historical",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="monitor_id",
 *           description="ID of the monitor the observation is about",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="school_id",
 *           description="ID of the school associated with the observation",
 *           type="integer",
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
 */class MonitorObservation extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitor_observations';

    public $fillable = [
        'general',
        'notes',
        'historical',
        'monitor_id',
        'school_id'
    ];

    protected $casts = [
        'general' => 'string',
        'notes' => 'string',
        'historical' => 'string'
    ];

    public static array $rules = [
        'general' => 'string|max:5000',
        'notes' => 'string|max:5000',
        'historical' => 'string|max:5000',
        'monitor_id' => 'required',
        'school_id' => 'required',
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

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
