<?php

namespace App\Repositories;

use App\Models\SchoolColor;
use App\Repositories\BaseRepository;

class SchoolColorRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'school_id',
        'name',
        'color'
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
