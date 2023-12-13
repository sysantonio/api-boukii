<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteStations extends Model
{
    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'station_id'
    ];


    /**
     * Relations
     */

    public function stations()
	{
		return $this->hasMany(Station::class, 'station_id');
	}
}
