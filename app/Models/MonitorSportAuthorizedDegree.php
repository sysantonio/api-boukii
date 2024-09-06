<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="MonitorSportAuthorizedDegree",
 *      required={"monitor_sport_id","degree_id"},
 *       @OA\Property(
 *           property="monitor_sport_id",
 *           description="ID of the monitor sport",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="degree_id",
 *           description="ID of the degree authorized for the monitor sport",
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
 */class MonitorSportAuthorizedDegree extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitor_sport_authorized_degrees';

    protected $with = ['degree'];

    public $fillable = [
        'monitor_sport_id',
        'degree_id'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'monitor_sport_id' => 'required',
        'degree_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function degree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'degree_id');
    }

    public function monitorSport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\MonitorSportsDegree::class, 'monitor_sport_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
