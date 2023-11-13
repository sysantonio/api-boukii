<?php

namespace App\Repositories;

use App\Models\SchoolUser;
use App\Repositories\BaseRepository;

class SchoolUserRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'school_id',
        'user_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SchoolUser::class;
    }
}
