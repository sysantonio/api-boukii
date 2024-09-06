<?php

namespace App\Models;

use App\Mail\BookingCancelMailer;
use App\Mail\BookingInfoMailer;
use App\Models\OldModels\BookingUsers2;
use App\Models\OldModels\School;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Booking",
 *      required={"school_id","price_total","has_cancellation_insurance","price_cancellation_insurance","currency","paid_total","paid","attendance","payrexx_refund","notes","paxes"},
 *      @OA\Property(
 *          property="price_total",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="has_cancellation_insurance",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="price_cancellation_insurance",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="paid_total",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="paid",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="payrexx_reference",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="payrexx_transaction",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="attendance",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="payrexx_refund",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="notes",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *     @OA\Property(
 *           property="notes_school",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *           property="school_id",
 *           description="School ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="client_main_id",
 *           description="Main Client ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="user_id",
 *           description="User ID who performed the action",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="payment_method_id",
 *           description="Payment Method ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="paxes",
 *           description="Number of paxes",
 *           type="integer",
 *           nullable=false
 *       ),
 *      @OA\Property(
 *          property="color",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *     @OA\Property(
 *           property="source",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *           property="status",
 *           description="Status of the booking",
 *           type="integer",
 *           example=1
 *       ),
 *      @OA\Property(
 *           property="has_boukii_care",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="boolean",
 *       ),
 *     @OA\Property(
 *           property="price_boukii_care",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="number",
 *           format="number"
 *      ),
 *      @OA\Property(
 *            property="has_tva",
 *            description="",
 *            readOnly=false,
 *            nullable=false,
 *            type="boolean",
 *        ),
 *      @OA\Property(
 *            property="price_tva",
 *            description="",
 *            readOnly=false,
 *            nullable=false,
 *            type="number",
 *            format="number"
 *       ),
 *      @OA\Property(
 *            property="has_reduction",
 *            description="",
 *            readOnly=false,
 *            nullable=false,
 *            type="boolean",
 *        ),
 *      @OA\Property(
 *            property="price_reduction",
 *            description="",
 *            readOnly=false,
 *            nullable=false,
 *            type="number",
 *            format="number"
 *       ),
 *      @OA\Property(
 *           property="basket",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */

class Booking extends Model
{
     use LogsActivity, SoftDeletes, HasFactory;

     public $table = 'bookings';

    public $fillable = [
        'school_id',
        'client_main_id',
        'user_id',
        'price_total',
        'has_cancellation_insurance',
        'price_cancellation_insurance',
        'source',
        'currency',
        'payment_method_id',
        'paid_total',
        'paid',
        'payrexx_reference',
        'payrexx_transaction',
        'attendance',
        'payrexx_refund',
        'notes',
        'notes_school',
        'paxes',
        'status',
        'old_id',
        'has_boukii_care',
        'price_boukii_care',
        'has_tva',
        'price_tva',
        'has_reduction',
        'price_reduction',
        'color',
        'basket'
    ];

    protected $casts = [
        'price_total' => 'decimal:2',
        'has_cancellation_insurance' => 'boolean',
        'has_tva' => 'boolean',
        'has_reduction' => 'boolean',
        'price_cancellation_insurance' => 'decimal:2',
        'price_reduction' => 'decimal:2',
        'price_tva' => 'decimal:2',
        'source' => 'string',
        'currency' => 'string',
        'paid_total' => 'decimal:2',
        'price_boukii_care' => 'decimal:2',
        'paid' => 'boolean',
        'has_boukii_care' => 'boolean',
        'payrexx_reference' => 'string',
        'payrexx_transaction' => 'string',
        'attendance' => 'boolean',
        'payrexx_refund' => 'boolean',
        'notes' => 'string',
        'status' => 'integer',
        'notes_school' => 'string',
        'color' => 'string',
        'basket' => 'string'
    ];

    public static array $rules = [
        'school_id' => 'nullable',
        'client_main_id' => 'nullable',
        'user_id' => 'nullable',
        'price_total' => 'nullable|numeric',
        'has_cancellation_insurance' => 'nullable|boolean',
        'price_cancellation_insurance' => 'nullable|numeric',
        'currency' => 'nullable|string|max:3',
        'payment_method_id' => 'nullable',
        'paid_total' => 'nullable',
        'paid' => 'nullable',
        'payrexx_reference' => 'nullable|string|max:65535',
        'payrexx_transaction' => 'nullable|string|max:65535',
        'attendance' => 'nullable',
        'payrexx_refund' => 'nullable|boolean',
        'notes' => 'nullable|string|max:500',
        'notes_school' => 'nullable|string|max:500',
        'paxes' => 'nullable',
        'status' => 'nullable',
        'basket' => 'nullable',
        'color' => 'nullable|string|max:45',
        'source' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function clientMain(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_main_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function bookingLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingLog::class, 'booking_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'booking_id');
    }

    public function vouchersLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\VouchersLog::class, 'booking_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Payment::class, 'booking_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
    protected $appends = ['sport', 'bonus', 'payment_method'];

    public function getBonusAttribute() {
        return $this->vouchersLogs()->exists();
    }

    public function getPaymentMethodAttribute() {
        $hasVouchers = $this->vouchersLogs()->exists();
        $hasPayments = $this->payments()->exists();

        if ($hasVouchers && !$hasPayments) {
            return 6;
        }

        return $this->payment_method_id;
    }

    public function getSportAttribute()
    {
        // Obtener todos los bookingUsers asociados a este booking
        $bookingUsers = $this->bookingUsers()->with('course')->get();

        // Obtener los cursos únicos asociados a los bookingUsers
        $courses = $bookingUsers->pluck('course')->unique();

        if ($courses->isNotEmpty()) {
            // Verificar si todos los bookingUsers tienen el mismo course_type
            $courseType = $courses->first()->course_type ?? null;
            $sameCourseType = $courses->every(function ($course) use ($courseType) {
                if ($course && isset($course->course_type)) {
                    return $course->course_type === $courseType;
                }
                return false;
            });

            if ($sameCourseType && $courses->count() == 1) {
                // Si solo hay un curso y todos tienen el mismo course_type
                // devolver el deporte de ese curso
                if ($courseType == 1) {
                    return $courses->first()->sport->icon_collective;
                } elseif ($courseType == 2) {
                    return $courses->first()->sport->icon_prive;
                }
            } elseif ($sameCourseType && $courses->pluck('sport')->unique()->count() == 1) {
                // Si hay varios cursos pero todos tienen el mismo course_type y el mismo deporte
                // devolver ese deporte
                if ($courseType == 1) {
                    return $courses->first()->sport->icon_collective;
                } elseif ($courseType == 2) {
                    return $courses->first()->sport->icon_prive;
                }
            }
        }

        // Si hay varios cursos con diferentes course_type o deportes diferentes
        // devolver 'multiple'
        return 'multiple';
    }

    public function getPaymentStatus()
    {
        $status = '';
        $statusPayment = '';

        if ($this->status == 2) {
            $status = 'total_cancel';
            $statusPayment = $this->bookingLogs->last()->action;

        } else {
            $partialCancellation = $this->bookingUsers()->where('status', 2)->exists();
            $status = $partialCancellation ? 'partial_cancel' : 'active';

        }



        return $status;
    }

    /**
     * Generate an unique reference for Payrexx - only for bookings that wanna pay this way
     * (i.e. BoukiiPay or Online)
     */
    public function getOrGeneratePayrexxReference()
    {
        if (!$this->payrexx_reference &&
            ($this->payment_method_id == 2 || $this->payment_method_id == 3))
        {
            $ref = 'Boukii #' . $this->id;
            $this->payrexx_reference = (env('APP_ENV') == 'production') ? $ref : 'TEST ' . $ref;
            $this->save();
        }

        return $this->payrexx_reference;
    }

    // Special for field "payrexx_transaction": store encrypted
    public function setPayrexxTransaction($value)
    {
        $this->payrexx_transaction = encrypt( json_encode($value) );
    }

    public function getPayrexxTransaction()
    {
        $decrypted = null;
        if ($this->payrexx_transaction)
        {
            try
            {
                $decrypted = decrypt($this->payrexx_transaction);
            }
                // @codeCoverageIgnoreStart
            catch (\Illuminate\Contracts\Encryption\DecryptException $e)
            {
                $decrypted = null;  // Data seems corrupt or tampered
            }
            // @codeCoverageIgnoreEnd
        }

        return $decrypted ? json_decode($decrypted, true) : [];
    }

    public function parseBookedGroupedCourses()
    {
        $this->loadMissing(['bookingUsers', 'bookingUsers.client', 'bookingUsers.degree', 'bookingUsers.monitor',
            'bookingUsers.courseExtras', 'bookingUsers.courseSubGroup', 'bookingUsers.course',
            'bookingUsers.courseDate']);

        $bookingUsers = $this->bookingUsers;

        $groupedCourses = $bookingUsers->groupBy(['course.course_type', 'client_id',
            'course_id', 'degree_id', 'course_date_id']);

        return $groupedCourses;
    }

    public static function bookingInfo24h()
    {
        /*
            We send reservation information 24 hours before the course starts
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron bookingInfo24h');

        $bookings = self::with('clientMain')->where('paid', 1)
            ->get();

        foreach($bookings as $booking)
        {
            $closestBookingUser = null;
            $closestTimeDiff = null;
            $currentDateTime = Carbon::now();

            // the booking can have different courses/classes
            $lines = BookingUser::with('course')->where('booking_id', $booking->id)->get();

            foreach ($lines as $line) {
                // Calcular la fecha y hora de inicio de este booking user
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $line->date . ' ' . $line->hour);

                // Calcular la diferencia en horas entre la fecha actual y la fecha del booking
                $timeDiff = $currentDateTime->diffInHours($bookingDateTime);

                // Comprobar si este booking user es el más cercano hasta ahora
                if ($closestBookingUser === null || $timeDiff < $closestTimeDiff) {
                    $closestBookingUser = $line;
                    $closestTimeDiff = $timeDiff;
                }
            }

            // Verificar si el booking user más cercano cumple con la condición de las 24 horas
            if ($closestBookingUser !== null && $closestTimeDiff < 24) {
                $bookingType = $closestBookingUser->course->type == 2 ? 'Privado' : 'Colectivo';
                \Illuminate\Support\Facades\Log::debug('bookingInfo24h: '
                    . $bookingType . ' ID ' . $booking->id
                    . ' - Enviamos info de la reserva: '
                    . $closestBookingUser->id . " : Fecha de inicio "
                    . $closestBookingUser->date . ' '
                    . $closestBookingUser->hour . ' - Dif in hours: '
                    . $closestTimeDiff);


                // Envía el email aquí
                $mySchool = School::find($closestBookingUser->booking->school_id);
                dispatch(function () use ($mySchool, $closestBookingUser) {
                    // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                    try {
                        \Mail::to($closestBookingUser->booking->clientMain->email)
                            ->send(new BookingInfoMailer(
                                $mySchool,
                                $closestBookingUser->booking,
                                $closestBookingUser->booking->clientMain->email
                            ));
                    } catch (\Exception $ex) {
                        \Illuminate\Support\Facades\Log::debug('Cron bookingInfo24h: ', $ex->getTrace());
                    }
                })->afterResponse();
            }
        }

        \Illuminate\Support\Facades\Log::debug('Fin cron bookingInfo24h');
    }

    public static function sendPaymentNotice()
    {
        /*
            We send reservation information 24 hours before the course starts
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron sendPaymentNotice');

        $bookings = self::with('clientMain')->where('payment_method_id', 3)
            ->where('paid', 0)
            ->get();

        foreach($bookings as $booking)
        {
            $closestBookingUser = null;
            $closestTimeDiff = null;
            $currentDateTime = Carbon::now();

            // the booking can have different courses/classes
            $lines = BookingUser::with('course')->where('booking_id', $booking->id)->get();

            foreach ($lines as $line) {
                // Calcular la fecha y hora de inicio de este booking user
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $line->date . ' ' . $line->hour);

                // Calcular la diferencia en horas entre la fecha actual y la fecha del booking
                $timeDiff = $currentDateTime->diffInHours($bookingDateTime);

                // Comprobar si este booking user es el más cercano hasta ahora
                if ($closestBookingUser === null || $timeDiff < $closestTimeDiff) {
                    $closestBookingUser = $line;
                    $closestTimeDiff = $timeDiff;
                }
            }

            // Verificar si el booking user más cercano cumple con la condición de las 24 horas
            if ($closestBookingUser !== null && $closestTimeDiff > 48 && $closestTimeDiff < 72
                && BookingPaymentNoticeLog::checkToNotify($closestBookingUser)) {

                \Illuminate\Support\Facades\Log::debug('sendPaymentNotice: ID '.
                    $booking->id.' - Enviamos aviso para '. $closestBookingUser->id." : Fecha de inicio ".$currentDateTime);



                // Envía el email aquí
                $mySchool = School::find($closestBookingUser->booking->school_id);
                dispatch(function () use ($mySchool, $closestBookingUser) {
                    // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                    try {
                        \Mail::to($closestBookingUser->booking->clientMain->email)
                            ->send(new BookingInfoMailer(
                                $mySchool,
                                $closestBookingUser->booking,
                                $closestBookingUser->booking->clientMain->email
                            ));

                        BookingPaymentNoticeLog::create([
                            'booking_id' => $closestBookingUser->booking->id,
                            'booking_user_id' => $closestBookingUser->id,
                            'date' => date('Y-m-d H:i:s')
                        ]);
                    } catch (\Exception $ex) {
                        \Illuminate\Support\Facades\Log::debug('Cron bookingInfo15min: ', $ex->getTrace());
                    }
                })->afterResponse();
            }
        }

        \Illuminate\Support\Facades\Log::debug('Fin cron sendPaymentNotice');
    }

    public static function cancelUnpaids15m()
    {
        /*
            We send reservation information 24 hours before the course starts
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron cancelUnpaids15m');

        $bookings = self::with('clientMain')->where('status', 3)
            ->get();

        foreach($bookings as $booking)
        {
            {
                $fecha_actual = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));
                $fecha_creacion = Carbon::createFromFormat('Y-m-d H:i:s', $booking->created_at);

                if( $fecha_actual < $fecha_creacion->addMinutes(15) ) {
                    continue;
                }

                $tipo = 'completa';
                self::cancelBookingFull($booking->id);

                \Illuminate\Support\Facades\Log::debug('cancelUnpaids15m: ID '.$booking->id.' - Eliminamos reserva '.$tipo.'. '." Fecha de creación ".$fecha_creacion.' - Diferencia de tiempo: '.$fecha_actual->diffInMinutes($fecha_creacion));
            }

        }

        \Illuminate\Support\Facades\Log::debug('Fin cron cancelUnpaids15m');
    }

    public static function cancelBookingFull($bookingID)
    {
        $bookingData = self::with('clientMain')->where('id', '=', intval($bookingID))->first();
        if(isset($bookingData->id)) {
            $cancelledLines = $bookingData->parseBookedGroupedCourses();
            BookingUser::where('booking_id', $bookingData->id)->update(['status' => 2]);

            //TODO: que pasa si hacen una reserva con cancellation insurance y no la pagan.
        /*    if ($bookingData->has_cancellation_insurance)
            {
                $bookingData->price_total = $bookingData->price_cancellation_insurance;
                $bookingData->save();
            }*/

            $bookingData->status = 3;

            $mySchool = School::find($bookingData->school_id);
            $voucherData = array();

            dispatch(function () use ($mySchool, $bookingData, $cancelledLines, $voucherData) {
                $buyerUser = $bookingData->clientMain;

                // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                try
                {
                    \Mail::to($bookingData->email)
                        ->send(new BookingCancelMailer(
                            $mySchool,
                            $bookingData,
                            $cancelledLines,
                            $buyerUser,
                            $voucherData
                        ));
                }
                catch (\Exception $ex)
                {
                    \Illuminate\Support\Facades\Log::debug('BookingController->cancelBookingFull BookingCancelMailer: ' . $ex->getMessage());
                }
            })->afterResponse();
        }
    }

}
