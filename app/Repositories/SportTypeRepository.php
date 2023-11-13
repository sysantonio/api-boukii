<?php

namespace App\Repositories;

use App\Models\SportType;
use App\Repositories\BaseRepository;

class SportTypeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SportType::class;
    }
}
