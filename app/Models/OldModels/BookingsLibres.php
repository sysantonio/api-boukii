<?php

namespace App\Models\OldModels;

/**
 * @deprecated
 *      -> Bookings2
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingsLibres extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'course_libre_id',
        'monitor_id',
        'date',
        'hour',
        'payment_method_id',
        'paid',
        'attendance',
        'payrexx_reference'
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


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
     * Get the School of the CourseLibre related to "this" BookingLibre.
     *
     * @return School|null
     */
    public function getSchoolData()
    {
        $course = CoursesLibre::find($this->course_libre_id);

        return $course ? School::find($course->school_id) : null;
    }
}
