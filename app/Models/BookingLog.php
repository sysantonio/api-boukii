<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="BookingLog",
 *      required={"booking_id", "action", "user_id"},
 *      @OA\Property(
 *          property="booking_id",
 *          description="Booking ID",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="action",
 *          description="Action performed",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="Description of the action",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="User ID who performed the action",
 *          type="integer",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="before_change",
 *          description="State of booking before the change",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class BookingLog extends Model
{
    use LogsActivity, SoftDeletes, HasFactory;

    public $table = 'booking_logs';

    public $fillable = [
        'booking_id',
        'action',
        'description',
        'user_id',
        'before_change'
    ];

    protected $casts = [
        'action' => 'string',
        'description' => 'string',
        'before_change' => 'string'
    ];

    public static array $rules = [
        'booking_id' => 'required',
        'action' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'user_id' => 'nullable',
        'before_change' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Booking::class, 'booking_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
