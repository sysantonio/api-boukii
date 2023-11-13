<?php

use Illuminate\Support\Facades\Route;

use App\Models\UserType;


// Private
Route::middleware('userRequired:superadmin')->group(function() {



});
