<?php

namespace App\Models\OldModels;

/**
 * @deprecated
 *      -> BookingUsers2 with $course_groups_subgroup2_id=null
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingsLibresUsers extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'booking_libre_id',
        'client_id',
        'attended',
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
