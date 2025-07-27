<?php

namespace App\Services\Admin\V3;

use App\Models\School;
use App\Services\Weather\WeatherProviderInterface;
use Illuminate\Http\Request;

class WeatherService
{
    protected WeatherProviderInterface $provider;

    public function __construct(WeatherProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getWeather(Request $request): array
    {
        $stationId = $this->resolveStationId($request);

        if (! $stationId) {
            return [
                'forecast_12_hours' => [],
                'forecast_5_days' => [],
            ];
        }

        return [
            'forecast_12_hours' => $this->provider->get12HourForecast($stationId),
            'forecast_5_days' => $this->provider->get5DayForecast($stationId),
        ];
    }

    private function resolveStationId(Request $request): ?int
    {
        if ($request->filled('station_id')) {
            return (int) $request->station_id;
        }

        if ($request->filled('school_id')) {
            $school = School::with('stationsSchools')->find($request->school_id);
            return $school->stationsSchools[0]->station_id ?? null;
        }

        return null;
    }
}
