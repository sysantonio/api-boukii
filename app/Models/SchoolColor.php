<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="SchoolColor",
 *      required={"school_id","name"},
 *      @OA\Property(
 *          property="name",
 *          description="Name of the color associated with the school",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *           property="school_id",
 *           description="Unique identifier of the school associated with this color",
 *           type="integer",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *          property="color",
 *          description="Hexadecimal or other string representation of the color",
 *          type="string",
 *          nullable=true
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
 */
class SchoolColor extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'school_colors';

    public $fillable = [
        'school_id',
        'name',
        'color'
    ];

    protected $casts = [
        'name' => 'string',
        'color' => 'string'
    ];

    public static array $rules = [
        'school_id' => 'required',
        'name' => 'required|string|max:100',
        'color' => 'nullable|string|max:45',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

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
