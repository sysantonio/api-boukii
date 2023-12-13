<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceType
 *
 * @property int $id
 * @property string $name
 *
 * @property Collection|StationServices[] $services
 *
 * @package App\Models
 */
class ServiceType extends Model
{
    protected $table = 'service_type';
    public $timestamps = false;

    /**
     * Relations
     */

	public function services()
	{
		return $this->hasMany(StationServices::class, 'service_type_id', 'id');
	}


    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
