<?php

namespace App\Repositories;

use App\Models\EmailLog;
use App\Repositories\BaseRepository;

class EmailLogRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'date',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'headers',
        'attachments'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return EmailLog::class;
    }
}
