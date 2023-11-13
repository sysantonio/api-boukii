<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;

// Routes for Boukii Teach (i.e. Monitor's) app


// Private
Route::middleware('userRequired:monitor')->group(function() {

});
