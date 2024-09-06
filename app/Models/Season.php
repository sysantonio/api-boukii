<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Season",
 *      required={"start_date","end_date","is_active","school_id"},
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="start_date",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="end_date",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *            property="hour_start",
 *            description="Start Hour",
 *            type="string",
 *            nullable=true,
 *            format="time"
 *        ),
 *        @OA\Property(
 *            property="hour_end",
 *            description="End Hour",
 *            type="string",
 *            nullable=true,
 *            format="time"
 *        ),
 *      @OA\Property(
 *          property="is_active",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *     @OA\Property(
 *           property="vacation_days",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *           property="school_id",
 *           description="ID of the school associated with the season",
 *           type="integer",
 *           format="int64",
 *           example=1
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
 */class Season extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'seasons';

    public $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'hour_start',
        'hour_end',
        'vacation_days',
        'school_id'
    ];

    protected $casts = [
        'name' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'vacation_days' => 'string',
        'is_active' => 'boolean'
    ];

    public static array $rules = [
        'name' => 'nullable|string|max:255',
        'start_date' => 'required',
        'end_date' => 'required',
        'hour_start' => 'nullable',
        'hour_end' => 'nullable',
        'is_active' => 'required|boolean',
        'school_id' => 'required',
        'vacation_days' => 'nullable|string',
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
