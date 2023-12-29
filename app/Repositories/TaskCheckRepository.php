<?php

namespace App\Repositories;

use App\Models\TaskCheck;
use App\Repositories\BaseRepository;

class TaskCheckRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'text',
        'checked',
        'task_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return TaskCheck::class;
    }
}
