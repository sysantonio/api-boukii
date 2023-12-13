<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSportAuthorizedDegrees extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'user_sport_id',
        'degree_id',
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
