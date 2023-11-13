<?php

namespace App\Repositories;

use App\Models\MonitorNwd;
use App\Repositories\BaseRepository;

class MonitorNwdRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'monitor_id',
        'school_id',
        'station_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'full_day',
        'description',
        'color',
        'user_nwd_subtype_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MonitorNwd::class;
    }
}
