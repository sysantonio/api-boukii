<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;

// Routes for Boukii Teach (i.e. Monitor's) app
Route::post('login', [\App\Http\Controllers\Teach\AuthController::class, 'login'])->name('api.admin.login');

// Private
Route::middleware('userRequired:monitor')->group(function() {

});
