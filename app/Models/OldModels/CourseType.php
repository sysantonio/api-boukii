<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseType
 *
 * @property int $id
 * @property string $name
 *
 * @property Collection|Course2[] $courses
 * @property Collection|CoursesIcon[] $icons
 *
 * @package App\Models
 */
class CourseType extends Model
{
	protected $table = 'course_types';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name'
	];

	public function courses()
	{
		return $this->hasMany(Course2::class);
	}

	public function icons()
	{
		return $this->hasMany(CoursesIcon::class);
	}


    // Constant CourseType IDs as of 2022-10:
    /** CourseType for 'collectif' type */
    const ID_COLLECTIF = 1;
     /** CourseType for 'prive' type */
    const ID_PRIVE = 2;
     /** CourseType for 'speciaux' type */
    const ID_SPECIAUX = 3;
}
