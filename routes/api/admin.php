<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;


// Public
Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('api.admin.login');
/*
Route::delete('logout', [\App\Http\Controllers\Auth\LogoutController::class, 'destroy'])->name('api.admin.logout');
Route::post('auth/recover-password', [\App\Http\Controllers\Auth\AuthController::class, 'recoverPassword'])->name('api.admin.recoverPassword');
Route::post('auth/reset-password/{token}', [\App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);*/


// Private
Route::middleware(['auth:sanctum', 'ability:admin:all'])->group(function() {

    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class)
        ->except(['create', 'edit']);

    Route::get('getPlanner', [\App\Http\Controllers\Admin\PlannerController::class, 'getPlanner'])
        ->name('api.admin.planner');

    Route::resource('clients', App\Http\Controllers\Admin\ClientsController::class)
        ->except(['create', 'edit']);

    Route::get('clients/{id}/utilizers', [\App\Http\Controllers\Admin\ClientsController::class, 'getUtilizers']);

});
