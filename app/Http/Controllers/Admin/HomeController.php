<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\Station;
use Illuminate\Http\Request;

class HomeController extends AppBaseController
{
    public function get12HourlyForecastByStation(Request $request)
    {
        $forecast = [];

        $station = Station::find($request->station_id);

        if ($station)
        {
            // Pick its Station coordinates:
            // TODO TBD what about Schools located at _several_ Stations ??
            // As of 2022-11 just forecast the first one
            $accuweatherData = ($station && $station->accuweather) ?
                json_decode($station->accuweather, true) : [];
            $forecast = $accuweatherData['12HoursForecast'] ?? [];
        }

        return $this->sendResponse($forecast, 'Weather send correctly');
    }

    public function get5DaysForecastByStation(Request $request)
    {
        $forecast = [];

        $station = Station::find($request->station_id);

        if ($station)
        {
            // Pick its Station coordinates:
            // TODO TBD what about Schools located at _several_ Stations ??
            // As of 2022-11 just forecast the first one
            $accuweatherData = ($station && $station->accuweather) ?
                json_decode($station->accuweather, true) : [];
            $forecast = $accuweatherData['5DaysForecast'] ?? [];
        }

        return $this->sendResponse($forecast, 'Weather send correctly');
    }
}
