<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TaskCheck
 *
 * @property int $id
 * @property string $text
 * @property bool $checked
 * @property int $task_id
  * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Task $task
 *
 * @package App\Models
 */
class TaskCheck extends Model
{
    use SoftDeletes;
    protected $table = 'task_checks';

    protected $connection = 'old';

protected $fillable = [
        'text',
        'checked',
        'task_id'
    ];

    protected $casts = [
        'checked' => 'bool',
		'task_id' => 'int'
	];

    protected $hidden = [
        'updated_at'
    ];


    /**
     * Relations
     */

	public function task()
	{
		return $this->belongsTo(Task::class);
	}


    /**
     * Convert "this" TaskCheck fields to an array.
     * Note that as of 2022-11:
     *   - Frontend prefers bools ("checked") as integers.
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'text' => $this->text,
            'checked' => $this->checked ? 1 : 0,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : ''
        ];
    }
}
