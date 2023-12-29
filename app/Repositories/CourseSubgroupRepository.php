<?php

namespace App\Repositories;

use App\Models\CourseSubgroup;
use App\Repositories\BaseRepository;

class CourseSubgroupRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'course_id',
        'course_date_id',
        'degree_id',
        'course_group_id',
        'monitor_id',
        'max_participants'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CourseSubgroup::class;
    }
}
