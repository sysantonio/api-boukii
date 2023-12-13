<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseIcon
 *
 * @property int $id
 * @property int $sport_id
 * @property int $course_type_id
 * @property string $icon
 *
 * @property Sport $sport
 * @property CourseType $course_type
 *
 * @package App\Models
 */
class CourseIcon extends Model
{
	protected $table = 'courses_icons';
	public $timestamps = false;

	protected $casts = [
		'sport_id' => 'int',
		'course_type_id' => 'int'
	];

	protected $connection = 'old';

protected $fillable = [
		'sport_id',
		'course_type_id',
		'icon'
	];

	public function sport()
	{
		return $this->belongsTo(Sport::class);
	}

	public function course_type()
	{
		return $this->belongsTo(CourseType::class);
	}
}
