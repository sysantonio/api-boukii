<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SkiConditionsController;

Route::get('ski-conditions', [SkiConditionsController::class, 'current']);
