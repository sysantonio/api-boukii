<?php

namespace App\Repositories;

use App\Models\CourseExtra;
use App\Repositories\BaseRepository;

class CourseExtraRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'course_id',
        'name',
        'description',
        'group',
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
