<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherForecastController;

Route::prefix('forecast')->group(function () {
    Route::get('12h', [WeatherForecastController::class, 'forecast12Hours']);
    Route::get('5d', [WeatherForecastController::class, 'forecast5Days']);
});
