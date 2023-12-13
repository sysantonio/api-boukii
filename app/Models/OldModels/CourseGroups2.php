<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CourseGroups2
 *
 * @property int $id
 * @property int $course2_id
 * @property int $degree_id
 * @property int|null $age_min
 * @property int|null $age_max
 * @property int|null $recommended_age
 * @property int $teachers_min
 * @property int $teachers_max
 * @property string|null $observations
 * @property int $teacher_min_degree
 * @property bool $auto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Course2 $course
 * @property Degree $degree
 * @property Collection|CourseGroupsSubgroups2[] $subgroups
 *
 * @package App\Models
 */
class CourseGroups2 extends Model
{
	use SoftDeletes;
	protected $table = 'course_groups2';

	protected $casts = [
		'course2_id' => 'int',
		'degree_id' => 'int',
		'age_min' => 'int',
		'age_max' => 'int',
		'recommended_age' => 'int',
		'teachers_min' => 'int',
		'teachers_max' => 'int',
		'teacher_min_degree' => 'int',
		'auto' => 'bool'
	];

	protected $connection = 'old';

protected $fillable = [
		'course2_id',
		'degree_id',
		'age_min',
		'age_max',
		'recommended_age',
		'teachers_min',
		'teachers_max',
		'observations',
		'teacher_min_degree',
		'auto',
        'created_at',
        'updated_at'
	];

    protected $hidden = ['deleted_at'];


    /**
     * Relations
     */

	public function course()
	{
		return $this->belongsTo(Course2::class, 'course2_id');
	}

	public function degree()
	{
		return $this->belongsTo(Degree::class, 'degree');
	}

    public function teacher_min_degree()
	{
		return $this->belongsTo(Degree::class, 'teacher_min_degree');
	}

	public function subgroups()
	{
		return $this->hasMany(CourseGroupsSubgroups2::class, 'course_group2_id');
	}



}
