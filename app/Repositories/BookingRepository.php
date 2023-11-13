<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\BaseRepository;

class BookingRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'school_id',
        'client_main_id',
        'price_total',
        'has_cancellation_insurance',
        'price_cancellation_insurance',
        'currency',
        'payment_method_id',
        'paid_total',
        'paid',
        'payrexx_reference',
        'payrexx_transaction',
        'attendance',
        'payrexx_refund',
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
