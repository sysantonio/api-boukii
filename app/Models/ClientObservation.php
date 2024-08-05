<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="ClientObservation",
 *      required={"general", "notes", "historical", "client_id", "school_id"},
 *      @OA\Property(
 *          property="general",
 *          description="General observations about the client",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="notes",
 *          description="Additional notes regarding the client",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="historical",
 *          description="Historical information about the client",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="client_id",
 *          description="ID of the client",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="school_id",
 *          description="ID of the school",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp of the observation",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Last update timestamp of the observation",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp of the observation",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class ClientObservation extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'client_observations';

    public $fillable = [
        'general',
        'notes',
        'historical',
        'client_id',
        'school_id'
    ];

    protected $casts = [
        'general' => 'string',
        'notes' => 'string',
        'historical' => 'string'
    ];

    public static array $rules = [
        'general' => 'nullable|string|max:5000',
        'notes' => 'nullable|string|max:5000',
        'historical' => 'nullable|string|max:5000',
        'client_id' => 'required|integer|exists:clients,id',
        'school_id' => 'required|integer|exists:schools,id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
