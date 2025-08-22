<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="V5UserSeasonRole",
 *     required={"user_id","season_id","role"},
 *
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="season_id", type="integer"),
 *     @OA\Property(property="role", type="string")
 * )
 */
class UserSeasonRole extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_season_roles';

    public $incrementing = false;
    protected $primaryKey = ['user_id', 'season_id', 'role'];
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'season_id',
        'role',
    ];

    public static array $rules = [
        'user_id' => 'required|integer',
        'season_id' => 'required|integer',
        'role' => 'required|string|max:255',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
