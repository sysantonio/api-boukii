<?php

namespace App\Repositories;

use App\Models\Season;
use App\Repositories\BaseRepository;

class SeasonRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'school_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Season::class;
    }
}
