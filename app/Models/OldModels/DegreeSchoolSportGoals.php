<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DegreeSchoolSportGoals
 *
 * @property int $id
 * @property int $degrees_school_sport_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property DegreesSchoolSport $dss
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class DegreeSchoolSportGoals extends Model
{
	use SoftDeletes;
	protected $table = 'degrees_school_sport_goals';

	protected $casts = [
		'degrees_school_sport_id' => 'int'
	];

	protected $connection = 'old';

protected $fillable = [
		'degrees_school_sport_id',
		'name'
	];


    /**
     * Relations
     */

	public function dss()
	{
		return $this->belongsTo(DegreeSchoolSport::class, 'degrees_school_sport_id');
	}



}
