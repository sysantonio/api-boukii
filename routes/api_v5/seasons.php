<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\SeasonController;

Route::middleware(['auth:sanctum', 'school.context.middleware', 'role.permission.middleware:seasons.manage'])
    ->prefix('seasons')
    ->name('v5.seasons.')
    ->group(function () {
        Route::get('/', [SeasonController::class, 'index'])->name('index');
        Route::post('/', [SeasonController::class, 'store'])->name('store');
        Route::get('/current', [SeasonController::class, 'current'])->name('current');
        Route::get('/{season}', [SeasonController::class, 'show'])->name('show');
        Route::put('/{season}', [SeasonController::class, 'update'])->name('update');
        Route::patch('/{season}', [SeasonController::class, 'update'])->name('patch');
        Route::delete('/{season}', [SeasonController::class, 'destroy'])->name('destroy');
        Route::post('/{season}/activate', [SeasonController::class, 'activate'])->name('activate');
        Route::post('/{season}/deactivate', [SeasonController::class, 'deactivate'])->name('deactivate');
        Route::post('/{season}/close', [SeasonController::class, 'close'])->name('close');
        Route::post('/{season}/reopen', [SeasonController::class, 'reopen'])->name('reopen');
    });
