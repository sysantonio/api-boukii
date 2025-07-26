<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsAPIController extends AppBaseController
{
    public function optimizationSuggestions(Request $request): JsonResponse
    {
        $type = $request->get('type', 'general');
        $timeframe = $request->get('timeframe', 'monthly');

        $data = [
            'type' => $type,
            'timeframe' => $timeframe,
            'suggestions' => [
                'Review pricing strategies for better margins',
                'Consider promotions to increase early bookings',
            ],
        ];

        return $this->sendResponse($data, 'Optimization suggestions retrieved');
    }
}
