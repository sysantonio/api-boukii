<?php

namespace App\Models\OldModels;

/**
 * @deprecated
 *      -> Course2 with CourseSupertype=ID_LOOSE
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesLibre extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'course_type_id',
        'sport_id',

        'name',
        'price',
        'short_description',
        'description',
        'duration',
        'max_participants',

        'date_start',
        'date_end',

        'school_id',
        'confirm_attendance',
        'active',
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
