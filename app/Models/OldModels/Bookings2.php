<?php

namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OldModels\BookingUsers2;
use App\Models\OldModels\User;
use App\Models\OldModels\School;
use App\Models\OldModels\BookingPaymentNoticeLog;

use App\Http\PayrexxHelpers;
use App\Mail\BookingNoticePayMailer;
use App\Mail\BookingCancelMailer;
use App\Mail\BookingInfoMailer;

/**
 * Class Bookings2
 *
 * @property int $id
 * @property int $school_id
 * @property int $user_main_id
 * @property float $price_total
 * @property bool $has_cancellation_insurance
 * @property float $price_cancellation_insurance
 * @property string $currency
 * @property int|null $payment_method_id
 * @property bool $paid
 * @property string|null $payrexx_reference
 * @property string|null $payrexx_transaction
 * @property bool $attendance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @property PaymentMethod|null $payment_method
 * @property User $main_user
 * @property School $school
 * @property Collection|BookingUsers2[] $booking_users
 * @property Collection|BookingUsers2[] $booking_users_with_trashed
 *
 * @package App\Models
 */
class Bookings2 extends Model
{
    use SoftDeletes;
    protected $table = 'bookings2';

    protected $casts = [
        'school_id' => 'int',
        'user_main_id' => 'int',
        'price_total' => 'float',
        'has_cancellation_insurance' => 'bool',
        'price_cancellation_insurance' => 'float',
        'payment_method_id' => 'int',
        'paxes' => 'int',
        'paid' => 'bool',
        'attendance' => 'bool'
    ];

    protected $connection = 'old';

protected $fillable = [
        'school_id',
        'user_main_id',
        'price_total',
        'currency',
        'payment_method_id',
        'paid',
        'payrexx_reference',
        'payrexx_transaction',
        'notes',
        'attendance',
        'is_past',
        'color',
        'paxes',
        'created_at',
        'updated_at'
    ];



    /**
     * Relations
     */

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function main_user()
    {
        return $this->belongsTo(User::class, 'user_main_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function booking_users()
    {
        return $this->hasMany(BookingUsers2::class, 'booking2_id')->withTrashed();
    }

    public function booking_users_with_trashed()
    {
        return $this->hasMany(BookingUsers2::class, 'booking2_id')->withTrashed();
    }

    /**
     * Scopes
     */

    public function scopeSearch($query, $search){

        if($search) {
            $query->where(function($q) use($search) {
                $q->orWhere(function($q) use($search) {
                    $q->whereHas('booking_users_with_trashed', function($q) use( $search) {
                            $q->whereHas('subgroup', function ($q) use ($search) {
                                $q->whereHas('group', function ($q) use ($search) {
                                    $q->whereHas('course', function ($q) use ($search) {
                                        $q->whereHas('sport', function($q) use( $search) {
                                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                                        });
                                        $q->orWhereHas('course_type', function ($q) use ($search) {
                                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                                        });
                                        $q->orWhereHas('course_supertype', function ($q) use ($search) {
                                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                                        });
                                        $q->orWhereHas('course_dates', function ($q) use ($search) {
                                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                                        });
                                    });
                                });
                            });
                        });
                });
                $q->orWhere(function($q) use($search) {
                    $q->whereHas('booking_users_with_trashed.course', function($q) use( $search) {
                        $q->whereHas('sport', function($q) use( $search) {
                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                        });
                        $q->orWhereHas('course_type', function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                        });
                        $q->orWhereHas('course_supertype', function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                        });
                        $q->orWhereHas('course_dates', function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', [strtolower("%" . $search . "%")]);
                        });
                    });
                });
              $q->orWhere(function($q) use($search) {
                    $q->whereHas('main_user', function($q) use( $search) {
                        $q->where('first_name', 'like', "%" . $search . "%")
                            ->orWhere('last_name', 'like', "%" . $search . "%");
                    });
                });
                $q->orWhere('id', 'like', "%" . $search . "%");
                $q->orWhereRaw("CONVERT(created_at, CHAR) LIKE '%".$search."%'");
            });
        }

    }

    public function scopeIsMultiple($query, $is_multiple)
    {
        if ($is_multiple === 'multiple') {
            return $query->whereHas('booking_users_with_trashed', function ($subQuery) {
                $subQuery->select('booking2_id')->groupBy('booking2_id')
                    ->havingRaw('COUNT(DISTINCT course2_id) > 1 OR COUNT(DISTINCT CONCAT(course2_id, date, hour)) > 1');
            });
        } elseif ($is_multiple === 'single') {
            return $query->whereHas('booking_users_with_trashed', function ($subQuery) {
                $subQuery->select('booking2_id')->groupBy('booking2_id')
                    ->havingRaw('COUNT(DISTINCT course2_id) = 1 AND COUNT(DISTINCT CONCAT(course2_id, date, hour)) = 1');
            });
        } else {
            return $query;
        }
    }

    public function scopeCourseType($query, $course_type)
    {
        if (is_null($course_type)) {
            return $query;
        } elseif ($course_type === 'collective') {
            return $query->whereHas('booking_users_with_trashed', function ($subquery) {
                $subquery->whereNotNull('course_groups_subgroup2_id');
            });
        } elseif ($course_type === 'private') {
            return $query->whereDoesntHave('booking_users_with_trashed', function ($subquery) {
                $subquery->whereNotNull('course_groups_subgroup2_id');
            });
        }
    }

    public function scopePaid($query, $paid)
    {
        if (is_null($paid)) {
            return $query;
        } else {
            return $query->where('paid', $paid);
        }
    }

    /**
     * Admin Restrictions
     */

    public static function canCreate()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_BOOKING)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_CREATE, $restrictions));
        }

        return true;
    }

    public static function canView()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_BOOKING)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_SHOW, $restrictions));
        }

        return true;
    }

    public static function canEdit()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_BOOKING)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_EDIT, $restrictions));
        }

        return true;
    }

    public static function canDelete()
    {
        $myUser = \Auth::user();
        $restrictions = $myUser->restrictions()->where('type_id', AdminRestrictionType::ID_BOOKING)->pluck('action_id')->toArray();
        if ($myUser && $restrictions)
        {
            return (!in_array(AdminRestrictionAction::ID_DELETE, $restrictions));
        }

        return true;
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


    /**
     * Generate an unique reference for Payrexx - only for bookings that wanna pay this way
     * (i.e. BoukiiPay or Online)
     */
    public function getOrGeneratePayrexxReference()
    {
        if (!$this->payrexx_reference &&
            ($this->payment_method_id == PaymentMethod::ID_BOUKIIPAY || $this->payment_method_id == PaymentMethod::ID_ONLINE))
        {
            $ref = 'Boukii #' . $this->id;
            $this->payrexx_reference = (env('APP_ENV') == 'production') ? $ref : 'TEST ' . $ref;
            $this->save();
        }

        return $this->payrexx_reference;
    }



    /**
     * Get an array representation of all Courses linked to this Booking:
     * for each one, its "name", "unit_price" and "users" booked
     * Ex.
     * [
     *      ['name' => 'One course', 'unit_price' => 50, 'users' => ['Jane', 'John', 'Jack']],
     *      ['name' => 'Other course', 'unit_price' => 99.99, 'users' => ['Mary']],
     *      ...
     * ]
     *
     */
    public function parseBookedCourses()
    {
        $this->loadMissing(['booking_users', 'booking_users.user', 'booking_users.subgroup', 'booking_users.course']);

        $list1 = [];
        $list2 = [];
        $groupedCourses = [];

        foreach ($this->booking_users as $bu)
        {

            // a. If booked a "definite" course, has a SubgroupID
            if ($bu->subgroup)
            {
                if (!isset($list1[$bu->subgroup->id]))
                {
                    $groupKey = $bu->getRelatedGroupId() . '-' . $bu->user->id;

                    if (!isset($groupedCourses[$groupKey])) {
                        $groupedCourses[$groupKey] = [
                            'name' => $bu->getRelatedCourseTitle(),
                            'unit_price' => $bu->getRelatedPrice($days = 0),
                            'days' => 1,
                            'users' => [$bu->user->first_name . ' ' . $bu->user->last_name],
                            'dates' => $bu->getRelatedBookingCourseDates(),
                            'monitor' => $bu->getRelatedMonitorName(),
                            'group_id' => $bu->getRelatedGroupId(),
                        ];
                    } else {

                        $groupedCourses[$groupKey]['days'] += 1;
                        $groupedCourses[$groupKey]['dates'] = array_merge($groupedCourses[$groupKey]['dates'], $bu->getRelatedBookingCourseDates());
                        $groupedCourses[$groupKey]['unit_price'] =
                            $bu->getRelatedPrice($groupedCourses[$groupKey]['days']);
                    }
                }
                else
                {
                    $list1[$bu->subgroup->id]['users'][] = $bu->user->first_name . ' ' . $bu->user->last_name;
                }
            }

            // b. If booked a "loose" course, has a courseID
            else if ($bu->course)
            {

                $courseDates = $bu->getRelatedCourseDates();
                $index = sha1($bu->course->id . ' ' . implode(" ", $courseDates));

                // Si no existe una entrada para el curso y fecha en la lista, agregar una nueva
                if (!isset($list2[$index]))
                {
                    $list2[$index] = [
                        'name' => $bu->getRelatedCourseTitle(),
                        'unit_price' => $bu->price,
                        'users' => [$bu->user->first_name . ' ' . $bu->user->last_name],
                        'dates' => $courseDates,
                        'monitor' => $bu->getRelatedMonitorName()
                    ];
                }
                else
                {
                    $list2[$index]['users'][] = $bu->user->first_name . ' ' . $bu->user->last_name;
                }
            }
        }

        return array_merge($groupedCourses, $list2);
    }

    public function parseBookedGroupedCourses()
    {
        $this->loadMissing(['booking_users', 'booking_users.user', 'booking_users.subgroup', 'booking_users.course']);

        $list1 = [];
        $list2 = [];

        foreach ($this->booking_users as $bu)
        {
            // a. If booked a "definite" course, has a SubgroupID
            if ($bu->subgroup)
            {
                if (!isset($list1[$bu->subgroup->id]))
                {
                    $list1[$bu->subgroup->group->course->group_id . '-' . $bu->subgroup->group->degree_id . '-' . $bu->user->id] = [
                        'name' => $bu->getRelatedCourseTitle(),
                        'unit_price' => $bu->price,
                        'users' => [0 => $bu->user->first_name . ' ' . $bu->user->last_name],
                        'dates' => $bu->getRelatedCourseDates(),
                        'monitor' => $bu->getRelatedMonitorName(),
                        'type' => 'collective'
                    ];

                }
                else
                {
                    $list1[$bu->subgroup->id]['users'][] = $bu->user->first_name . ' ' . $bu->user->last_name;
                }
            }

            // b. If booked a "loose" course, has a courseID
            else if ($bu->course)
            {

                $courseDates = $bu->getRelatedCourseDates();
                $index = sha1($bu->course->id . ' ' . implode(" ", $courseDates));

                // Si no existe una entrada para el curso y fecha en la lista, agregar una nueva
                if (!isset($list2[$index]))
                {
                    $list2[$index] = [
                        'name' => $bu->getRelatedCourseTitle(),
                        'unit_price' => $bu->price,
                        'users' => [$bu->user->first_name . ' ' . $bu->user->last_name],
                        'dates' => $courseDates,
                        'monitor' => $bu->getRelatedMonitorName(),
                        'type' => 'private'
                    ];
                }
                else
                {
                    $list2[$index]['users'][] = $bu->user->first_name . ' ' . $bu->user->last_name;
                }
            }
        }

        return ['collective' => $list1, 'private'=>$list2];
    }

    public static function sendPaymentNotice()
    {
        /*
            We send a payment notice 72 hours before the start of the course
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron sendPaymentNotice');

        $bookings = self::where('payment_method_id', 3)
            ->where('paid', 0)
            ->get();

        foreach($bookings as $booking)
        {
            // the booking can have different courses/classes
            $lines = BookingUsers2::where('booking2_id', $booking->id)->get();

            $fecha_actual = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));

            foreach($lines as $line)
            {

                if(!empty($line->course2_id)) {
                    // course privé
                    $fecha_inicio = date("Y-m-d", strtotime($line->date)).' '.date("H:i:s", strtotime($line->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);
                        if($diff_in_hours > 48 && $diff_in_hours < 72) {

                            if(BookingPaymentNoticeLog::checkToNotify($line)) {

                                $schoolData = School::find($booking->school_id);
                                if(!isset($schoolData->id)) continue;
                                $bookingData = $booking;
                                $buyerUser = User::find($booking->user_main_id);
                                if(!isset($buyerUser->id)) continue;

                                // Create pay link
                                $link = PayrexxHelpers::createPayLink($schoolData, $bookingData, $buyerUser);
                                if (strlen($link) < 1)
                                {
                                    throw new \Exception('Cant create Payrexx Direct Link for School ID=' . $schoolData->id);
                                }else{

                                    \Mail::to($buyerUser->email)
                                        ->send(new BookingNoticePayMailer(
                                            $schoolData,
                                            $bookingData,
                                            $buyerUser,
                                            $link
                                        ));


                                    BookingPaymentNoticeLog::create([
                                        'booking2_id' => $booking->id,
                                        'booking_user2_id' => $line->id,
                                        'date' => date('Y-m-d H:i:s')
                                    ]);

                                    \Illuminate\Support\Facades\Log::debug('sendPaymentNotice: ID '.$booking->id.' - Enviamos aviso para '. $line->id." : Fecha de inicio ".$fecha_reserva);
                                }
                            }
                        }
                    }

                }elseif(!empty($line->course_groups_subgroup2_id)){
                    /*
                        course collectif
                        we need the first date of the course
                    */
                    $subgroup = CourseGroupsSubgroups2::where('id', $line->course_groups_subgroup2_id)->first();
                    if(!isset($subgroup->id)) continue;
                    $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                    if(!isset($group->id)) continue;
                    $courseDate = CourseDates2::select("id","date","hour")->where('course2_id', $group->course2_id)
                        ->orderBy('date', 'asc')
                        ->orderBy('hour', 'asc')
                        ->first();
                    if(!isset($courseDate->id)) continue;
                    $fecha_inicio = date("Y-m-d", strtotime($courseDate->date)).' '.date("H:i:s", strtotime($courseDate->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);
                        if($diff_in_hours > 48 && $diff_in_hours < 72) {
                            if(BookingPaymentNoticeLog::checkToNotify($line)) {
                                $schoolData = School::find($booking->school_id);
                                if(!isset($schoolData->id)) continue;
                                $bookingData = $booking;
                                $buyerUser = User::find($booking->user_main_id);
                                if(!isset($buyerUser->id)) continue;

                                // Create pay link
                                $link = PayrexxHelpers::createPayLink($schoolData, $bookingData, $buyerUser);
                                if (strlen($link) < 1)
                                {
                                    throw new \Exception('Cant create Payrexx Direct Link for School ID=' . $schoolData->id);
                                }else{

                                    \Mail::to($buyerUser->email)
                                        ->send(new BookingNoticePayMailer(
                                            $schoolData,
                                            $bookingData,
                                            $buyerUser,
                                            $link
                                        ));

                                    BookingPaymentNoticeLog::create([
                                        'booking2_id' => $booking->id,
                                        'booking_user2_id' => $line->id,
                                        'date' => date('Y-m-d H:i:s')
                                    ]);

                                    \Illuminate\Support\Facades\Log::debug('sendPaymentNotice: ID '.$booking->id.' - Enviamos aviso para '. $line->id." : Fecha de inicio ".$fecha_reserva);
                                }
                            }
                        }
                    }

                }

            }

        }

        \Illuminate\Support\Facades\Log::debug('Fin cron sendPaymentNotice');
    }


    public static function cancelUnpaids15m()
    {
        /*
        We cancel unpaid bookings 15 minutes after the creation if they have not been paid
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron cancelUnpaids15m');

        $bookings = self::where('payment_method_id', 2)
            ->where('paid', 0)
            ->get();

        foreach($bookings as $booking)
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

        \Illuminate\Support\Facades\Log::debug('Fin cron cancelUnpaids15m');
    }


    public static function cancelUnpaids48h()
    {
        /*
            We cancel unpaid bookings 48 hours before the start of the course
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron cancelUnpaids48h');

        $bookings = self::where('payment_method_id', 3)
            ->where('paid', 0)
            ->get();

        foreach($bookings as $booking)
        {
            // the booking can have different courses/classes
            $lines = BookingUsers2::where('booking2_id', $booking->id)->get();

            $fecha_actual = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));

            foreach($lines as $line)
            {
                if(!empty($line->course2_id)) {
                    // course privé
                    $fecha_inicio = date("Y-m-d", strtotime($line->date)).' '.date("H:i:s", strtotime($line->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);
                        if($diff_in_hours > 0 && $diff_in_hours < 48) {
                            // eliminamos registro de booking_users2
                            if(count($lines)>1) {
                                // eliminamos únicamente la línea y recalculamos precio reserva
                                $tipo = 'parcial';
                                self::cancelBookingUser($line->id);
                            }else{
                                // sólo hay una línea en la reserva, eliminamos la reserva completa
                                $tipo = 'completa';
                                self::cancelBookingFull($booking->id);
                            }

                            \Illuminate\Support\Facades\Log::debug('cancelUnpaids48h: Privé ID '.$booking->id.' - Eliminamos reserva '.$tipo.'. ID Linea: '. $line->id." : Fecha de inicio ".$fecha_reserva.' - Dif in hours: '.$diff_in_hours);
                        }
                    }

                }elseif(!empty($line->course_groups_subgroup2_id)){
                    /*
                        course collectif
                        we need the first date of the course
                    */
                    $subgroup = CourseGroupsSubgroups2::where('id', $line->course_groups_subgroup2_id)->first();
                    if(!isset($subgroup->id)) continue;
                    $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                    if(!isset($group->id)) continue;
                    $courseDate = CourseDates2::select("id","date","hour")->where('course2_id', $group->course2_id)
                        ->orderBy('date', 'asc')
                        ->orderBy('hour', 'asc')
                        ->first();

                    if(!isset($courseDate->id)) continue;
                    $fecha_inicio = date("Y-m-d", strtotime($courseDate->date)).' '.date("H:i:s", strtotime($courseDate->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);

                        if($diff_in_hours > 0 && $diff_in_hours < 48) {
                            if(count($lines)>1) {
                                // eliminamos únicamente la línea y recalculamos precio reserva
                                $tipo = 'parcial';
                                self::cancelBookingUser($line->id);
                            }else{
                                // sólo hay una línea en la reserva, eliminamos la reserva completa
                                $tipo = 'completa';
                                self::cancelBookingFull($booking->id);
                            }

                            \Illuminate\Support\Facades\Log::debug('cancelUnpaids48h: Colective ID '.$booking->id.' - Eliminamos reserva '.$tipo.'. ID Línea: '. $line->id." : Fecha de inicio ".$fecha_reserva.' - Dif in hours: '.$diff_in_hours);
                        }

                    }
                }
            }
        }

        \Illuminate\Support\Facades\Log::debug('Fin cron cancelUnpaids48h');
    }

    public static function cancelBookingFull($bookingID)
    {
        $bookingData = self::where('id', '=', intval($bookingID))->first();
        if(isset($bookingData->id)) {
            $cancelledLines = $bookingData->parseBookedGroupedCourses();
            BookingUsers2::where('booking2_id', $bookingData->id)->delete();

            if ($bookingData->has_cancellation_insurance)
            {
                $bookingData->price_total = $bookingData->price_cancellation_insurance;
                $bookingData->save();
            }

            $bookingData->delete();

            $mySchool = School::find($bookingData->school_id);
            $voucherData = array();

            dispatch(function () use ($mySchool, $bookingData, $cancelledLines, $voucherData) {
                $buyerUser = $bookingData->main_user;

                // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                try
                {
                    \Mail::to($buyerUser->email)
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

    public static function cancelBookingUser($bookingUserID)
    {
        $bookingUserData = BookingUsers2::with('booking')
            ->where('id', intval($bookingUserID) )
            ->first();
        if(isset($bookingUserData->id) && isset($bookingUserData->booking)) {
            $bookingData = $bookingUserData->booking;

            $bookingUserData->delete();

            if ($bookingUserData->price > 0)
            {
                $bookingData->price_total = $bookingData->price_total - $bookingUserData->price;
                $bookingData->save();
            }

            $cancelledLines = $bookingData->parseBookedGroupedCourses();

            $mySchool = School::find($bookingData->school_id);
            $voucherData = array();

            dispatch(function () use ($mySchool, $bookingData, $cancelledLines, $voucherData) {
                $buyerUser = $bookingData->main_user;

                // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                try
                {
                    \Mail::to($buyerUser->email)
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
                    \Illuminate\Support\Facades\Log::debug('BookingController->cancelBookingUser BookingCancelMailer: ' . $ex->getMessage());
                }
            })->afterResponse();
        }
    }

    public static function bookingInfo24h()
    {
        /*
            We send reservation information 24 hours before the course starts
        */
        \Illuminate\Support\Facades\Log::debug('Inicio cron bookingInfo24h');

        $bookings = self::where('paid', 1)
            ->get();

        foreach($bookings as $booking)
        {
            $sendEmail = 0;
            // the booking can have different courses/classes
            $lines = BookingUsers2::where('booking2_id', $booking->id)->get();

            $fecha_actual = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));

            foreach($lines as $line)
            {
                if(!empty($line->course2_id)) {
                    // course privé
                    $fecha_inicio = date("Y-m-d", strtotime($line->date)).' '.date("H:i:s", strtotime($line->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);
                        if($diff_in_hours < 24) {
                            $sendEmail = 1;
                            \Illuminate\Support\Facades\Log::debug('bookingInfo24h: Privé ID '.$booking->id.' - Enviamos info de la reserva: '. $line->id." : Fecha de inicio ".$fecha_reserva.' - Dif in hours: '.$diff_in_hours);
                        }
                    }

                }elseif(!empty($line->course_groups_subgroup2_id)){
                    /*
                        course collectif
                        we need the first date of the course
                    */
                    $subgroup = CourseGroupsSubgroups2::where('id', $line->course_groups_subgroup2_id)->first();
                    if(!isset($subgroup->id)) continue;
                    $group = CourseGroups2::where('id', $subgroup->course_group2_id)->first();
                    if(!isset($group->id)) continue;
                    $courseDate = CourseDates2::select("id","date","hour")->where('course2_id', $group->course2_id)
                        ->orderBy('date', 'asc')
                        ->orderBy('hour', 'asc')
                        ->first();

                    if(!isset($courseDate->id)) continue;
                    $fecha_inicio = date("Y-m-d", strtotime($courseDate->date)).' '.date("H:i:s", strtotime($courseDate->hour));
                    $fecha_reserva = Carbon::createFromFormat('Y-m-d H:i:s', $fecha_inicio);

                    if( $fecha_actual < $fecha_reserva ) {
                        $diff_in_hours = $fecha_reserva->diffInHours($fecha_actual);

                        if($diff_in_hours < 24) {
                            $sendEmail = 1;
                            \Illuminate\Support\Facades\Log::debug('bookingInfo24h: Colective ID '.$booking->id.' - Enviamos info de la reserva: '. $line->id." : Fecha de inicio ".$fecha_reserva.' - Dif in hours: '.$diff_in_hours);
                        }
                    }

                }
            }

            if ($sendEmail == 1) {
                $mySchool = School::find($booking->school_id);
                $newBooking = $booking;
                $buyerUser = User::find($booking->user_main_id);

                dispatch(function () use ($mySchool, $newBooking, $buyerUser) {
                    // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                    try
                    {
                        \Mail::to($buyerUser->email)
                            ->send(new BookingInfoMailer(
                                $mySchool,
                                $newBooking,
                                $buyerUser
                            ));
                    }
                    catch (\Exception $ex)
                    {
                        \Illuminate\Support\Facades\Log::debug('Cron bookingInfo24h: ' . $ex->getMessage());
                    }
                })->afterResponse();
            }
        }

        \Illuminate\Support\Facades\Log::debug('Fin cron bookingInfo24h');
    }


    public static function markPastBookings()
    {
        \Illuminate\Support\Facades\Log::debug('Inicio cron markPastBookings');

        $now = Carbon::now();
        $coursesFutureCache = [];

        // Take each Booking that (currently) seems not past:
        foreach (self::withTrashed()->with(['booking_users', 'booking_users.subgroup', 'booking_users.subgroup.group'])
                     ->where('is_past', 0)
                     ->get() as $b)
        {
            $changeToPast = true;

            // 1. Includes any Private Course in the future ?
            foreach ($b->booking_users as $bu)
            {
                if ($bu->date != null)
                {
                    $durationCarbon = Carbon::parse($bu->duration);
                    $durationHours = $durationCarbon->format('H');
                    $durationMinutes = $durationCarbon->format('i');
                    $durationSeconds = $durationCarbon->format('s');
                    $dateEnd = Carbon::parse($bu->date->toDateString() . ' ' . $bu->hour->toTimeString())
                        ->addHours($durationHours)
                        ->addMinutes($durationMinutes)
                        ->addSeconds($durationSeconds);

                    if ($dateEnd->gt($now))
                    {
                        // This Private Course is future, so Booking is not past
                        $changeToPast = false;
                        break;
                    }
                }
            }


            // 2. Includes any Collective Course in the future ?
            if ($changeToPast)
            {
                foreach ($b->booking_users as $bu)
                {
                    if ($bu->subgroup)
                    {
                        // Check if related Course has any of its dates not-yet-past
                        $courseID = $bu->subgroup->group->course2_id;
                        if (!isset($coursesFutureCache[$courseID]))
                        {
                            $course = Course2::find($courseID);
                            if ($course)
                            {
                                $courseDetails = $course->toArray();
                                $coursesFutureCache[$courseID] = ($courseDetails['past_dates'] < $courseDetails['total_dates']);
                            }
                            else
                            {
                                // Somehow course was deleted, so assume it's not future
                                $coursesFutureCache[$courseID] = false;
                            }
                        }

                        if ($coursesFutureCache[$courseID])
                        {
                            // This Collective Course is future, so Booking is not past
                            $changeToPast = false;
                            break;
                        }
                    }
                }
            }


            // Update Booking if changed to already past
            if ($changeToPast)
            {
                $b->is_past = true;
                $b->save();
            }
        }

        \Illuminate\Support\Facades\Log::debug('Fin cron markPastBookings');
    }

}
