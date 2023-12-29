<?php

namespace App\Repositories;

use App\Models\Degree;
use App\Repositories\BaseRepository;

class DegreeRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'league',
        'level',
        'name',
        'annotation',
        'degree_order',
        'progress',
        'color',
        'active',
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
