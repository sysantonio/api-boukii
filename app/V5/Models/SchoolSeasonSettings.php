<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="V5SchoolSeasonSettings",
 *     required={"school_id","season_id","key"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="school_id", type="integer"),
 *     @OA\Property(property="season_id", type="integer"),
 *     @OA\Property(property="key", type="string"),
 *     @OA\Property(property="value", type="object", nullable=true)
 * )
 */
class SchoolSeasonSettings extends Model
{
    use HasFactory;

    protected $table = 'school_season_settings';

    protected $fillable = [
        'school_id',
        'season_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static array $rules = [
        'school_id' => 'required|integer',
        'season_id' => 'required|integer',
        'key' => 'required|string|max:255',
        'value' => 'nullable',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
