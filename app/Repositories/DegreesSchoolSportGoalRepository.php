<?php

namespace App\Repositories;

use App\Models\DegreesSchoolSportGoal;
use App\Repositories\BaseRepository;

class DegreesSchoolSportGoalRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'degree_id',
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return DegreesSchoolSportGoal::class;
    }
}
