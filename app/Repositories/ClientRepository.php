<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\BaseRepository;

class ClientRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'email',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'telephone',
        'address',
        'cp',
        'city',
        'province',
        'country',
        'language1_id',
        'language2_id',
        'language3_id',
        'image',
        'user_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Client::class;
    }
}
