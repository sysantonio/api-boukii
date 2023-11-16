<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="BookingUserExtra",
 *      required={"booking_user_id", "course_extra_id"},
 *      @OA\Property(
 *          property="booking_user_id",
 *          description="Booking User ID",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="course_extra_id",
 *          description="Course Extra ID",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Update timestamp",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class BookingUserExtra extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $table = 'booking_user_extras';

    public $fillable = [
        'booking_user_id', // Corregido el nombre de la propiedad
        'course_extra_id'
    ];

    protected $casts = [
        // Tus definiciones de casts si son necesarias
    ];

    public static array $rules = [
        'booking_user_id' => 'required',
        'course_extra_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];


    public function booukingUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\BookingUser::class, 'booking_user_id');
    }

    public function courseExtra(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CourseExtra::class, 'course_extra_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
