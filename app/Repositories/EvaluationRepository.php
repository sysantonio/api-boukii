<?php

namespace App\Repositories;

use App\Models\Evaluation;
use App\Repositories\BaseRepository;

class EvaluationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'client_id',
        'degree_id',
        'observations'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Evaluation::class;
    }
}
