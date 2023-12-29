<?php

namespace App\Repositories;

use App\Models\EvaluationFulfilledGoal;
use App\Repositories\BaseRepository;

class EvaluationFulfilledGoalRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'evaluation_id',
        'degrees_school_sport_goals_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return EvaluationFulfilledGoal::class;
    }
}
