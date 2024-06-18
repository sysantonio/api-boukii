<?php

use App\Models\Client;
use App\Models\ClientSport;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Language;
use App\Models\Mail;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorsSchool;
use App\Models\OldModels\UserSport;
use App\Models\Station;
use App\Models\StationService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Payrexx\Models\Request\Transaction as TransactionRequest;
use Payrexx\Models\Response\Transaction as TransactionResponse;
use Payrexx\Payrexx;

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
    $output = shell_exec('php artisan permission:cache-reset');
    foreach (User::all() as $user) {
        $user->setInitialPermissionsByRole();
    }
});

Route::any('/testAval', function (Request $request) {

        $date = $request->input('date');
        $schoolId = 8;

        // Obtén todos los monitores de la escuela
        $monitors = MonitorsSchool::with(['monitor.sports', 'monitor.courseSubgroups'])
            ->where('school_id', $schoolId)
            ->where('active_school', 1)
            ->get()
            ->pluck('monitor');

        $availableMonitors = [];

        foreach ($monitors as $monitor) {
            // Obtén los NWDS del monitor para ese día
            $nwds = MonitorNwd::where('monitor_id', $monitor->id)
                ->where('school_id', $schoolId)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->get();

            // Obtén los subgrupos asignados al monitor para ese día
            $subgroups = $monitor->courseSubgroups->filter(function ($subgroup) use ($date) {
                return $subgroup->courseDate->date == $date;
            });

            // Define los rangos de horas disponibles para el monitor
            $availableHours = [];

            // Agrega el rango de horas de NWDS al arreglo de horas disponibles
            foreach ($nwds as $nwd) {
                $availableHours[] = [
                    'start_time' => $nwd->start_time,
                    'end_time' => $nwd->end_time,
                ];
            }

            // Agrega los rangos de horas de los subgrupos al arreglo de horas disponibles
            foreach ($subgroups as $subgroup) {
                $availableHours[] = [
                    'start_time' => $subgroup->start_time,
                    'end_time' => $subgroup->end_time,
                ];
            }


            $combinedHours = [];

            // Combinar los rangos de horas solapados
            foreach ($availableHours as $hour) {
                $added = false;
                foreach ($combinedHours as &$combinedHour) {
                    if ($hour['start_time'] <= $combinedHour['end_time'] && $hour['end_time'] >= $combinedHour['start_time']) {
                        // Solapamiento encontrado, combina los rangos
                        $combinedHour['start_time'] = min($hour['start_time'], $combinedHour['start_time']);
                        $combinedHour['end_time'] = max($hour['end_time'], $combinedHour['end_time']);
                        $added = true;
                        break;
                    }
                }
                if (!$added) {
                    // No se encontró solapamiento, agrega el rango como nuevo
                    $combinedHours[] = $hour;
                }
            }

            // Ordenar los rangos combinados por hora de inicio
            usort($combinedHours, function ($a, $b) {
                return $a['start_time'] <=> $b['start_time'];
            });

            $availableMonitors[] = [
                'monitor' => $monitor,
                'available_hours' => $combinedHours,
            ];
        }

        return collect($availableMonitors);

});

Route::any('/monitors-active', function () {
    $monitors = Monitor::with('monitorsSchools.school.stationsSchools')
        ->get();
    foreach ($monitors as $monitor) {
        if(isset($monitor->monitorsSchools[0])) {
            $monitor->active_school =  $monitor->monitorsSchools[0]->school->id;
        }
        if(isset($monitor->monitorsSchools[0]->school->stationsSchools[0])) {
            $monitor->active_station =  $monitor->monitorsSchools[0]->school->stationsSchools[0]->station_id;
        }
        $monitor->save();

    }
});

Route::any('/clients-active', function () {
    $clients = Client::with('utilizers.clientsSchools', 'clientSports.degree', 'clientSports.sport', 'clientsSchools')
        ->get();
    foreach ($clients as $client) {
        if($client->utilizers->count()){
            foreach ($client->utilizers as $utilizer) {
                $clientSchoolIds = $client->clientsSchools->pluck('school_id')->toArray();
                $utilizerSchoolIds = $utilizer->clientsSchools->pluck('school_id')->toArray();

                foreach ($clientSchoolIds as $clientSchoolId) {
                    if (!in_array($clientSchoolId, $utilizerSchoolIds)) {
                        // El utilizador no tiene este school_id, así que lo creamos
                        ClientsSchool::create([
                            'client_id' => $utilizer->id,
                            'school_id' => $clientSchoolId,
                            'accepted_at' => Carbon::now()
                        ]);
                    }
                }
                foreach ($utilizerSchoolIds as $utilizerSchoolId) {
                    if (!in_array($utilizerSchoolId, $clientSchoolIds)) {
                        ClientsSchool::create([
                            'client_id' => $client->id,
                            'school_id' => $utilizerSchoolId,
                            'accepted_at' => Carbon::now()
                        ]);
                    }
                }
            }

        }

    }
});

Route::get('/refund', function () {

    $transactionID = '13023439';
    $bookingData = \App\Models\Booking::with('school')->find(2103);
    $schoolData = $bookingData->school;
    $tr = new TransactionRequest();
    $tr->setId($transactionID);
    $tr->setAmount(500 * 100);
    $tr->setCurrency($bookingData->currency);

    $payrexx = new Payrexx(
        'pruebas',
        'vgJrvQ7AYKzpiqmreocpeGYtjFTX39',
        '',
        env('PAYREXX_API_BASE_DOMAIN')
    );
    $response = $payrexx->refund($tr);

    // Status will be "refunded" if the amount was the whole Booking price,
    // or "partially refunded" if just some of its BookingUsers
    $responseStatus = $response->getStatus() ?? '';

    return ($responseStatus == TransactionResponse::REFUNDED ||
        $responseStatus == TransactionResponse::PARTIALLY_REFUNDED);
});

Route::any('/update-clients-schools', function () {

    $oldUserSports = UserSport::whereNotNull('school_id')->get();
    $oldDegrees = \App\Models\OldModels\Degree::all();

    foreach ($oldUserSports as $oldUserSport) {
        $client = Client::where('old_id', $oldUserSport->user_id)->first();
        $oldDegree = $oldDegrees->firstWhere('id', $oldUserSport->degree_id);
        $newDegree = Degree::where('degree_order', $oldDegree->degree_order)
            ->where('school_id', $oldUserSport->school_id)->where('sport_id', $oldUserSport->sport_id)
            ->first();

        if($client) {
            // Encuentra el registro correspondiente en ClientSport
            $clientSports = ClientSport::where('client_id', $client->id)
                ->where('sport_id', $oldUserSport->sport_id)
                ->where('degree_id', $newDegree->id)
                ->get();



            foreach ($clientSports as $clientSport) {
                // Actualiza el campo school_id en ClientSport con el valor de UserSport
                $clientSport->update(['school_id' => $oldUserSport->school_id ?? 1]);
            }
        }

    }

    return response()->json(['message' => 'School IDs updated successfully']);
});

Route::any('/mailtest/{bookingId}', function ($bookingId) {

    $bookingData = \App\Models\Booking::find($bookingId);
    $bookingData->loadMissing(['bookingUsers', 'bookingUsers.client', 'bookingUsers.degree', 'bookingUsers.monitor',
        'bookingUsers.courseExtras', 'bookingUsers.courseSubGroup', 'bookingUsers.course',
        'bookingUsers.courseDate']);
    $schoolData = \App\Models\School::find(1);
    $userData = Client::find($bookingData->client_main_id);
    //return response()->json($bookingData);
    // Apply that user's language - or default
    $defaultLocale = config('app.fallback_locale');
    $oldLocale = \App::getLocale();
    $userLang = Language::find($userData->language1_id);
    $userLocale = $userLang ? $userLang->code : $defaultLocale;
    \App::setLocale($userLocale);


    $templateView = 'mails.bookingCreate';

    $footerView = 'mails.footer';

    $templateMail = Mail::where('type', 'booking_confirm')->where('school_id', $schoolData->id)
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
        'reference' =>  $bookingData->id,
        'bookingNotes' => $bookingData->notes,
        'courses' => $bookingData->parseBookedGroupedCourses(),
        'voucherCode' => $voucherCode,
        'voucherAmount' => $voucherAmount,
        'hasCancellationInsurance' => false,
        'amount' => number_format($bookingData->price_total, 2),
        'currency' => $bookingData->currency,
        'actionURL' => 'test',
        'footerView' => $footerView
    ];

    //dd($templateData['courses']);


    $subject = __('emails.bookingInfo.subject');
    \App::setLocale($oldLocale);

    return view($templateView)->subject($subject)->with($templateData);
});

Route::any('/mailtest', function () {

    $user = User::find(8560);
    $defaultLocale = config('app.fallback_locale');
    $oldLocale = \App::getLocale();
    $userLang = Language::find($user->language1_id);
    $userLocale = $userLang ? $userLang->code : $defaultLocale;
    \App::setLocale($userLocale);

    $templateView = 'mails.recoverPassword'; // Load the view file directly
    $footerView = 'mails.footer'; // Load the view file directly

    $templateData = [
        'userName' => trim($user->first_name . ' ' . $user->last_name),
        'actionURL' => env('APP_RESETPASSWORD_URL') . '/' . $user->recover_token .'?user='. $user->id ,
        'footerView' => $footerView,

        // SCHOOL DATA - none
        'schoolName' => '',
        'schoolLogo' => '',
        'schoolEmail' => '',
        'schoolConditionsURL' => '',
    ];

    $subject = __('emails.recoverPassword.subject');
    \App::setLocale($oldLocale);

    return  view($templateView)->with($templateData);
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
