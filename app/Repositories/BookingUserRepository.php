<?php

namespace App\Repositories;

use App\Models\BookingUser;
use App\Repositories\BaseRepository;

class BookingUserRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'school_id',
        'booking_id',
        'client_id',
        'price',
        'currency',
        'course_subgroup_id',
        'course_id',
        'course_date_id',
        'degree_id',
        'course_group_id',
        'monitor_id',
        'date',
        'hour_start',
        'hour_end',
        'status',
        'attended',
        'color'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return BookingUser::class;
    }
}
