<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\MeSchoolController;

Route::middleware(['auth:sanctum', 'throttle:api'])
    ->prefix('me')
    ->name('v5.me.')
    ->group(function () {
        Route::get('/schools', [MeSchoolController::class, 'index'])->name('schools.index');
    });
