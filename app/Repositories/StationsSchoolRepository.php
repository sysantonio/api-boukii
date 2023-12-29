<?php

namespace App\Repositories;

use App\Models\StationsSchool;
use App\Repositories\BaseRepository;

class StationsSchoolRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'station_id',
        'school_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StationsSchool::class;
    }
}
