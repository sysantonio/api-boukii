<?php

namespace App\Repositories;

use App\Models\CourseDate;
use App\Repositories\BaseRepository;

class CourseDateRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'course_id',
        'date',
        'hour_start',
        'hour_end'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CourseDate::class;
    }
}
