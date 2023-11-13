<?php

namespace App\Repositories;

use App\Models\Sport;
use App\Repositories\BaseRepository;

class SportRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'icon_selected',
        'icon_unselected',
        'sport_type'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Sport::class;
    }
}
