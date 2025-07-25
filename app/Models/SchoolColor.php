<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
 *            property="default",
 *            description="Indicates if the school color is default",
 *            type="boolean",
 *            nullable=false
 *        ),
 *           @OA\Property(
 *           property="price",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="number",
 *           format="number"
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
 */
class SchoolColor extends Model
{
     use LogsActivity, SoftDeletes, HasFactory;     public $table = 'school_colors';

    public $fillable = [
        'school_id',
        'name',
        'color',
        'default',
        'price'
    ];

    protected $casts = [
        'name' => 'string',
        'color' => 'string',
        'default' => 'boolean'
    ];

    public static array $rules = [
        'school_id' => 'required',
        'name' => 'required|string|max:100',
        'color' => 'nullable|string|max:45',
        'default' => 'nullable',
        'price' => 'nullable',
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
         return LogOptions::defaults();
    }
}
