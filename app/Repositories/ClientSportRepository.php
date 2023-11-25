<?php

namespace App\Repositories;

use App\Models\ClientSport;
use App\Repositories\BaseRepository;

class ClientSportRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'client_id',
        'sport_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClientSport::class;
    }
}
