<?php

namespace App\Repositories;

use App\Models\CourseGroup;
use App\Repositories\BaseRepository;

class CourseGroupRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'course_id',
        'course_date_id',
        'degree_id',
        'age_min',
        'age_max',
        'recommended_age',
        'teachers_min',
        'teachers_max',
        'observations',
        'teacher_min_degree',
        'auto'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CourseGroup::class;
    }
}
