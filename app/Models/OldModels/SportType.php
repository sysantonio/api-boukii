<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SportType
 *
 * @property int $id
 * @property string $name
 *
 * @property Collection|Sport[] $sports
 *
 * @package App\Models
 */
class SportType extends Model
{
    protected $table = 'sport_types';
    public $timestamps = false;

    /**
     * Relations
     */

	public function sports()
	{
		return $this->hasMany(Sport::class, 'sport_type', 'id');
	}
}
