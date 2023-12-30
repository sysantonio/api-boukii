<?php

namespace App\Repositories;

use App\Models\Mail;
use App\Repositories\BaseRepository;

class MailRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'type',
        'subject',
        'body',
        'school_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Mail::class;
    }
}
