<?php

namespace App\Repositories;

use App\Models\MonitorObservation;
use App\Repositories\BaseRepository;

class MonitorObservationRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'general',
        'notes',
        'historical',
        'monitor_id',
        'school_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MonitorObservation::class;
    }
}
