<?php

namespace App\Repositories;

use App\Models\ClientObservation;
use App\Repositories\BaseRepository;

class ClientObservationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'client_id',
        'general',
        'notes',
        'historical',
        'client_id',
        'school_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClientObservation::class;
    }
}
