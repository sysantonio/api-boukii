<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;

// Routes for Boukii Sport (i.e. Client's) app

// Public

// Private
Route::middleware('userRequired:client')->group(function() {
    // My profile: basic data

});
