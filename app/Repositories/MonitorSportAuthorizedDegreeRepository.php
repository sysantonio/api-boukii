<?php

namespace App\Repositories;

use App\Models\MonitorSportAuthorizedDegree;
use App\Repositories\BaseRepository;

class MonitorSportAuthorizedDegreeRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'monitor_sport_id',
        'degree_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MonitorSportAuthorizedDegree::class;
    }
}
