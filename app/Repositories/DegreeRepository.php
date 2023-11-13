<?php

namespace App\Repositories;

use App\Models\Degree;
use App\Repositories\BaseRepository;

class DegreeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'league',
        'level',
        'name',
        'annotation',
        'degree_order',
        'progress',
        'color',
        'school_id',
        'sport_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Degree::class;
    }
}
