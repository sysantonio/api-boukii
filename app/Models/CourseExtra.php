<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="CourseExtra",
 *      required={"course_id", "name", "price"},
 *      @OA\Property(
 *          property="course_id",
 *          description="ID of the course",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Name of the course extra",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="Description of the course extra",
 *          type="string",
 *          nullable=true
 *      ),
 *     @OA\Property(
 *          property="group",
 *          description="group of the course extra when is activity",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="price",
 *          description="Price of the course extra",
 *          type="number",
 *          format="number",
 *          nullable=false
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
 *          description="Update timestamp",
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
class CourseExtra extends Model
{
    use LogsActivity, SoftDeletes, HasFactory;

    public $table = 'course_extras';

    public $fillable = [
        'course_id',
        'name',
        'description',
        'group',
        'price'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'group' => 'string',
        'price' => 'decimal:2'
    ];

    public static array $rules = [
        'course_id' => 'required',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:255',
        'group' => 'nullable|string|max:255',
        'price' => 'required|numeric',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function bookingUserExtras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUserExtra::class, 'course_extra_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
