<?php

namespace App\Services\Admin\V3;

use Illuminate\Http\Request;

class CourseStatsService
{
    public function getCourseStats(Request $request): array
    {
        return [
            'totalCourses' => 10,
            'topCourses' => [
                ['id' => 1, 'name' => 'Ski Basics', 'bookings' => 50],
                ['id' => 2, 'name' => 'Advanced Ski', 'bookings' => 30],
            ],
        ];
    }
}
