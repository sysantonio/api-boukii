<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\PayrexxHelpers;
use App\Http\Resources\API\BookingResource;
use App\Mail\BookingPayMailer;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\BookingUser;
use App\Models\BookingUserExtra;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseExtra;
use App\Models\CourseSubgroup;
use App\Models\MonitorNwd;
use App\Models\MonitorSportsDegree;
use App\Models\Voucher;
use App\Models\VouchersLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class BookingController extends SlugAuthController
{

    /**
     * @OA\Post(
     *      path="/slug/bookings",
     *      summary="createCourse",
     *      tags={"BookingPage"},
     *      description="Create Course",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Course")
     *      ),
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
     *                  ref="#/components/schemas/Course"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */

    public function store(Request $request)
    {

        //TODO: Check OVERLAP
        $data = $request->all();
        // Crear la reserva (Booking)
        $booking = Booking::create([
            'school_id' => $data['school_id'],
            'client_main_id' => $data['client_main_id'],
            'price_total' => $data['price_total'],
            'has_cancellation_insurance' => $data['has_cancellation_insurance'],
            'price_cancellation_insurance' => $data['price_cancellation_insurance'],
            'status' => 1,
            'currency' => 'CHF',

            // ... otros campos
        ]);

        // Crear BookingUser para cada detalle
        foreach ($data['cart'] as $cartItem) {
            foreach ($cartItem['details'] as $detail) {
                $bookingUser = new BookingUser([
                    'school_id' => $detail['school_id'],
                    'booking_id' => $booking->id,
                    'client_id' => $detail['client_id'],
                    'price' => $detail['price'],
                    'currency' => $detail['currency'],
                    'course_id' => $detail['course_id'],
                    'course_date_id' => $detail['course_date_id'],
                    'course_group_id' => $detail['course_group_id'],
                    'course_subgroup_id' => $detail['course_subgroup_id'],
                    'date' => $detail['date'],
                    'hour_start' => $detail['hour_start'],
                    'hour_end' => $detail['hour_end'],
                    // Puedes añadir campos adicionales según necesites
                ]);

                $bookingUser->save();

                if(isset($detail['extra'])){
                    $tva = isset($detail['extra']['tva']) ? $detail['extra']['tva'] : 0;
                    $price = isset($detail['extra']['price']) ? $detail['extra']['price'] : 0;

                    // Calcular el precio con el TVA
                    $priceWithTva = $price + ($price * ($tva / 100));

                    $courseExtra = new CourseExtra([
                        'course_id' => $detail['course_id'],
                        'name' => $detail['extra']['id'],
                        'description' => $detail['extra']['id'],
                        'price' => $priceWithTva
                    ]);

                    $courseExtra->save();

                    BookingUserExtra::create([
                        'booking_user_id' => $bookingUser->id,
                        'course_extra_id' => $courseExtra->id
                    ]);
                }
            }
        }

        // Actualizar VouchersLog y el cupón si es necesario
        if ($data['voucherAmount'] > 0) {
            // Suponiendo que 'voucher_id' es parte de tu request
            $voucher = Voucher::find($request->voucher['id']);
            $voucher->remaining_balance -= $data['voucherAmount'];
            $voucher->save();


            VouchersLog::create([
                'voucher_id' => $voucher->id,
                'booking_id' => $booking->id,
                'amount' => -$data['voucherAmount'],
            ]);
        }

        $client = Client::find($data['client_main_id'])->load('user');
        BookingLog::create([
            'booking_id' => $booking->id,
            'action' => 'Booking created from booking page',
            'user_id' => $client->user->id
        ]);

        return response()->json(['message' => 'Reserva creada con éxito', 'booking_id' => $booking->id], 201);

    }

    /**
     * @OA\Post(
     *      path="/slug/bookings/checkbooking",
     *      summary="checkOverlapBooking",
     *      tags={"BookingPage"},
     *      description="Check overlap booking for a client",
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
     *                  @OA\Items(ref="#/components/schemas/Client")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function checkClientBookingOverlap(Request $request): JsonResponse
    {
        $clientIds = [];
        $highestDegreeId = 0;
        $date = null;
        $startTime = null;
        $endTime = null;

        // Obtiene información común para todos los bookingUsers
        foreach ($request->bookingUsers as $bookingUser) {
            if($bookingUser['course']['course_type'] == 2) {
                $clientIds[] = $bookingUser['client']['id'];

                // Obtener el degree_id más alto solo una vez
                if ($highestDegreeId === 0) {
                    $sportId = $bookingUser['course']['sport_id'];
                    $clientDegrees = $bookingUser['client']['sports'];

                    foreach ($clientDegrees as $clientDegree) {
                        if ($clientDegree['pivot']['sport_id'] == $sportId && $clientDegree['pivot']['degree_id'] > $highestDegreeId) {
                            $highestDegreeId = $clientDegree['pivot']['degree_id'];
                        }
                    }
                }

                // Obtener la fecha, hora de inicio y hora de fin solo una vez
                if ($date === null) {
                    $date = $bookingUser['date'];
                    $startTime = $bookingUser['hour_start'];
                    $endTime = $bookingUser['hour_end'];
                }
            }


            if (BookingUser::hasOverlappingBookings($bookingUser)) {
                return $this->sendError('Client has booking on that date');
            }
        }

        if($request->bookingUsers[0]['course']['course_type'] == 2) {
            $monitorAvailabilityRequest = new Request([
                'date' => $date,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'minimumDegreeId' => $highestDegreeId,
                'sportId' => $bookingUser['course']['sport_id'],
                'clientIds' => $clientIds
            ]);
            if(!$this->getMonitorsAvailable($monitorAvailabilityRequest)) {
                return $this->sendError('No monitor available on that date');
            }
        }

        return $this->sendResponse([], 'Client has not overlaps bookings');
    }

    public function getMonitorsAvailable(Request $request): bool
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
                $query->whereTime('hour_start', '<=', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>=', Carbon::createFromFormat('H:i', $request->startTime))->where('status', 1);
            })
            ->pluck('monitor_id')
            ->merge(MonitorNwd::whereDate('start_date', '<=', $request->date)
                ->whereDate('end_date', '>=', $request->date)
                ->where(function ($query) use ($request) {
                    // Aquí incluimos la lógica para verificar si es un día entero
                    $query->where('full_day', true)
                        ->orWhere(function ($timeQuery) use ($request) {
                            $timeQuery->whereTime('start_time', '<=',
                                Carbon::createFromFormat('H:i', $request->endTime))
                                ->whereTime('end_time', '>=', Carbon::createFromFormat('H:i', $request->startTime));
                        });
                })
                ->pluck('monitor_id'))
            ->merge(CourseSubgroup::whereHas('courseDate', function ($query) use ($request) {
                $query->whereDate('date', $request->date)
                    ->whereTime('hour_start', '<=', Carbon::createFromFormat('H:i', $request->endTime))
                    ->whereTime('hour_end', '>=', Carbon::createFromFormat('H:i', $request->startTime));
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
        return empty($availableMonitors);

    }


    /**
     * @OA\Post(
     *      path="/slug/bookings/payments/{id}",
     *      summary="payBooking",
     *      tags={"BookingPage"},
     *      description="Pay specific booking",
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
     *                  @OA\Items(ref="#/components/schemas/Client")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function payBooking(Request $request, $id): JsonResponse
    {
        $school = $this->school;
        $booking = Booking::find($id);
        $paymentMethod = 2;

        if (!$booking) {
            return $this->sendError('Booking not found');
        }

        $booking->payment_method_id = $paymentMethod;
        $booking->save();

        $payrexxLink = PayrexxHelpers::createGatewayLink(
            $school,
            $booking,
            $request,
            $booking->clientMain,
            $request->redirectUrl
        );

        if ($payrexxLink) {
            return $this->sendResponse($payrexxLink, 'Link retrieved successfully');
        }

        return $this->sendError('Link could not be created');
    }

}
