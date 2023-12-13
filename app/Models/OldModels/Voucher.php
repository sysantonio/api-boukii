<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\School;
use App\Models\OldModels\UserSchools;

class Voucher extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'code',
        'quantity',
        'remaining_balance',
        'payed',
        'user_id',
        'school_id',
        'payrexx_reference',
		'payrexx_transaction'
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];

    public function school()
	{
		return $this->belongsTo(School::class);
	}

    public static function listFromClient($userID)
    {
        $mySchool = UserSchools::getAdminSchool();
        if (!$mySchool)
        {
            $this->messages[] = 'No School';
            $this->code = Response::HTTP_UNAUTHORIZED;
            return $this->sendResponse();
        }

        $vouchers = self::where('user_id', '=', $userID)
                    ->where('school_id', $mySchool->id)
                    ->where('remaining_balance', '>', 0)
                    ->get();

        return $vouchers;
    }

    public static function listFromIframeClient($schoolID)
    {
        $myUser = \Auth::user();

        $vouchers = self::where('user_id', '=', $myUser->id)
                    ->where('school_id', $schoolID)
                    ->where('remaining_balance', '>', 0)
                    ->get();

        return $vouchers;
    }

    public static function checkFromIframe($schoolID, $code, $id)
    {
        $voucher = array();

        if ($id > 0) {
            $voucher1 = self::where('school_id', $schoolID)
                    ->where('id', '=', $id)
                    ->where('remaining_balance', '>', 0)
                    ->first();
            if(isset($voucher1->id)) $voucher = $voucher1;
        }

        if (strlen($code)>0) {
            $voucher1 = self::where('school_id', $schoolID)
                ->where('code', '=', $code)
                ->where('remaining_balance', '>', 0)
                ->first();
            if(isset($voucher1->id)) $voucher = $voucher1;
        }

        return $voucher;
    }

    public static function checkFromClient($userID, $code, $id)
    {
        $mySchool = UserSchools::getAdminSchool();
        if (!$mySchool)
        {
            $this->messages[] = 'No School';
            $this->code = Response::HTTP_UNAUTHORIZED;
            return $this->sendResponse();
        }

        $voucher = array();

        if ($id > 0) {
            $voucher1 = self::where('user_id', '=', $userID)
                    ->where('school_id', $mySchool->id)
                    ->where('id', '=', $id)
                    ->where('remaining_balance', '>', 0)
                    ->first();
            if(isset($voucher1->id)) $voucher = $voucher1;
        }

        if (strlen($code)>0) {
            $voucher1 = self::where('school_id', $mySchool->id)
                ->where('code', '=', $code)
                ->where('remaining_balance', '>', 0)
                ->first();
            if(isset($voucher1->id)) $voucher = $voucher1;
        }


        return $voucher;
    }

    public static function getFromIframe($schoolID, $code)
    {
        $voucher = self::where('school_id', $schoolID)
                ->where('code', '=', $code)
                ->where('remaining_balance', '>', 0)
                ->first();

        return $voucher;
    }

    public static function getFromClient($userID, $code)
    {
        $mySchool = UserSchools::getAdminSchool();
        if (!$mySchool) return false;

        $voucher = self::where('user_id', '=', $userID)
                    ->where('school_id', $mySchool->id)
                    ->where('code', '=', $code)
                    ->where('remaining_balance', '>', 0)
                    ->first();

        return $voucher;
    }

    public static function updateRemainingBalance($voucherID, $amount)
    {
        self::where('id', $voucherID)
                ->update(['remaining_balance' => $amount]);
    }

    // Special for field "payrexx_transaction": store encrypted
    public function setPayrexxTransaction($value)
    {
        $this->payrexx_transaction = encrypt( json_encode($value) );
    }
    public function getPayrexxTransaction()
    {
        $decrypted = null;
        if ($this->payrexx_transaction)
        {
            try
            {
                $decrypted = decrypt($this->payrexx_transaction);
            }
            // @codeCoverageIgnoreStart
            catch (\Illuminate\Contracts\Encryption\DecryptException $e)
            {
                $decrypted = null;  // Data seems corrupt or tampered
            }
            // @codeCoverageIgnoreEnd
        }

        return $decrypted ? json_decode($decrypted, true) : [];
    }

    /**
     * Generate an unique reference for Payrexx - only for bookings that wanna pay this way
     * (i.e. BoukiiPay or Online)
     */
    public function generatePayrexxReference()
    {
        $ref = 'Boukii Voucher #' . $this->id;
        $this->payrexx_reference = (env('APP_ENV') == 'production') ? $ref : 'TEST ' . $ref;
        $this->save();

        return $this->payrexx_reference;
    }

    public static function checkNewCode($code)
    {
        $voucher = self::where("code", $code)->first();
        if(isset($voucher->id)) return true;
        return false;
    }
}
