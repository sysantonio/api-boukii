<?php

namespace App\Repositories;

use App\Models\BookingUserExtra;
use App\Repositories\BaseRepository;

class BookingUserExtraRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'booking_user_id',
        'course_extra_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return BookingUserExtra::class;
    }
}
