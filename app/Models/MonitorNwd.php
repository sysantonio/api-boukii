<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="MonitorNwd",
 *      required={"monitor_id","start_date","end_date","full_day"},
 *      @OA\Property(
 *          property="start_date",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="end_date",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="full_day",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="color",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="user_nwd_subtype_id",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
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
 */class MonitorNwd extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'monitor_nwd';

    public $fillable = [
        'monitor_id',
        'school_id',
        'station_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'full_day',
        'description',
        'color',
        'user_nwd_subtype_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'full_day' => 'boolean',
        'description' => 'string',
        'color' => 'string',
        'user_nwd_subtype_id' => 'boolean'
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
        'description' => 'nullable|string|max:65535',
        'color' => 'nullable|string|max:45',
        'user_nwd_subtype_id' => 'nullable|boolean',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
