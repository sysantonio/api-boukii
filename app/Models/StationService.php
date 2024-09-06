<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="StationService",
 *      required={"station_id","service_type_id","name","url","telephone","email","image","active"},
 *      @OA\Property(
 *          property="station_id",
 *          description="The ID of the station to which the service belongs",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="service_type_id",
 *          description="The ID of the service type",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="The name of the service",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="url",
 *          description="The URL related to the service",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="telephone",
 *          description="The telephone number related to the service",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="email",
 *          description="The email address related to the service",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="The image related to the service",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="Indicates if the service is active",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="The timestamp when the service was created",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="The timestamp when the service was last updated",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="The timestamp when the service was deleted",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class StationService extends Model
{
    use LogsActivity, SoftDeletes, HasFactory;

    public $table = 'station_service';

    public $fillable = [
        'station_id',
        'service_type_id',
        'name',
        'url',
        'telephone',
        'email',
        'image',
        'active'
    ];

    protected $casts = [
        'station_id' => 'integer',
        'service_type_id' => 'integer',
        'name' => 'string',
        'url' => 'string',
        'telephone' => 'string',
        'email' => 'string',
        'image' => 'string',
        'active' => 'boolean'
    ];

    public static array $rules = [
        'station_id' => 'required|integer',
        'service_type_id' => 'required|integer',
        'name' => 'required|string|max:100',
        'url' => 'nullable|string|max:100',
        'telephone' => 'nullable|string|max:100',
        'email' => 'nullable|string|max:100',
        'image' => 'required|string|max:255',
        'active' => 'required|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function station(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'station_id');
    }

    public function serviceType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ServiceType::class, 'service_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
