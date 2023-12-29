<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\BaseRepository;

class BookingRepository extends BaseRepository
{
    protected $fieldSearchable = [
'id',
        'school_id',
        'client_main_id',
        'price_total',
        'has_cancellation_insurance',
        'price_cancellation_insurance',
        'currency',
        'payment_method_id',
        'paid_total',
        'paid',
        'attendance',
        'notes',
        'paxes',
        'color'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Booking::class;
    }
}
