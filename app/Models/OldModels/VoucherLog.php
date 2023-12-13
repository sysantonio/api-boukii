<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherLog extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'vouchers_log';

	protected $connection = 'old';

protected $fillable = [
		'voucher_id',
		'booking_id',
        "amount"
	];

    protected $guarded = [];
    protected $hidden = ["updated_at"];

    public static function getLog($booking_id)
    {
        $voucher = self::select('vouchers_log.amount', 'vouchers.code')
                    ->join('vouchers', 'vouchers.id', '=', 'vouchers_log.voucher_id')
                    ->where('vouchers_log.booking_id', '=', $booking_id)
                    ->first();

        return $voucher;
    }

}
