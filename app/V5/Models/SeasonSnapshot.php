<?php

namespace App\V5\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonSnapshot extends Model
{
    use HasFactory;

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
}
