<?php

namespace App\V5\Modules\Booking\Models;

use App\V5\Modules\Booking\Models\BookingExtra;
use App\V5\Modules\Booking\Models\BookingEquipment;
use App\V5\Modules\Booking\Models\BookingPayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * V5 Booking Model
 * 
 * Represents a booking in the V5 system with season context,
 * following the booking wizard interface and workflow states.
 * 
 * @property int $id
 * @property string $booking_reference
 * @property int $season_id
 * @property int $school_id
 * @property int $client_id
 * @property int|null $course_id
 * @property int|null $monitor_id
 * @property string $type
 * @property string $status
 * @property array $booking_data
 * @property array $participants
 * @property float $base_price
 * @property float $extras_price
 * @property float $equipment_price
 * @property float $insurance_price
 * @property float $tax_amount
 * @property float $discount_amount
 * @property float $total_price
 * @property string $currency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $meeting_point
 * @property bool $has_insurance
 * @property bool $has_equipment
 * @property string|null $special_requests
 * @property string|null $notes
 * @property array|null $metadata
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Booking extends Model
{
    use SoftDeletes;

    protected $table = 'v5_bookings';

    protected $fillable = [
        'booking_reference',
        'season_id',
        'school_id',
        'client_id',
        'course_id',
        'monitor_id',
        'type',
        'status',
        'booking_data',
        'participants',
        'base_price',
        'extras_price',
        'equipment_price',
        'insurance_price',
        'tax_amount',
        'discount_amount',
        'total_price',
        'currency',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'meeting_point',
        'has_insurance',
        'has_equipment',
        'special_requests',
        'notes',
        'metadata',
        'confirmed_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'booking_data' => 'array',
        'participants' => 'array',
        'metadata' => 'array',
        'base_price' => 'decimal:2',
        'extras_price' => 'decimal:2',
        'equipment_price' => 'decimal:2',
        'insurance_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'has_insurance' => 'boolean',
        'has_equipment' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'confirmed_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Booking types
    public const TYPE_COURSE = 'course';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_MATERIAL = 'material';

    // Booking statuses following the workflow: pending → confirmed → paid → completed → cancelled
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    // Valid status transitions
    public const VALID_STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PAID, self::STATUS_CANCELLED],
        self::STATUS_PAID => [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
        self::STATUS_COMPLETED => [], // Final state
        self::STATUS_CANCELLED => [], // Final state
        self::STATUS_NO_SHOW => [], // Final state
    ];

    /**
     * Get all valid booking types
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_COURSE,
            self::TYPE_ACTIVITY,
            self::TYPE_MATERIAL,
        ];
    }

    /**
     * Get all valid booking statuses
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PAID,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_NO_SHOW,
        ];
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $currentStatus = $this->status;
        
        if (!isset(self::VALID_STATUS_TRANSITIONS[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::VALID_STATUS_TRANSITIONS[$currentStatus]);
    }

    /**
     * Generate unique booking reference
     */
    public static function generateBookingReference(int $seasonId, int $schoolId): string
    {
        $seasonCode = str_pad($seasonId, 2, '0', STR_PAD_LEFT);
        $schoolCode = str_pad($schoolId, 2, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "BKV5-{$seasonCode}{$schoolCode}-{$timestamp}-{$random}";
    }

    /**
     * Boot method to auto-generate booking reference
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = self::generateBookingReference(
                    $booking->season_id,
                    $booking->school_id
                );
            }
        });
    }

    /**
     * Relationship: Booking extras
     */
    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    /**
     * Relationship: Booking equipment
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(BookingEquipment::class);
    }

    /**
     * Relationship: Booking payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Relationship: Season (mandatory context)
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(\App\V5\Modules\Season\Models\Season::class);
    }

    /**
     * Relationship: School (mandatory context)
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class);
    }

    /**
     * Relationship: Client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    /**
     * Relationship: Course (nullable for non-course bookings)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    /**
     * Relationship: Monitor (nullable for self-service bookings)
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Monitor::class);
    }

    /**
     * Scope: Filter by season (mandatory context)
     */
    public function scopeForSeason($query, int $seasonId)
    {
        return $query->where('season_id', $seasonId);
    }

    /**
     * Scope: Filter by school (mandatory context)
     */
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Active bookings (not cancelled or deleted)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED])
                    ->whereNull('deleted_at');
    }

    /**
     * Scope: Confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CONFIRMED,
            self::STATUS_PAID,
            self::STATUS_COMPLETED
        ]);
    }

    /**
     * Scope: Upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
                    ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_NO_SHOW]);
    }

    /**
     * Get the total paid amount from payments
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()
                   ->where('status', BookingPayment::STATUS_COMPLETED)
                   ->sum('amount');
    }

    /**
     * Get the remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_price - $this->getTotalPaidAttribute());
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->getRemainingBalanceAttribute() <= 0.01; // Account for floating point precision
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PAID
        ]) && $this->start_date && $this->start_date->isFuture();
    }

    /**
     * Get booking duration in hours
     */
    public function getDurationInHours(): ?float
    {
        if (!$this->start_date || !$this->end_date || !$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
        $end = Carbon::parse($this->end_date->format('Y-m-d') . ' ' . $this->end_time);

        return $start->diffInHours($end, true);
    }

    /**
     * Get participant count
     */
    public function getParticipantCountAttribute(): int
    {
        return is_array($this->participants) ? count($this->participants) : 0;
    }

    /**
     * Check if booking requires availability validation
     */
    public function requiresAvailabilityValidation(): bool
    {
        return in_array($this->type, [self::TYPE_COURSE, self::TYPE_ACTIVITY]);
    }

    /**
     * Format booking for frontend response
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'season_id' => $this->season_id,
            'school_id' => $this->school_id,
            'client' => [
                'id' => $this->client_id,
                'name' => $this->client?->first_name . ' ' . $this->client?->last_name,
                'email' => $this->client?->email,
            ],
            'type' => $this->type,
            'status' => $this->status,
            'course' => $this->course ? [
                'id' => $this->course->id,
                'name' => $this->course->name,
                'description' => $this->course->description,
            ] : null,
            'monitor' => $this->monitor ? [
                'id' => $this->monitor->id,
                'name' => $this->monitor->first_name . ' ' . $this->monitor->last_name,
            ] : null,
            'schedule' => [
                'start_date' => $this->start_date?->format('Y-m-d'),
                'end_date' => $this->end_date?->format('Y-m-d'),
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'duration_hours' => $this->getDurationInHours(),
                'meeting_point' => $this->meeting_point,
            ],
            'participants' => $this->participants,
            'participant_count' => $this->getParticipantCountAttribute(),
            'pricing' => [
                'base_price' => $this->base_price,
                'extras_price' => $this->extras_price,
                'equipment_price' => $this->equipment_price,
                'insurance_price' => $this->insurance_price,
                'tax_amount' => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'total_price' => $this->total_price,
                'currency' => $this->currency,
                'total_paid' => $this->getTotalPaidAttribute(),
                'remaining_balance' => $this->getRemainingBalanceAttribute(),
                'is_fully_paid' => $this->isFullyPaid(),
            ],
            'features' => [
                'has_insurance' => $this->has_insurance,
                'has_equipment' => $this->has_equipment,
            ],
            'extras' => $this->extras?->map(fn($extra) => $extra->toFrontendArray())->toArray() ?? [],
            'equipment' => $this->equipment?->map(fn($equipment) => $equipment->toFrontendArray())->toArray() ?? [],
            'payments' => $this->payments?->map(fn($payment) => $payment->toFrontendArray())->toArray() ?? [],
            'special_requests' => $this->special_requests,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
                'confirmed_at' => $this->confirmed_at?->toISOString(),
                'paid_at' => $this->paid_at?->toISOString(),
                'completed_at' => $this->completed_at?->toISOString(),
                'cancelled_at' => $this->cancelled_at?->toISOString(),
            ],
            'cancellation_reason' => $this->cancellation_reason,
            'can_be_cancelled' => $this->canBeCancelled(),
            'available_status_transitions' => $this->getAvailableStatusTransitions(),
        ];
    }

    /**
     * Get available status transitions for current status
     */
    public function getAvailableStatusTransitions(): array
    {
        return self::VALID_STATUS_TRANSITIONS[$this->status] ?? [];
    }
}