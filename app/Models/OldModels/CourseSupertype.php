<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseSupertype
 *
 * @property int $id
 * @property string $name
 *
 * @property Collection|Course2[] $courses
 *
 * @package App\Models
 */
class CourseSupertype extends Model
{
	protected $table = 'course_supertypes';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name'
	];

	public function courses()
	{
		return $this->hasMany(Course2::class);
	}


    // Constant CourseSupertype IDs as of 2022-10:
    /** CourseSupertype for 'definido' supertype */
    const ID_DEFINITE = 1;
     /** CourseSupertype for 'libre' type */
    const ID_LOOSE = 2;
}
