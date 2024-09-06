<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="MonitorSportsDegree",
 *      required={"sport_id","degree_id","monitor_id","is_default"},
 *      @OA\Property(
 *          property="is_default",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *       @OA\Property(
 *           property="sport_id",
 *           description="ID of the sport",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="school_id",
 *           description="ID of the school",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="degree_id",
 *           description="ID of the degree",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="monitor_id",
 *           description="ID of the monitor",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="salary_level",
 *           description="Salary level for the monitor",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="allow_adults",
 *           description="Indicates if adults are allowed",
 *           type="boolean",
 *           nullable=true
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
class MonitorSportsDegree extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitor_sports_degrees';

    public $fillable = [
        'sport_id',
        'school_id',
        'degree_id',
        'monitor_id',
        'salary_level',
        'allow_adults',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'allow_adults' => 'boolean',

    ];

    public static array $rules = [
        'sport_id' => 'required',
        'school_id' => 'nullable',
        'degree_id' => 'required',
        'monitor_id' => 'required',
        'salary_level' => 'nullable',
        'allow_adults' => 'nullable',
        'is_default' => 'required|boolean',
        'updated_at' => 'nullable',
        'created_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function degree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'degree_id');
    }

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Sport::class, 'sport_id');
    }

    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class, 'monitor_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function salary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolSalaryLevel::class, 'salary_level', 'id');
    }

    public function monitorSportAuthorizedDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorSportAuthorizedDegree::class, 'monitor_sport_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
