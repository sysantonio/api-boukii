<?php

namespace App\Repositories;

use App\Models\StationService;
use App\Repositories\BaseRepository;

class StationServiceRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'station_id',
        'service_type_id',
        'name',
        'url',
        'telephone',
        'email',
        'image',
        'active'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StationService::class;
    }
}
