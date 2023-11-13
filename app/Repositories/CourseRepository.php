<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\BaseRepository;

class CourseRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'course_type',
        'is_flexible',
        'sport_id',
        'school_id',
        'station_id',
        'name',
        'short_description',
        'description',
        'price',
        'currency',
        'max_participants',
        'duration',
        'duration_flexible',
        'date_start',
        'date_end',
        'date_start_res',
        'date_end_res',
        'hour_min',
        'hour_max',
        'confirm_attendance',
        'active',
        'online',
        'image',
        'translations',
        'price_range',
        'discounts',
        'settings'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Course::class;
    }
}
