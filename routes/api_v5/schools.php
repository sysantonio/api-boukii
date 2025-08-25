<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\SchoolController;

Route::middleware('auth:sanctum')
    ->prefix('schools')
    ->name('v5.schools.')
    ->group(function () {
        Route::get('/', [SchoolController::class, 'index'])->name('index');
        Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
    });
