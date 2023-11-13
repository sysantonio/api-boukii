<?php

namespace App\Repositories;

use App\Models\SchoolSalaryLevel;
use App\Repositories\BaseRepository;

class SchoolSalaryLevelRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'school_id',
        'name',
        'pay'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SchoolSalaryLevel::class;
    }
}
