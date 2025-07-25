<?php

namespace App\Repositories;

use App\Models\SchoolColor;
use App\Repositories\BaseRepository;

class SchoolColorRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'school_id',
        'name',
        'color',
        'default'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SchoolColor::class;
    }
}
