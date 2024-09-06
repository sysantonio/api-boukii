<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="CourseDate",
 *      required={"course_id", "date", "hour_start", "hour_end"},
 *      @OA\Property(
 *          property="course_id",
 *          description="ID of the course",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="date",
 *          description="Date of the course session",
 *          type="string",
 *          format="date",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="hour_start",
 *          description="Start hour of the course session",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="hour_end",
 *          description="End hour of the course session",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *           property="active",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="boolean",
 *       ),
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
class CourseDate extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'course_dates';

    public $fillable = [
        'course_id',
        'date',
        'hour_start',
        'hour_end',
        'active'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public static array $rules = [
        'course_id' => 'required',
        'date' => 'required',
        'hour_start' => 'required',
        'hour_end' => 'required',
        'active' => 'boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function setHourStartAttribute($value)
    {
        if ($value && \Carbon\Carbon::hasFormat($value, 'H:i')) {
            // Si el formato es correcto, lo guarda con el formato H:i:s
            $this->attributes['hour_start'] = \Carbon\Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } else {
            // Si el formato es incorrecto o no viene, se puede manejar de diferentes maneras
            $this->attributes['hour_start'] = null; // Establece el valor como null o algún valor por defecto
        }
    }

    public function setHourEndAttribute($value)
    {
        if ($value && \Carbon\Carbon::hasFormat($value, 'H:i')) {
            // Si el formato es correcto, lo guarda con el formato H:i:s
            $this->attributes['hour_end'] = \Carbon\Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } else {
            // Si el formato es incorrecto o no viene, lo maneja adecuadamente
            $this->attributes['hour_end'] = null; // Establece el valor como null o algún valor por defecto
        }
    }

    public function getHourStartAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null; // Manejar valor null
    }

    public function getHourEndAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null; // Manejar valor null
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_date_id');
    }

    public function courseGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'course_date_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'course_date_id');
    }

public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
