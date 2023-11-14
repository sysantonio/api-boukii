<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;

// Routes for Boukii Teach (i.e. Monitor's) app
Route::post('login', [\App\Http\Controllers\Teach\AuthController::class, 'login'])->name('api.teach.login');

// Private
Route::middleware(['auth:sanctum', 'ability:teach:all'])->group(function() {
    Route::get('getAgenda', [\App\Http\Controllers\Teach\HomeController::class, 'getAgenda'])->name('api.teach.home.agenda');

});
