<?php

namespace App\V5\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * V5 Booking Payment Model
 * 
 * Represents payment transactions associated with a booking
 * including deposits, full payments, refunds, and payment methods.
 * 
 * @property int $id
 * @property int $booking_id
 * @property string $payment_reference
 * @property string $payment_type
 * @property string $payment_method
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $gateway
 * @property string|null $gateway_transaction_id
 * @property string|null $gateway_reference
 * @property array|null $gateway_response
 * @property float|null $fee_amount
 * @property string|null $fee_currency
 * @property Carbon|null $processed_at
 * @property Carbon|null $refunded_at
 * @property float|null $refunded_amount
 * @property string|null $refund_reason
 * @property array|null $payment_data
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BookingPayment extends Model
{
    protected $table = 'v5_booking_payments';

    protected $fillable = [
        'booking_id',
        'payment_reference',
        'payment_type',
        'payment_method',
        'amount',
        'currency',
        'status',
        'gateway',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_response',
        'fee_amount',
        'fee_currency',
        'processed_at',
        'refunded_at',
        'refunded_amount',
        'refund_reason',
        'payment_data',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'payment_data' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Payment types
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_FULL_PAYMENT = 'full_payment';
    public const TYPE_PARTIAL_PAYMENT = 'partial_payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_FEE = 'fee';

    // Payment methods
    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_DEBIT_CARD = 'debit_card';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_PAYPAL = 'paypal';
    public const METHOD_APPLE_PAY = 'apple_pay';
    public const METHOD_GOOGLE_PAY = 'google_pay';
    public const METHOD_CASH = 'cash';
    public const METHOD_VOUCHER = 'voucher';
    public const METHOD_OTHER = 'other';

    // Payment statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    // Payment gateways
    public const GATEWAY_PAYREXX = 'payrexx';
    public const GATEWAY_STRIPE = 'stripe';
    public const GATEWAY_PAYPAL = 'paypal';
    public const GATEWAY_MANUAL = 'manual';

    /**
     * Get all valid payment types
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_DEPOSIT,
            self::TYPE_FULL_PAYMENT,
            self::TYPE_PARTIAL_PAYMENT,
            self::TYPE_REFUND,
            self::TYPE_FEE,
        ];
    }

    /**
     * Get all valid payment methods
     */
    public static function getValidMethods(): array
    {
        return [
            self::METHOD_CREDIT_CARD,
            self::METHOD_DEBIT_CARD,
            self::METHOD_BANK_TRANSFER,
            self::METHOD_PAYPAL,
            self::METHOD_APPLE_PAY,
            self::METHOD_GOOGLE_PAY,
            self::METHOD_CASH,
            self::METHOD_VOUCHER,
            self::METHOD_OTHER,
        ];
    }

    /**
     * Get all valid payment statuses
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
            self::STATUS_PARTIALLY_REFUNDED,
        ];
    }

    /**
     * Get all valid payment gateways
     */
    public static function getValidGateways(): array
    {
        return [
            self::GATEWAY_PAYREXX,
            self::GATEWAY_STRIPE,
            self::GATEWAY_PAYPAL,
            self::GATEWAY_MANUAL,
        ];
    }

    /**
     * Generate unique payment reference
     */
    public static function generatePaymentReference(int $bookingId): string
    {
        $bookingCode = str_pad($bookingId, 6, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "PAY-{$bookingCode}-{$timestamp}-{$random}";
    }

    /**
     * Boot method to auto-generate payment reference
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = self::generatePaymentReference($payment->booking_id);
            }
        });
    }

    /**
     * Relationship: Parent booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope: Filter by payment type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope: Filter by payment method
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Completed payments only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Pending payments only
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Failed payments only
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is refunded (fully or partially)
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && $this->payment_type !== self::TYPE_REFUND;
    }

    /**
     * Get net amount (amount minus fees)
     */
    public function getNetAmountAttribute(): float
    {
        return $this->amount - ($this->fee_amount ?? 0);
    }

    /**
     * Get refundable amount
     */
    public function getRefundableAmountAttribute(): float
    {
        if (!$this->canBeRefunded()) {
            return 0.0;
        }

        return $this->amount - ($this->refunded_amount ?? 0);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(?string $gatewayTransactionId = null, ?array $gatewayResponse = null): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'gateway_transaction_id' => $gatewayTransactionId ?? $this->gateway_transaction_id,
            'gateway_response' => $gatewayResponse ?? $this->gateway_response,
        ]);

        return $this;
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(?string $reason = null, ?array $gatewayResponse = null): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes,
            'gateway_response' => $gatewayResponse ?? $this->gateway_response,
        ]);

        return $this;
    }

    /**
     * Process partial refund
     */
    public function processPartialRefund(float $amount, string $reason): self
    {
        if (!$this->canBeRefunded() || $amount > $this->getRefundableAmountAttribute()) {
            throw new \InvalidArgumentException('Invalid refund amount');
        }

        $currentRefunded = $this->refunded_amount ?? 0;
        $newRefundedAmount = $currentRefunded + $amount;

        $status = $newRefundedAmount >= $this->amount 
            ? self::STATUS_REFUNDED 
            : self::STATUS_PARTIALLY_REFUNDED;

        $this->update([
            'status' => $status,
            'refunded_amount' => $newRefundedAmount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Process full refund
     */
    public function processFullRefund(string $reason): self
    {
        return $this->processPartialRefund($this->getRefundableAmountAttribute(), $reason);
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        $displayNames = [
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_DEBIT_CARD => 'Debit Card',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_APPLE_PAY => 'Apple Pay',
            self::METHOD_GOOGLE_PAY => 'Google Pay',
            self::METHOD_CASH => 'Cash',
            self::METHOD_VOUCHER => 'Voucher',
            self::METHOD_OTHER => 'Other',
        ];

        return $displayNames[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    /**
     * Format payment for frontend response
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'payment_reference' => $this->payment_reference,
            'type' => $this->payment_type,
            'method' => $this->payment_method,
            'method_display' => $this->getPaymentMethodDisplayAttribute(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'gateway' => [
                'name' => $this->gateway,
                'transaction_id' => $this->gateway_transaction_id,
                'reference' => $this->gateway_reference,
            ],
            'fees' => [
                'fee_amount' => $this->fee_amount,
                'fee_currency' => $this->fee_currency,
                'net_amount' => $this->getNetAmountAttribute(),
            ],
            'refund' => [
                'refunded_amount' => $this->refunded_amount,
                'refundable_amount' => $this->getRefundableAmountAttribute(),
                'refund_reason' => $this->refund_reason,
                'refunded_at' => $this->refunded_at?->toISOString(),
                'can_be_refunded' => $this->canBeRefunded(),
            ],
            'status_checks' => [
                'is_completed' => $this->isCompleted(),
                'is_pending' => $this->isPending(),
                'is_failed' => $this->isFailed(),
                'is_refunded' => $this->isRefunded(),
            ],
            'payment_data' => $this->payment_data,
            'notes' => $this->notes,
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
                'processed_at' => $this->processed_at?->toISOString(),
            ],
        ];
    }
}