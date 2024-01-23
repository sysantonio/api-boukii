<?php

use App\Models\Client;
use App\Models\Course;
use App\Models\Language;
use App\Models\Mail;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\Station;
use App\Models\StationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

Route::any('/users-permisions', function () {

    foreach (User::all() as $user) {
        $user->setInitialPermissionsByRole();
    }
});

Route::any('/mailtest', function () {

    $bookingData = \App\Models\Booking::find(5561);
    $bookingData->loadMissing(['bookingUsers', 'bookingUsers.client', 'bookingUsers.degree', 'bookingUsers.monitor',
        'bookingUsers.courseSubGroup', 'bookingUsers.course', 'bookingUsers.courseDate']);
    $schoolData = \App\Models\School::find(1);
    $userData = Client::find($bookingData->client_main_id);
    //return response()->json($bookingData);
    // Apply that user's language - or default
    $defaultLocale = config('app.fallback_locale');
    $oldLocale = \App::getLocale();
    $userLang = Language::find($userData->language_id_1);
    $userLocale = $userLang ? $userLang->code : $defaultLocale;
    \App::setLocale($userLocale);


    $templateView = 'mails.bookingInfo';

    $footerView = 'mails.footer';

    $templateMail = Mail::where('type', 'booking_cancel')->where('school_id', $schoolData->id)
        ->where('lang', $userLocale)->first();

    $voucherCode = "";
    if (isset($voucherData->code)) $voucherCode = $voucherData->code;
    $voucherAmount = "";
    if (isset($voucherData->quantity)) $voucherAmount = number_format($voucherData->quantity, 2);


    $templateData = [
        'titleTemplate' => $templateMail->title ?? '',
        'bodyTemplate' => $templateMail->body ?? '',
        'userName' => trim($userData->first_name . ' ' . $userData->last_name),
        'schoolName' => $schoolData->name,
        'schoolLogo' => $schoolData->logo,
        'schoolEmail' => $schoolData->contact_email,
        'schoolConditionsURL' => $schoolData->conditions_url,
        'reference' => '#' . $bookingData->id,
        'bookingNotes' => $bookingData->notes,
        'courses' => $bookingData->parseBookedGroupedCourses(),
        'voucherCode' => $voucherCode,
        'voucherAmount' => $voucherAmount,
        'hasCancellationInsurance' => false,
        'actionURL' => null,
        'footerView' => $footerView
    ];


    $subject = __('emails.bookingInfo.subject');

    return view($templateView)->with($templateData);
});

Route::post('payrexxNotification', [\App\Http\Controllers\PayrexxController::class, 'processNotification'])
    ->name('api.migration.data');

Route::get('payrexx/finish', function (Request $request) {
    return response()->make('Payrexx close ' . $request->status, 200);
})->name('api.payrexx.finish');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('migration/data', [\App\Http\Controllers\MigrationController::class, 'migrateInitalData'])
    ->name('api.migration.data');

Route::get('migration/clients', [\App\Http\Controllers\MigrationController::class, 'migrateClients'])
    ->name('api.migration.clients');

Route::get('migration/monitors', [\App\Http\Controllers\MigrationController::class, 'migrateMonitors'])
    ->name('api.migration.monitors');

Route::get('migration/schools', [\App\Http\Controllers\MigrationController::class, 'migrateUsersSchools'])
    ->name('api.migration.schools');

Route::get('migration/courses', [\App\Http\Controllers\MigrationController::class, 'migrateCourses'])
    ->name('api.migration.courses');

Route::get('migration/bookings', [\App\Http\Controllers\MigrationController::class, 'migrateBookings'])
    ->name('api.migration.bookings');

Route::get('migration/all', [\App\Http\Controllers\MigrationController::class, 'migrateAll'])
    ->name('api.migration.all');

Route::get('/fix-nwds', function () {
    // Obtener todos los MonitorNwds
    $monitorNwds = MonitorNwd::all();

    foreach ($monitorNwds as $monitorNwd) {
        // Verificar si start_date es diferente de end_date
        if ($monitorNwd->start_date != $monitorNwd->end_date) {
            $startDate = \Carbon\Carbon::parse($monitorNwd->start_date);
            $endDate = \Carbon\Carbon::parse($monitorNwd->end_date);

            // Eliminar el registro antiguo
            $monitorNwd->delete();

            // Crear nuevos registros para cada día dentro del rango
            while ($startDate->lte($endDate)) {
                $newMonitorNwd = new MonitorNwd($monitorNwd->toArray());
                $newMonitorNwd->start_date = $startDate->toDateString();
                $newMonitorNwd->end_date = $startDate->toDateString();
                $newMonitorNwd->save();
                $startDate->addDay(); // Avanzar al siguiente día
            }
        }
    }
});

Route::get('/process-images', function () {
    $models = [
        Course::all(), Station::all(), Client::all(),
        Monitor::all(), StationService::all(), User::all()
    ];

    $modelFile = [
        \App\Models\EvaluationFile::all()
    ];

    foreach ($models as $modelCollection) {
        foreach ($modelCollection as $model) {
            $base64Image = $model->image;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $imageData = base64_decode($imageData);
                $imageName = 'image_' . time() . '.' . $type[1];
                Storage::disk('public')->put($imageName, $imageData);
                $model->image = Storage::disk('public')->url($imageName);
                $model->save();
            }
        }
    }

    foreach ($modelFile as $modelCollection) {
        foreach ($modelCollection as $model) {
            $base64File = $model->file; // Asumiendo que el campo se llama 'file'

            // Extraer el tipo MIME y decodificar el archivo
            if (preg_match('/^data:(\w+\/[\w\-\+\.]+);base64,/', $base64File, $matches)) {
                $type = $matches[1]; // Tipo MIME del archivo
                $fileData = substr($base64File, strpos($base64File, ',') + 1);
                $fileData = base64_decode($fileData);

                if ($fileData === false) {
                    throw new \Exception('base64_decode failed');
                }

                // Determinar la extensión del archivo a partir del tipo MIME
                $extension = ''; // Extensión predeterminada
                $mimeMap = [
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    'image/gif' => 'gif',
                    'image/bmp' => 'bmp',
                    'image/tiff' => 'tiff',
                    'image/svg+xml' => 'svg',
                    'application/pdf' => 'pdf',
                    'video/mp4' => 'mp4',
                    'image/webp' => 'webp',
                    // Añadir más tipos MIME según sea necesario
                ];

                if (isset($mimeMap[$type])) {
                    $extension = $mimeMap[$type];
                } else {
                    // Manejar archivos con tipos MIME desconocidos o no soportados
                    continue; // O manejar de otra manera
                }

                $fileName = 'files/' . time() . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $model->file = Storage::disk('public')->url($fileName);
                $model->save();
            }
        }
    }

    return 'Proceso completado';
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
Route::prefix('slug')
    ->group(base_path('routes/api/bookingPage.php'));
/* API IFRAME */
