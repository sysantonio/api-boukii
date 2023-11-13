<?php

namespace App\Repositories;

use App\Models\VouchersLog;
use App\Repositories\BaseRepository;

class VouchersLogRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'voucher_id',
        'booking_id',
        'amount'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return VouchersLog::class;
    }
}
