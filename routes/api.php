<?php

use App\Models\Client;
use App\Models\Course;
use App\Models\Monitor;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
                $imageName = 'image_' .time().  '.' . $type[1];
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
Route::prefix('bookingPage')
    ->group(base_path('routes/api/bookingPage.php'));
/* API IFRAME */
