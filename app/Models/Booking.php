<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

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
 */class Booking extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'bookings';

    public $fillable = [
        'school_id',
        'client_main_id',
        'price_total',
        'has_cancellation_insurance',
        'price_cancellation_insurance',
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
        'color'
    ];

    protected $casts = [
        'price_total' => 'decimal:2',
        'has_cancellation_insurance' => 'boolean',
        'price_cancellation_insurance' => 'decimal:2',
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
        'color' => 'string'
    ];

    public static array $rules = [
        'school_id' => 'nullable',
        'client_main_id' => 'nullable',
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
        'color' => 'nullable|string|max:45',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
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



}
