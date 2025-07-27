<?php

namespace App\Services\Admin\V3;

use Illuminate\Http\Request;

class DashboardSummaryService
{
    public function getSummary(Request $request): array
    {
        return [
            'totalBookings' => 120,
            'upcomingBookings' => 34,
            'revenueToday' => 1500,
        ];
    }
}
