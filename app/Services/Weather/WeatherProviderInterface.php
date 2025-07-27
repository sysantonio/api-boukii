<?php

namespace App\Services\Weather;

interface WeatherProviderInterface
{
    /**
     * Get a 12-hour forecast for the given station.
     *
     * @param int $stationId
     * @return array
     */
    public function get12HourForecast(int $stationId): array;

    /**
     * Get a 5-day forecast for the given station.
     *
     * @param int $stationId
     * @return array
     */
    public function get5DayForecast(int $stationId): array;
}
