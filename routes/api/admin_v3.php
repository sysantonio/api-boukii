<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::get('/summary', function () {
        return response()->json(['message' => 'summary']);
    });

    Route::get('/courses', function () {
        return response()->json(['message' => 'courses']);
    });

    Route::get('/weather', function () {
        return response()->json(['message' => 'weather']);
    });

    Route::get('/sales', function () {
        return response()->json(['message' => 'sales']);
    });

    Route::get('/reservations', function () {
        return response()->json(['message' => 'reservations']);
    });
});
