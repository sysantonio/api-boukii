<?php

namespace App\Repositories;

use App\Models\MonitorSportsDegree;
use App\Repositories\BaseRepository;

class MonitorSportsDegreeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'sport_id',
        'school_id',
        'degree_id',
        'monitor_id',
        'salary_level',
        'allow_adults',
        'is_default'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MonitorSportsDegree::class;
    }
}
