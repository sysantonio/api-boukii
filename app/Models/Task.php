<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Task",
 *      required={"name","date","time","school_id"},
 *      @OA\Property(
 *          property="name",
 *          description="The name of the task",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="date",
 *          description="The date of the task",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="time",
 *          description="The time of the task",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="school_id",
 *          description="The ID of the school associated with the task",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="The timestamp when the task was created",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="The timestamp when the task was last updated",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="The timestamp when the task was deleted",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class Task extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $table = 'tasks';

    public $fillable = [
        'name',
        'date',
        'time',
        'school_id'
    ];

    protected $casts = [
        'name' => 'string',
        'date' => 'date',
        'time' => 'string',
        'school_id' => 'integer',
    ];

    public static array $rules = [
        'name' => 'required|string|max:200',
        'date' => 'required|date',
        'time' => 'required|string',
        'school_id' => 'required|integer',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function taskChecks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TaskCheck::class, 'task_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
