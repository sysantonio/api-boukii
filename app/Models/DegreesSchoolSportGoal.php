<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="DegreesSchoolSportGoal",
 *      required={"degree_id", "name"},
 *      @OA\Property(
 *          property="degree_id",
 *          description="ID of the associated degree",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Name of the goal",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp of the school sport goal",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Last update timestamp of the school sport goal",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp of the school sport goal",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class DegreesSchoolSportGoal extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'degrees_school_sport_goals';

    public $fillable = [
        'degree_id',
        'name'
    ];

    protected $casts = [
        'name' => 'string'
    ];

    public static array $rules = [
        'degree_id' => 'required',
        'name' => 'required|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function degree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Degree::class, 'degree_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
