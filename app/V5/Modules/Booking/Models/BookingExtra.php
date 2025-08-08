<?php

namespace App\V5\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * V5 Booking Extra Model
 * 
 * Represents additional services/extras added to a booking
 * such as insurance, equipment rental, special services, etc.
 * 
 * @property int $id
 * @property int $booking_id
 * @property string $extra_type
 * @property string $name
 * @property string|null $description
 * @property float $unit_price
 * @property int $quantity
 * @property float $total_price
 * @property string $currency
 * @property bool $is_required
 * @property bool $is_active
 * @property array|null $extra_data
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BookingExtra extends Model
{
    protected $table = 'v5_booking_extras';

    protected $fillable = [
        'booking_id',
        'extra_type',
        'name',
        'description',
        'unit_price',
        'quantity',
        'total_price',
        'currency',
        'is_required',
        'is_active',
        'extra_data',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'extra_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Extra types
    public const TYPE_INSURANCE = 'insurance';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_TRANSPORT = 'transport';
    public const TYPE_MEAL = 'meal';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CERTIFICATE = 'certificate';
    public const TYPE_SPECIAL_SERVICE = 'special_service';
    public const TYPE_OTHER = 'other';

    /**
     * Get all valid extra types
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_INSURANCE,
            self::TYPE_EQUIPMENT,
            self::TYPE_TRANSPORT,
            self::TYPE_MEAL,
            self::TYPE_PHOTO,
            self::TYPE_VIDEO,
            self::TYPE_CERTIFICATE,
            self::TYPE_SPECIAL_SERVICE,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Boot method to auto-calculate total price
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($extra) {
            $extra->total_price = $extra->unit_price * $extra->quantity;
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
     * Scope: Filter by extra type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('extra_type', $type);
    }

    /**
     * Scope: Active extras only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Required extras only
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Check if extra is insurance type
     */
    public function isInsurance(): bool
    {
        return $this->extra_type === self::TYPE_INSURANCE;
    }

    /**
     * Check if extra is equipment type
     */
    public function isEquipment(): bool
    {
        return $this->extra_type === self::TYPE_EQUIPMENT;
    }

    /**
     * Format extra for frontend response
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'type' => $this->extra_type,
            'name' => $this->name,
            'description' => $this->description,
            'pricing' => [
                'unit_price' => $this->unit_price,
                'quantity' => $this->quantity,
                'total_price' => $this->total_price,
                'currency' => $this->currency,
            ],
            'flags' => [
                'is_required' => $this->is_required,
                'is_active' => $this->is_active,
            ],
            'extra_data' => $this->extra_data,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}