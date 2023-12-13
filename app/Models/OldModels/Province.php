<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property string $contry_iso
 *
 * @property Country $country
 *
 * @package App\Models
 */
class Province extends Model
{
    protected $table = 'provinces';
    public $timestamps = false;

    protected $casts = [
		'country_id' => 'int'
	];


    /**
     * Relations
     */

	public function country()
	{
		return $this->belongsTo(Country::class, 'country_id');
	}


    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // unused as of 2022-11 because Provinces are always searched by Country
            // 'country_id' => $this->country_id,
            // 'country_iso' => $this->country_iso
        ];
    }
}
