<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StationServices extends Model
{
    use SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'station_id',
        'service_type_id',
        'name',
        'url',
        'telephone',
        'email',
        'image',
        'active'
    ];


    /**
     * Relations
     */

	public function type()
	{
		return $this->belongsTo(ServiceType::class, 'service_type_id', 'id');
	}
}
