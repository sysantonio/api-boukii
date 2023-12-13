<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 *
 * @property int $id
 * @property string $name
 * @property string $iso
 *
 * @property Collection|Province[] $provinces
 *
 * @package App\Models
 */
class Country extends Model
{
	protected $table = 'countries';
    public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name',
		'iso'
	];


    /**
     * Relations
     */

	public function provinces()
	{
		return $this->hasMany(Province::class, 'country_id');
	}
}
