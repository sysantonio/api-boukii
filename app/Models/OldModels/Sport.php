<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Sport
 *
 * @property int $id
 * @property string $name
 * @property string $icon_selected
 * @property string $icon_unselected
 * @property int $sport_type
 *
 * @property SportType $type
 *
 * @package App\Models
 */
class Sport extends Model
{
    protected $table = 'sports';
    public $timestamps = false;

    protected $casts = [
		'sport_type' => 'int'
	];

    /**
     * Relations
     */

	public function type()
	{
		return $this->belongsTo(SportType::class, 'sport_type');
	}


    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon_selected,
            'sport_type' => $this->sport_type
        ];
    }
}
