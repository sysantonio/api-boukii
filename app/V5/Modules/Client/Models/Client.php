<?php

namespace App\V5\Modules\Client\Models;

use App\V5\Modules\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * V5 Client Model
 * 
 * Represents a client in the V5 system with season context,
 * profile analysis, and booking relationships.
 * 
 * @property int $id
 * @property string $client_reference
 * @property int $school_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $telephone
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string|null $nationality
 * @property string|null $preferred_language
 * @property string $status
 * @property string $profile_type
 * @property array|null $address
 * @property array|null $emergency_contact
 * @property array|null $medical_conditions
 * @property array|null $preferences
 * @property array|null $tags
 * @property string|null $avatar
 * @property string|null $notes
 * @property array|null $metadata
 * @property Carbon|null $last_booking_at
 * @property Carbon|null $last_activity_at
 * @property float $total_spent
 * @property int $total_bookings
 * @property float $average_rating
 * @property string $loyalty_tier
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Client extends Model
{
    use SoftDeletes;

    protected $table = 'v5_clients';

    protected $fillable = [
        'client_reference',
        'school_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'telephone',
        'date_of_birth',
        'gender',
        'nationality',
        'preferred_language',
        'status',
        'profile_type',
        'address',
        'emergency_contact',
        'medical_conditions',
        'preferences',
        'tags',
        'avatar',
        'notes',
        'metadata',
        'last_booking_at',
        'last_activity_at',
        'total_spent',
        'total_bookings',
        'average_rating',
        'loyalty_tier',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'address' => 'array',
        'emergency_contact' => 'array',
        'medical_conditions' => 'array',
        'preferences' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'last_booking_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'total_bookings' => 'integer',
        'average_rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'date_of_birth',
        'last_booking_at',
        'last_activity_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Client statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_PENDING = 'pending';

    // Profile types (automatic profiling)
    public const PROFILE_NEW = 'new';
    public const PROFILE_REGULAR = 'regular';
    public const PROFILE_VIP = 'vip';
    public const PROFILE_FREQUENT = 'frequent';
    public const PROFILE_SEASONAL = 'seasonal';
    public const PROFILE_RISK = 'risk';

    // Loyalty tiers
    public const TIER_BRONZE = 'bronze';
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';
    public const TIER_PLATINUM = 'platinum';

    // Skill levels
    public const LEVEL_BEGINNER = 'Principiante';
    public const LEVEL_INTERMEDIATE = 'Intermedio';
    public const LEVEL_ADVANCED = 'Avanzado';
    public const LEVEL_EXPERT = 'Experto';

    /**
     * Get all valid statuses
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_BLOCKED,
            self::STATUS_PENDING,
        ];
    }

    /**
     * Get all valid profile types
     */
    public static function getValidProfileTypes(): array
    {
        return [
            self::PROFILE_NEW,
            self::PROFILE_REGULAR,
            self::PROFILE_VIP,
            self::PROFILE_FREQUENT,
            self::PROFILE_SEASONAL,
            self::PROFILE_RISK,
        ];
    }

    /**
     * Get all valid loyalty tiers
     */
    public static function getValidLoyaltyTiers(): array
    {
        return [
            self::TIER_BRONZE,
            self::TIER_SILVER,
            self::TIER_GOLD,
            self::TIER_PLATINUM,
        ];
    }

    /**
     * Get all valid skill levels
     */
    public static function getValidLevels(): array
    {
        return [
            self::LEVEL_BEGINNER,
            self::LEVEL_INTERMEDIATE,
            self::LEVEL_ADVANCED,
            self::LEVEL_EXPERT,
        ];
    }

    /**
     * Generate unique client reference
     */
    public static function generateClientReference(int $schoolId): string
    {
        $schoolCode = str_pad($schoolId, 2, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "CLV5-{$schoolCode}-{$timestamp}-{$random}";
    }

    /**
     * Boot method to auto-generate client reference
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->client_reference)) {
                $client->client_reference = self::generateClientReference($client->school_id);
            }
            
            // Set default values
            if (empty($client->status)) {
                $client->status = self::STATUS_ACTIVE;
            }
            
            if (empty($client->profile_type)) {
                $client->profile_type = self::PROFILE_NEW;
            }
            
            if (empty($client->loyalty_tier)) {
                $client->loyalty_tier = self::TIER_BRONZE;
            }
            
            if (empty($client->preferred_language)) {
                $client->preferred_language = 'es';
            }
        });

        static::updating(function ($client) {
            $client->last_activity_at = now();
        });
    }

    /**
     * Relationship: School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class);
    }

    /**
     * Relationship: Bookings across all seasons
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /**
     * Relationship: Bookings for specific season
     */
    public function seasonBookings(int $seasonId): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id')
                   ->where('season_id', $seasonId);
    }

    /**
     * Relationship: Active bookings
     */
    public function activeBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id')
                   ->whereIn('status', [
                       Booking::STATUS_PENDING,
                       Booking::STATUS_CONFIRMED,
                       Booking::STATUS_PAID
                   ]);
    }

    /**
     * Relationship: Completed bookings
     */
    public function completedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id')
                   ->where('status', Booking::STATUS_COMPLETED);
    }

    /**
     * Scope: Filter by school
     */
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by profile type
     */
    public function scopeWithProfile($query, string $profileType)
    {
        return $query->where('profile_type', $profileType);
    }

    /**
     * Scope: Filter by loyalty tier
     */
    public function scopeWithLoyaltyTier($query, string $tier)
    {
        return $query->where('loyalty_tier', $tier);
    }

    /**
     * Scope: Active clients only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Search by name or email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('client_reference', 'LIKE', "%{$search}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
        });
    }

    /**
     * Scope: Clients with bookings in season
     */
    public function scopeWithSeasonBookings($query, int $seasonId)
    {
        return $query->whereHas('bookings', function ($bookingQuery) use ($seasonId) {
            $bookingQuery->where('season_id', $seasonId);
        });
    }

    /**
     * Scope: Recent clients (created in last N days)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get client's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get client's initials
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(
            substr($this->first_name, 0, 1) . 
            substr($this->last_name, 0, 1)
        );
    }

    /**
     * Get client's age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? 
            $this->date_of_birth->diffInYears(now()) : 
            null;
    }

    /**
     * Check if client is VIP
     */
    public function isVip(): bool
    {
        return $this->profile_type === self::PROFILE_VIP || 
               $this->loyalty_tier === self::TIER_PLATINUM;
    }

    /**
     * Check if client is new (created in last 30 days or has no bookings)
     */
    public function isNew(): bool
    {
        return $this->profile_type === self::PROFILE_NEW ||
               $this->created_at->isAfter(now()->subDays(30)) ||
               $this->total_bookings === 0;
    }

    /**
     * Check if client is at risk (high cancellation rate, etc.)
     */
    public function isRisk(): bool
    {
        return $this->profile_type === self::PROFILE_RISK;
    }

    /**
     * Update booking statistics
     */
    public function updateBookingStats(): void
    {
        $completedBookings = $this->completedBookings()
                                 ->with(['payments' => function ($query) {
                                     $query->where('status', 'completed');
                                 }])
                                 ->get();

        $this->update([
            'total_bookings' => $completedBookings->count(),
            'total_spent' => $completedBookings->sum('total_price'),
            'last_booking_at' => $completedBookings->max('created_at'),
            'average_rating' => $completedBookings->avg('client_rating') ?: 0,
        ]);

        // Update loyalty tier based on spending
        $this->updateLoyaltyTier();
        
        // Update profile type based on behavior
        $this->updateProfileType();
    }

    /**
     * Update loyalty tier based on total spent
     */
    public function updateLoyaltyTier(): void
    {
        $tier = match (true) {
            $this->total_spent >= 5000 => self::TIER_PLATINUM,
            $this->total_spent >= 2000 => self::TIER_GOLD,
            $this->total_spent >= 500 => self::TIER_SILVER,
            default => self::TIER_BRONZE
        };

        if ($this->loyalty_tier !== $tier) {
            $this->update(['loyalty_tier' => $tier]);
        }
    }

    /**
     * Update profile type based on behavior analysis
     */
    public function updateProfileType(): void
    {
        $profile = $this->analyzeClientProfile();
        
        if ($this->profile_type !== $profile) {
            $this->update(['profile_type' => $profile]);
        }
    }

    /**
     * Analyze client profile based on behavior
     */
    public function analyzeClientProfile(): string
    {
        // Risk profile check
        if ($this->hasRiskFactors()) {
            return self::PROFILE_RISK;
        }

        // VIP profile check
        if ($this->total_spent >= 2000 || $this->loyalty_tier === self::TIER_PLATINUM) {
            return self::PROFILE_VIP;
        }

        // Frequent profile check
        if ($this->total_bookings >= 10 && $this->created_at->isBefore(now()->subMonths(6))) {
            return self::PROFILE_FREQUENT;
        }

        // Seasonal profile check
        if ($this->hasSeasonalPattern()) {
            return self::PROFILE_SEASONAL;
        }

        // Regular profile check
        if ($this->total_bookings >= 3 && $this->created_at->isBefore(now()->subMonths(2))) {
            return self::PROFILE_REGULAR;
        }

        // Default to new
        return self::PROFILE_NEW;
    }

    /**
     * Check if client has risk factors
     */
    private function hasRiskFactors(): bool
    {
        $cancelledBookings = $this->bookings()
                                 ->where('status', Booking::STATUS_CANCELLED)
                                 ->count();

        $cancellationRate = $this->total_bookings > 0 ? 
            ($cancelledBookings / $this->total_bookings) : 0;

        return $cancellationRate > 0.3; // More than 30% cancellation rate
    }

    /**
     * Check if client has seasonal booking pattern
     */
    private function hasSeasonalPattern(): bool
    {
        // This would analyze booking patterns across seasons
        // For now, return false - would need more complex analysis
        return false;
    }

    /**
     * Get preferred skill level from preferences
     */
    public function getPreferredLevelAttribute(): string
    {
        return $this->preferences['level'] ?? self::LEVEL_BEGINNER;
    }

    /**
     * Add tag to client
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?: [];
        
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove tag from client
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?: [];
        
        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
            $this->update(['tags' => array_values($tags)]);
        }
    }

    /**
     * Check if client has specific tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?: []);
    }

    /**
     * Format client for frontend response
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'client_reference' => $this->client_reference,
            'school_id' => $this->school_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->getFullNameAttribute(),
            'initials' => $this->getInitialsAttribute(),
            'email' => $this->email,
            'phone' => $this->phone,
            'telephone' => $this->telephone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->getAgeAttribute(),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'preferred_language' => $this->preferred_language,
            'status' => $this->status,
            'profile_type' => $this->profile_type,
            'loyalty_tier' => $this->loyalty_tier,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'medical_conditions' => $this->medical_conditions,
            'preferences' => $this->preferences,
            'preferred_level' => $this->getPreferredLevelAttribute(),
            'tags' => $this->tags ?: [],
            'avatar' => $this->avatar,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'statistics' => [
                'total_bookings' => $this->total_bookings,
                'total_spent' => $this->total_spent,
                'average_rating' => $this->average_rating,
                'last_booking_at' => $this->last_booking_at?->toISOString(),
                'last_activity_at' => $this->last_activity_at?->toISOString(),
            ],
            'profile_flags' => [
                'is_vip' => $this->isVip(),
                'is_new' => $this->isNew(),
                'is_risk' => $this->isRisk(),
            ],
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}