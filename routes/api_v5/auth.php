<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\AuthController;

Route::prefix('auth')->name('v5.auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('initial-login', [AuthController::class, 'initialLogin'])->name('initial-login');
    Route::post('check-user', [AuthController::class, 'checkUser'])->name('check-user');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('select-school', [AuthController::class, 'selectSchool'])->name('select-school');
        Route::post('select-season', [AuthController::class, 'selectSeason'])->name('select-season');
    });
});
