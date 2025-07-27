<?php

namespace App\Services\Admin\V3;

use Illuminate\Http\Request;

class SalesService
{
    public function getSalesData(Request $request): array
    {
        return [
            'totalSales' => 10000,
            'monthly' => [
                ['month' => '2023-01', 'value' => 800],
                ['month' => '2023-02', 'value' => 950],
            ],
        ];
    }
}
