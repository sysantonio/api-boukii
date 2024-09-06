<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="VouchersLog",
 *      required={"voucher_id","booking_id","amount"},
 *      @OA\Property(
 *          property="voucher_id",
 *          description="The ID of the voucher associated with the log entry",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="booking_id",
 *          description="The ID of the booking associated with the log entry",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="amount",
 *          description="The amount associated with the log entry",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="The timestamp when the log entry was created",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="The timestamp when the log entry was last updated",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="The timestamp when the log entry was deleted",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class VouchersLog extends Model
{
    use SoftDeletes;
    use HasFactory;
    public $table = 'vouchers_log';

    public $fillable = [
        'voucher_id',
        'booking_id',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public static array $rules = [
        'voucher_id' => 'required',
        'booking_id' => 'required',
        'amount' => 'required|numeric',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Booking::class, 'booking_id');
    }

    public function voucher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Voucher::class, 'voucher_id');
    }

public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
