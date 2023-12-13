<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteSchools extends Model
{
    protected $connection = 'old';

protected $fillable = [
        'user_id',
        'school_id'
    ];


    /**
     * Relations
     */

    public function schools()
	{
		return $this->hasMany(School::class, 'school_id');
	}
}
