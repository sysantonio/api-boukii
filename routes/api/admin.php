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
Route::middleware(['auth:sanctum', 'ability:permissions:all'])->group(function() {
    Route::get('hash', function (Request $request){
        $request->user()->givePermissionTo('edit articles');
        return $request->user();
        return Hash::make($request->password);
    });

});
