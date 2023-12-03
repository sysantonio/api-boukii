<?php

use App\Models\Course2;
use App\Models\CourseGlobal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


// Convenient alias of some public routes, so Iframe Frontend always calls "/iframe/xxx" urls


// Iframe with school
Route::middleware(['bookingPage'])->group(function() {


    Route::get('courses', [\App\Http\Controllers\BookingPage\CourseController::class, 'index'])
        ->name('api.bookings.courses.index');
});
