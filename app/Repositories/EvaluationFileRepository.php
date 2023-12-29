<?php

namespace App\Repositories;

use App\Models\EvaluationFile;
use App\Repositories\BaseRepository;

class EvaluationFileRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'evaluation_id',
        'name',
        'type',
        'file'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return EvaluationFile::class;
    }
}
