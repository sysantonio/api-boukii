<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CourseDates2
 *
 * @property int $id
 * @property int $course2_id
 * @property Carbon $date
 * @property Carbon $hour
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Course2 $course
 *
 * @package App\Models
 */
class CourseDates2 extends Model
{
	use SoftDeletes;
	protected $table = 'course_dates2';

	protected $casts = [
		'course2_id' => 'int'
	];

	protected $dates = [
		'date',
		'hour'
	];

	protected $connection = 'old';

protected $fillable = [
		'course2_id',
		'date',
		'hour',
        'created_at',
        'updated_at'
	];

	public function course()
	{
		return $this->belongsTo(Course2::class, 'course2_id');
	}
}
