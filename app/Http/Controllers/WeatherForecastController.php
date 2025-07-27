<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherForecastController extends AppBaseController
{
    public function forecast12Hours()
    {
        $data = [
            'location' => 'Demo Mountain',
            'forecast' => [
                [
                    'time' => now()->addHour()->format('H:00'),
                    'temperature' => -1,
                    'condition' => 'snow'
                ]
            ]
        ];

        return $this->sendResponse($data, '12 hour forecast retrieved');
    }

    public function forecast5Days()
    {
        $data = [
            'location' => 'Demo Mountain',
            'forecast' => [
                [
                    'date' => now()->format('Y-m-d'),
                    'low' => -5,
                    'high' => 2,
                    'condition' => 'sunny'
                ]
            ]
        ];

        return $this->sendResponse($data, '5 day forecast retrieved');
    }
}
