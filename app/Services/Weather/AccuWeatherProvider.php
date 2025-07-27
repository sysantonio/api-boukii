<?php

namespace App\Services\Weather;

use App\Models\Station;

class AccuWeatherProvider implements WeatherProviderInterface
{
    public function get12HourForecast(int $stationId): array
    {
        $station = Station::find($stationId);
        if (! $station) {
            return [];
        }

        $data = $station->accuweather ? json_decode($station->accuweather, true) : [];

        return $data['12HoursForecast'] ?? [];
    }

    public function get5DayForecast(int $stationId): array
    {
        $station = Station::find($stationId);
        if (! $station) {
            return [];
        }

        $data = $station->accuweather ? json_decode($station->accuweather, true) : [];

        return $data['5DaysForecast'] ?? [];
    }
}
