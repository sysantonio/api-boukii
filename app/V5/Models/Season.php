<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="V5Season",
 *     required={"start_date","end_date","school_id"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="name", type="string", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date"),
 *     @OA\Property(property="end_date", type="string", format="date"),
 *     @OA\Property(property="hour_start", type="string", format="time", nullable=true),
 *     @OA\Property(property="hour_end", type="string", format="time", nullable=true),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="vacation_days", type="string", nullable=true),
 *     @OA\Property(property="school_id", type="integer"),
 *     @OA\Property(property="is_closed", type="boolean"),
 *     @OA\Property(property="closed_at", type="string", format="date-time", nullable=true),
 * )
 */

class Season extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'seasons';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'hour_start',
        'hour_end',
        'is_active',
        'vacation_days',
        'school_id',
        'is_closed',
        'closed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public static array $rules = [
        'name' => 'nullable|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'hour_start' => 'nullable',
        'hour_end' => 'nullable',
        'is_active' => 'boolean',
        'vacation_days' => 'nullable|string',
        'school_id' => 'required|integer',
        'is_closed' => 'boolean',
        'closed_at' => 'nullable|date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(SeasonSnapshot::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(SeasonSettings::class);
    }
}
