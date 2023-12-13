<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class StationSchools extends Model
{
    protected $table = 'stations_schools';
	public $timestamps = false;

    protected $connection = 'old';

protected $fillable = [
        'station_id',
        'school_id'
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
