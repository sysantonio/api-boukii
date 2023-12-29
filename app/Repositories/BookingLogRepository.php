<?php

namespace App\Repositories;

use App\Models\BookingLog;
use App\Repositories\BaseRepository;

class BookingLogRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'booking_id',
        'action',
        'description',
        'user_id',
        'before_change'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return BookingLog::class;
    }
}
