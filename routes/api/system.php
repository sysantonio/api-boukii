<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\SystemValidationController;
use App\Http\Controllers\System\HealthCheckController;

Route::get('validate', [SystemValidationController::class, 'validateSystem']);
Route::get('health', HealthCheckController::class);
