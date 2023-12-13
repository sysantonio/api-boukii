<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourseAttendance extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'subgroup_id',
        'date_id',
        'user_id',
        'attendance',
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at',
    ];
}
