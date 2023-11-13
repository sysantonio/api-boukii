<?php

namespace App\Repositories;

use App\Models\SchoolSport;
use App\Repositories\BaseRepository;

class SchoolSportRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'school_id',
        'sport_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SchoolSport::class;
    }
}
