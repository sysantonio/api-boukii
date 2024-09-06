<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="ClientsUtilizer",
 *      required={"main_id", "client_id"},
 *      @OA\Property(
 *          property="main_id",
 *          description="Main client ID",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="client_id",
 *          description="Utilizer client ID",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Update timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class ClientsUtilizer extends Model
{

    use LogsActivity, SoftDeletes, HasFactory;

    public $table = 'clients_utilizers';

    public $fillable = [
        'main_id',
        'client_id'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'main_id' => 'required',
        'client_id' => 'required',
        'updated_at' => 'nullable',
        'created_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function main(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'main_id');
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
