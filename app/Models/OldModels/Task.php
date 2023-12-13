<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Task
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $date
 * @property Carbon|null $time
 * @property int $school_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property School $school
 * @property Collection|TaskCheck[] $checks
 *
 * @package App\Models
 */
class Task extends Model
{
    use SoftDeletes;
    protected $table = 'tasks';

    protected $connection = 'old';

protected $fillable = [
        'name',
        'date',
        'time',
        'school_id'
    ];

    protected $casts = [
		'school_id' => 'int'
	];

    protected $dates = [
		'date', 'time'
	];

    protected $hidden = [
        'updated_at', 'deleted_at'
    ];


    /**
     * Relations
     */

	public function school()
	{
		return $this->belongsTo(School::class);
	}

    public function checks()
	{
		return $this->hasMany(TaskCheck::class, 'task_id');
	}



}
