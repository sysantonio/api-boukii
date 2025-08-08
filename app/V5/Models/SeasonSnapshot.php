<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="V5SeasonSnapshot",
 *     required={"season_id","snapshot_type"},
 *
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="season_id", type="integer"),
 *     @OA\Property(property="snapshot_type", type="string"),
 *     @OA\Property(property="snapshot_data", type="object", nullable=true),
 *     @OA\Property(property="snapshot_date", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="is_immutable", type="boolean"),
 *     @OA\Property(property="created_by", type="integer", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="checksum", type="string", nullable=true),
 * )
 */
class SeasonSnapshot extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'season_snapshots';

    protected $fillable = [
        'season_id',
        'snapshot_type',
        'snapshot_data',
        'snapshot_date',
        'is_immutable',
        'created_by',
        'description',
        'checksum',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'snapshot_date' => 'datetime',
        'is_immutable' => 'boolean',
    ];

    public static array $rules = [
        'season_id' => 'required|integer',
        'snapshot_type' => 'required|string|max:255',
        'snapshot_data' => 'nullable|array',
        'snapshot_date' => 'nullable|date',
        'is_immutable' => 'boolean',
        'created_by' => 'nullable|integer',
        'description' => 'nullable|string',
        'checksum' => 'nullable|string',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->checksum = hash('sha256', json_encode($model->snapshot_data));
        });

        static::updating(function ($model) {
            if ($model->is_immutable && $model->isDirty()) {
                throw new \Exception('Immutable snapshots cannot be modified');
            }
        });
    }

    public function verifyIntegrity(): bool
    {
        $expected = hash('sha256', json_encode($this->snapshot_data));

        return $this->checksum === $expected;
    }
}
