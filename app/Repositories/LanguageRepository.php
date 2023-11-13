<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\BaseRepository;

class LanguageRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'code',
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Language::class;
    }
}
