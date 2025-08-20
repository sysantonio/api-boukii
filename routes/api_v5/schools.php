<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('schools')
    ->name('v5.schools.')
    ->group(function () {
        Route::get('/', function () {
            return response()->json([]);
        })->name('index');
    });
