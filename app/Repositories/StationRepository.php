<?php

namespace App\Repositories;

use App\Models\Station;
use App\Repositories\BaseRepository;

class StationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'cp',
        'city',
        'country',
        'province',
        'address',
        'image',
        'map',
        'latitude',
        'longitude',
        'num_hanger',
        'num_chairlift',
        'num_cabin',
        'num_cabin_large',
        'num_fonicular',
        'show_details',
        'active',
        'accuweather'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Station::class;
    }
}
