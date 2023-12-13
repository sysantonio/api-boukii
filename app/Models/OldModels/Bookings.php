<?php

namespace App\Models\OldModels;

/**
 * @deprecated
 *      -> Bookings2
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'course_group_subgroup_id',
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
        'payrexx_transaction'
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
     * Get the School of the Course related to "this" Booking.
     *
     * @return School|null
     */
    public function getSchoolData()
    {
        $subgroup = CourseGroupsSubgroups::find($this->course_group_subgroup_id);
        $group = $subgroup ? CourseGroups::find($subgroup->course_group_id) : null;
        $course = $group ? Course::find($group->course_id) : null;

        return $course ? School::find($course->school_id) : null;
    }
}
