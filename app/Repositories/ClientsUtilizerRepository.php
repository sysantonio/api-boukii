<?php

namespace App\Repositories;

use App\Models\ClientsUtilizer;
use App\Repositories\BaseRepository;

class ClientsUtilizerRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'main_id',
        'client_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClientsUtilizer::class;
    }
}
