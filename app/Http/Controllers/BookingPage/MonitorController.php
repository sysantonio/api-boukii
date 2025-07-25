<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\BookingUserResource;
use App\Http\Resources\Teach\HomeAgendaResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\CourseSubgroup;
use App\Models\Monitor;
use App\Models\MonitorNwd;
use App\Models\MonitorObservation;
use App\Models\MonitorSportsDegree;
use App\Models\MonitorsSchool;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Response;
use Validator;

;

/**
 * Class HomeController
 * @package App\Http\Controllers\Teach
 */
class MonitorController extends SlugAuthController
{

    /**
     * @OA\Post(
     *      path="/slug/monitor/available",
     *      summary="getMonitorsAvailable",
     *      tags={"BookingPage"},
     *      description="Get monitors available",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Monitor")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getMonitorsAvailable(Request $request): JsonResponse
    {
        $school = $this->school;

        $isAnyAdultClient = false;
        $clientLanguages = [];

        if ($request->has('clientIds') && is_array($request->clientIds)) {
            foreach ($request->clientIds as $clientId) {
                $client = Client::find($clientId);
                if ($client) {
                    $clientAge = Carbon::parse($client->birth_date)->age;
                    if ($clientAge >= 18) {
                        $isAnyAdultClient = true;
                    }

                    // Agregar idiomas del cliente al array de idiomas
                    for ($i = 1; $i <= 6; $i++) {
                        $languageField = 'language' . $i . '_id';
                        if (!empty($client->$languageField)) {
                            $clientLanguages[] = $client->$languageField;
                        }
                    }
                }
            }
        }

        $clientLanguages = array_unique($clientLanguages);
        // Paso 1: Obtener todos los monitores que tengan el deporte y grado requerido.
        $eligibleMonitors =
            MonitorSportsDegree::whereHas('monitorSportAuthorizedDegrees', function ($query) use ($school, $request) {
                $query->where('school_id', $school->id)
                    ->where('degree_id', '>=', $request->minimumDegreeId);
            })
                ->where('sport_id', $request->sportId)
                // Comprobación adicional para allow_adults si hay algún cliente adulto
                ->when($isAnyAdultClient, function ($query) {
                    return $query->where('allow_adults', true);
                })
                ->with(['monitor' => function ($query) use ($school, $clientLanguages) {
                    $query->whereHas('monitorsSchools', function ($subQuery) use ($school) {
                        $subQuery->where('school_id', $school->id)->where('active_school', 1);
                    });
                    // Añadir filtro de idiomas si clientIds está presente
                    if (!empty($clientLanguages)) {
                        $query->where(function ($query) use ($clientLanguages) {
                            $query->orWhereIn('language1_id', $clientLanguages)
                                ->orWhereIn('language2_id', $clientLanguages)
                                ->orWhereIn('language3_id', $clientLanguages)
                                ->orWhereIn('language4_id', $clientLanguages)
                                ->orWhereIn('language5_id', $clientLanguages)
                                ->orWhereIn('language6_id', $clientLanguages);

                        });
                    }
                }])
                ->get()
                ->pluck('monitor');

        $busyMonitors = BookingUser::whereDate('date', $request->date)
            ->where(function ($query) use ($request) {
                $query->whereTime('hour_start', '<', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>', Carbon::createFromFormat('H:i', $request->startTime)
                    ->where('status', 1));
            })->whereHas('booking', function ($query) {
                $query->where('status', '!=', 2); // La Booking no debe tener status 2
            })
            ->pluck('monitor_id')
            ->merge(MonitorNwd::whereDate('start_date', '<=', $request->date)
                ->whereDate('end_date', '>=', $request->date)
                ->where(function ($query) use ($request) {
                    // Aquí incluimos la lógica para verificar si es un día entero
                    $query->where('full_day', true)
                        ->orWhere(function ($timeQuery) use ($request) {
                            $timeQuery->whereTime('start_time', '<',
                                Carbon::createFromFormat('H:i', $request->endTime))
                                ->whereTime('end_time', '>', Carbon::createFromFormat('H:i', $request->startTime));
                        });
                })
                ->pluck('monitor_id'))
            ->merge(CourseSubgroup::whereHas('courseDate', function ($query) use ($request) {
                $query->whereDate('date', $request->date)
                    ->whereTime('hour_start', '<', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>', Carbon::createFromFormat('H:i', $request->startTime));
            })
                ->pluck('monitor_id'))
            ->unique();


        // Paso 3: Filtrar los monitores elegibles excluyendo los ocupados.
        $availableMonitors = $eligibleMonitors->whereNotIn('id', $busyMonitors);

        // Eliminar los elementos nulos
        $availableMonitors = array_filter($availableMonitors->toArray());

        // Reindexar el array para eliminar las claves
        $availableMonitors = array_values($availableMonitors);

        // Paso 4: Devolver los monitores disponibles.
        return $this->sendResponse($availableMonitors, 'Monitors returned successfully');

    }

    /**
     * @OA\Post(
     *      path="/slug/monitor/available/{id}",
     *      summary="checkIfMonitorIsAvailable",
     *      tags={"BookingPage"},
     *      description="Get monitor availability",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Monitor")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function checkIfMonitorIsAvailable(Request $request, $id): JsonResponse
    {
        try {
            // Validar los parámetros
            $validatedData = $request->validate([
                'date' => 'required|date',
                'hour_start' => 'required|date_format:H:i:s',
                'hour_end' => 'required|date_format:H:i:s',
            ]);

            // Comprobar si el monitor está ocupado
            $isBusy = Monitor::isMonitorBusy($id, $validatedData['date'],
                $validatedData['hour_start'], $validatedData['hour_end']);

            return $this->sendResponse(['available' => !$isBusy], 'Bookings returned successfully');

        } catch (ValidationException $e) {
            // Manejar errores de validación
             return $this->sendError($e->getMessage(), 400);
        }


    }

}
