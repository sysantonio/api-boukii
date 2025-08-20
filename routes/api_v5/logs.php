<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\ClientLogController;

Route::post('/logs', [ClientLogController::class, 'store'])->middleware('throttle:logging');
Route::post('/telemetry', [ClientLogController::class, 'store'])->middleware('throttle:logging');
