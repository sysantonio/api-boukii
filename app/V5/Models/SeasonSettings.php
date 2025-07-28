<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="V5SeasonSettings",
 *     required={"season_id","key"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="season_id", type="integer"),
 *     @OA\Property(property="key", type="string"),
 *     @OA\Property(property="value", type="object", nullable=true),
 * )
 */

class SeasonSettings extends Model
{
    use HasFactory;

    protected $table = 'season_settings';

    protected $fillable = [
        'season_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static array $rules = [
        'season_id' => 'required|integer',
        'key' => 'required|string|max:255',
        'value' => 'nullable',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
