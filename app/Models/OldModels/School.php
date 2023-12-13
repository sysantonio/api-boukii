<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class School extends Model
{
    public $timestamps = true;
    protected $connection = 'old';

protected $fillable = [
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'contact_telephone',
        'contact_address',
        'contact_cp',
        'contact_city',
        'contact_province_id',
        'contact_country_id',
        'fiscal_name',
        'fiscal_id',
        'fiscal_address',
        'fiscal_cp',
        'fiscal_city',
        'fiscal_province_id',
        'fiscal_country_id',
        'iban',
        'logo',
        'slug',
        'conditions_url',
        'active',
        'bookings_comission_cash',
        'bookings_comission_boukii_pay',
        'bookings_comission_other',
        'school_rate',
        'type',
        'payrexx_instance',
        'payrexx_key',
    ];

    protected $guarded = [];

    protected $hidden = [
        'updated_at',
        'pivot',
    ];

    /**
     * Relations
     */

    public function contact_country()
	{
		return $this->belongsTo(Country::class, 'contact_country_id', 'id');
	}

    public function contact_province()
	{
		return $this->belongsTo(Province::class, 'contact_province_id', 'id');
	}

    public function fiscal_country()
	{
		return $this->belongsTo(Country::class, 'fiscal_country_id', 'id');
	}

    public function fiscal_province()
	{
		return $this->belongsTo(Province::class, 'fiscal_province_id', 'id');
	}

    public function stations()
    {
        return $this->belongsToMany(Station::class, 'stations_schools', 'school_id', 'station_id')->orderBy('name', 'asc');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_schools', 'school_id', 'user_id');
    }

    /**
     * Helpers
     */

    public static function getOpeningTimes($format = null)
    {
        // Static opening times, prepared for the future will be dynamic
        $start = Carbon::createFromTimeString('08:00');
        $end = Carbon::createFromTimeString('17:00');
        return (object)[
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end,
        ];
    }

    /**
     * Payrexx
     */

    // Special for field "payrexx_instance" & "payrexx_key": store encrypted
    public function setPayrexxInstance($value)
    {
        $this->payrexx_instance = encrypt($value);
    }

    public function getPayrexxInstance()
    {
        $decrypted = null;
        if ($this->payrexx_instance)
        {
            try
            {
                $decrypted = decrypt($this->payrexx_instance);
            }
            // @codeCoverageIgnoreStart
            catch (\Illuminate\Contracts\Encryption\DecryptException $e)
            {
                $decrypted = null;  // Data seems corrupt or tampered
            }
            // @codeCoverageIgnoreEnd
        }

        return $decrypted;
    }

    public function setPayrexxKey($value)
    {
        $this->payrexx_key = encrypt($value);
    }

    public function getPayrexxKey()
    {
        $decrypted = null;
        if ($this->payrexx_key)
        {
            try
            {
                $decrypted = decrypt($this->payrexx_key);
            }
            // @codeCoverageIgnoreStart
            catch (\Illuminate\Contracts\Encryption\DecryptException $e)
            {
                $decrypted = null;  // Data seems corrupt or tampered
            }
            // @codeCoverageIgnoreEnd
        }

        return $decrypted;
    }

}
