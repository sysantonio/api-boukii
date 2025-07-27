<?php

namespace App\Services\Admin\V3;

use Illuminate\Http\Request;

class WeatherService
{
    public function getWeather(Request $request): array
    {
        return [
            'temperature' => 15,
            'condition' => 'Sunny',
        ];
    }
}
