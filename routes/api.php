<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* API PAYREXX */
Route::prefix('')
    ->group(base_path('routes/api/payrexx.php'));
/* API PAYREXX */

/* API USER TYPE ADMIN */
Route::prefix('admin')
    ->group(base_path('routes/api/admin.php'));
/* API USER TYPE ADMIN */


/* API USER TYPE SUPERADMIN */
Route::prefix('superadmin')
    ->group(base_path('routes/api/superadmin.php'));
/* API USER TYPE SUPERADMIN */


/* API APP SPORTS */
Route::prefix('sports')
    ->group(base_path('routes/api/sports.php'));

/* API APP SPORTS */


/* API APP TEACH */
Route::prefix('teach')
    ->group(base_path('routes/api/teach.php'));
/* API APP TEACH */


/* API PUBLIC */
Route::prefix('')
    ->group(base_path('routes/api/public.php'));
/* API PUBLIC */

/* API IFRAME */
Route::prefix('bookingPage')
    ->group(base_path('routes/api/bookingPage.php'));
/* API IFRAME */

Route::get('availiability', [App\Http\Controllers\API\AvailabilityAPIController::class, 'index'])
    ->name('api.availiability.index');


Route::resource('stations', App\Http\Controllers\API\StationAPIController::class)
    ->except(['create', 'edit']);

Route::resource('stations-schools', App\Http\Controllers\API\StationsSchoolAPIController::class)
    ->except(['create', 'edit']);

Route::resource('service-types', App\Http\Controllers\API\ServiceTypeAPIController::class)
    ->except(['create', 'edit']);

Route::resource('station-services', App\Http\Controllers\API\StationServiceAPIController::class)
    ->except(['create', 'edit']);

Route::resource('sport-types', App\Http\Controllers\API\SportTypeAPIController::class)
    ->except(['create', 'edit']);

Route::resource('sports', App\Http\Controllers\API\SportAPIController::class)
    ->except(['create', 'edit']);

Route::put('schools/{id}/sports', [\App\Http\Controllers\API\SchoolAPIController::class, 'updateSchoolSports'])
    ->name('api.schools.updateSchoolSports');

Route::resource('schools', App\Http\Controllers\API\SchoolAPIController::class)
    ->except(['create', 'edit']);

Route::resource('school-users', App\Http\Controllers\API\SchoolUserAPIController::class)
    ->except(['create', 'edit']);

Route::put('school-colors/multiple', [App\Http\Controllers\API\SchoolColorAPIController::class, 'updateColors'])
    ->name('api.schools.updatemultiple');


Route::resource('school-colors', App\Http\Controllers\API\SchoolColorAPIController::class)
    ->except(['create', 'edit']);

Route::resource('school-salary-levels', App\Http\Controllers\API\SchoolSalaryLevelAPIController::class)
    ->except(['create', 'edit']);

Route::resource('school-sports', App\Http\Controllers\API\SchoolSportAPIController::class)
    ->except(['create', 'edit']);

Route::resource('bookings', App\Http\Controllers\API\BookingAPIController::class)
    ->except(['create', 'edit']);

Route::resource('booking-users', App\Http\Controllers\API\BookingUserAPIController::class)
    ->except(['create', 'edit']);

Route::resource('booking-user-extras', App\Http\Controllers\API\BookingUserExtraAPIController::class)
    ->except(['create', 'edit']);

Route::resource('courses', App\Http\Controllers\API\CourseAPIController::class)
    ->except(['create', 'edit']);

Route::resource('course-dates', App\Http\Controllers\API\CourseDateAPIController::class)
    ->except(['create', 'edit']);

Route::resource('course-extras', App\Http\Controllers\API\CourseExtraAPIController::class)
    ->except(['create', 'edit']);

Route::resource('course-groups', App\Http\Controllers\API\CourseGroupAPIController::class)
    ->except(['create', 'edit']);

Route::resource('course-subgroups', App\Http\Controllers\API\CourseSubgroupAPIController::class)
    ->except(['create', 'edit']);

Route::resource('degrees', App\Http\Controllers\API\DegreeAPIController::class)
    ->except(['create', 'edit']);

Route::resource('degrees-school-sport-goals', App\Http\Controllers\API\DegreesSchoolSportGoalAPIController::class)
    ->except(['create', 'edit']);

Route::resource('evaluation-fulfilled-goals', App\Http\Controllers\API\EvaluationFulfilledGoalAPIController::class)
    ->except(['create', 'edit']);

Route::resource('evaluations', App\Http\Controllers\API\EvaluationAPIController::class)
    ->except(['create', 'edit']);

Route::resource('email-logs', App\Http\Controllers\API\EmailLogAPIController::class)
    ->except(['create', 'edit']);

Route::resource('task-checks', App\Http\Controllers\API\TaskCheckAPIController::class)
    ->except(['create', 'edit']);

Route::resource('tasks', App\Http\Controllers\API\TaskAPIController::class)
    ->except(['create', 'edit']);

Route::resource('seasons', App\Http\Controllers\API\SeasonAPIController::class)
    ->except(['create', 'edit']);

Route::resource('languages', App\Http\Controllers\API\LanguageAPIController::class)
    ->except(['create', 'edit']);

Route::resource('users', App\Http\Controllers\API\UserAPIController::class)
    ->except(['create', 'edit']);

Route::resource('clients', App\Http\Controllers\API\ClientAPIController::class)
    ->except(['create', 'edit']);

Route::resource('clients-utilizers', App\Http\Controllers\API\ClientsUtilizerAPIController::class)
    ->except(['create', 'edit']);

Route::resource('clients-schools', App\Http\Controllers\API\ClientsSchoolAPIController::class)
    ->except(['create', 'edit']);

Route::resource('client-observations', App\Http\Controllers\API\ClientObservationAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitors', App\Http\Controllers\API\MonitorAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitor-nwds', App\Http\Controllers\API\MonitorNwdAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitor-observations', App\Http\Controllers\API\MonitorObservationAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitors-schools', App\Http\Controllers\API\MonitorsSchoolAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitor-sports-degrees', App\Http\Controllers\API\MonitorSportsDegreeAPIController::class)
    ->except(['create', 'edit']);

Route::resource('monitor-sport-authorized-degrees', App\Http\Controllers\API\MonitorSportAuthorizedDegreeAPIController::class)
    ->except(['create', 'edit']);

Route::resource('vouchers', App\Http\Controllers\API\VoucherAPIController::class)
    ->except(['create', 'edit']);

Route::resource('vouchers-logs', App\Http\Controllers\API\VouchersLogAPIController::class)
    ->except(['create', 'edit']);

Route::resource('booking-logs', App\Http\Controllers\API\BookingLogAPIController::class)
    ->except(['create', 'edit']);


Route::resource('client-sports', App\Http\Controllers\API\ClientSportAPIController::class)
    ->except(['create', 'edit']);
