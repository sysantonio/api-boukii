<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Sport",
 *      required={"name","icon_selected","icon_unselected","sport_type"},
 *      @OA\Property(
 *          property="name",
 *          description="Name of the sport",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="icon_selected",
 *          description="Icon when the sport is selected",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="icon_unselected",
 *          description="Icon when the sport is not selected",
 *          type="string"
 *      ),
 *      @OA\Property(
 *           property="icon_collective",
 *           description="Icon when the sport is collective",
 *           type="string"
 *       ),
 *     @OA\Property(
 *           property="icon_activity",
 *           description="Icon when the sport is activity",
 *           type="string"
 *       ),
 *       @OA\Property(
 *           property="icon_prive",
 *           description="Icon when the sport is prive",
 *           type="string"
 *       ),
 *      @OA\Property(
 *          property="sport_type",
 *          description="Type of sport",
 *          type="string"
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
 *          description="Last update timestamp",
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
class Sport extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'sports';

    public $fillable = [
        'name',
        'icon_collective',
        'icon_prive',
        'icon_activity',
        'icon_selected',
        'icon_unselected',
        'sport_type'
    ];

    protected $casts = [
        'name' => 'string',
        'icon_collective' => 'string',
        'icon_prive' => 'string',
        'icon_activity' => 'string',
        'icon_selected' => 'string',
        'icon_unselected' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'icon_collective' => 'required|string|max:500',
        'icon_prive' => 'required|string|max:500',
        'icon_activity' => 'required|string|max:500',
        'icon_selected' => 'required|string|max:500',
        'icon_unselected' => 'required|string|max:500',
        'sport_type' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function sportType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SportType::class, 'sport_type');
    }

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Course::class, 'sport_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_sports', 'sport_id', 'school_id');
    }

    public function degrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Degree::class, 'sport_id');
    }

    public function monitorSportsDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorSportsDegree::class, 'sport_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
