<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

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
}
