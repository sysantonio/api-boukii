<?php

namespace App\Repositories;

use App\Models\CourseExtra;
use App\Repositories\BaseRepository;

class CourseExtraRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'course_id',
        'name',
        'description',
        'price'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CourseExtra::class;
    }
}
