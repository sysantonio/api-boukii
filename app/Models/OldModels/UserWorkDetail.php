<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkDetail extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'avs',
        'work_license',
        'bank_details',
        'country_id',
        'children',
        'civil_status',
        'family_allowance',
        'partner_work_license',
        'partner_works',
        'partner_percentaje',
        'user_id',
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];
}
