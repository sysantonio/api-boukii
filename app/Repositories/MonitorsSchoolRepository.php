<?php

namespace App\Repositories;

use App\Models\MonitorsSchool;
use App\Repositories\BaseRepository;

class MonitorsSchoolRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'monitor_id',
        'school_id',
        'station_id',
        'status_updated_at',
        'accepted_at'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MonitorsSchool::class;
    }
}
