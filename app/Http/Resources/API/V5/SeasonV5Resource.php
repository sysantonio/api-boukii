<?php

namespace App\Http\Resources\API\V5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * Class SeasonV5Resource
 * 
 * API Resource for Season model in V5 API.
 * Transforms season data for consistent API responses.
 * 
 * @package App\Http\Resources\API\V5
 */
class SeasonV5Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'is_closed' => $this->is_closed,
            'is_historical' => $this->is_historical,
            'is_current' => $this->is_current,
            'max_capacity' => $this->max_capacity,
            'price_modifier' => $this->price_modifier ? (float) $this->price_modifier : null,
            
            // Computed properties
            'duration_days' => $this->getDurationInDays(),
            'status' => $this->getSeasonStatus(),
            'progress_percentage' => $this->getProgressPercentage(),
            'days_remaining' => $this->getDaysRemaining(),
            'can_be_modified' => $this->canBeModified(),
            'can_be_deleted' => $this->canBeDeleted(),
            
            // Relationship data (only when loaded)
            'school' => $this->whenLoaded('school', function () {
                return [
                    'id' => $this->school->id,
                    'name' => $this->school->name,
                    'slug' => $this->school->slug,
                ];
            }),
            
            // Statistics (disabled until database schema includes season_id in bookings/courses tables)
            // 'statistics' => $this->when($this->relationLoaded('bookings') || $this->relationLoaded('courses'), function () {
            //     return [
            //         'total_bookings' => $this->bookings_count ?? $this->bookings()->count(),
            //         'total_courses' => $this->courses_count ?? $this->courses()->count(),
            //         'total_revenue' => $this->when(
            //             $this->relationLoaded('bookings'),
            //             fn() => $this->bookings->sum('total_amount')
            //         ),
            //         'active_bookings' => $this->when(
            //             $this->relationLoaded('bookings'),
            //             fn() => $this->bookings->whereIn('status', ['confirmed', 'pending'])->count()
            //         ),
            //     ];
            // }),
            
            // Temporary empty statistics until database schema is updated
            'statistics' => [
                'total_bookings' => 0,
                'total_courses' => 0,
                'total_revenue' => 0,
                'active_bookings' => 0,
            ],
            
            // Audit fields
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'closed_at' => $this->closed_at?->toISOString(),
            'closed_by' => $this->whenLoaded('closer', function () {
                return [
                    'id' => $this->closer->id,
                    'name' => $this->closer->name,
                    'email' => $this->closer->email,
                ];
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => 'v5',
                'resource' => 'season',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    // ==================== COMPUTED PROPERTIES ====================

    /**
     * Get season duration in days
     * 
     * @return int|null
     */
    private function getDurationInDays(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get current season status
     * 
     * @return string
     */
    private function getSeasonStatus(): string
    {
        if ($this->is_historical) {
            return 'historical';
        }

        if ($this->is_closed) {
            return 'closed';
        }

        if (!$this->start_date || !$this->end_date) {
            return 'draft';
        }

        $now = Carbon::now();
        
        if ($now->lt($this->start_date)) {
            return 'upcoming';
        }
        
        if ($now->gt($this->end_date)) {
            return 'finished';
        }
        
        if ($this->is_active) {
            return 'active';
        }
        
        return 'current';
    }

    /**
     * Get season progress percentage
     * 
     * @return float|null
     */
    private function getProgressPercentage(): ?float
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        $now = Carbon::now();
        $start = $this->start_date;
        $end = $this->end_date;

        // Before season starts
        if ($now->lt($start)) {
            return 0.0;
        }

        // After season ends
        if ($now->gt($end)) {
            return 100.0;
        }

        // During season
        $totalDuration = $start->diffInDays($end);
        $elapsedDays = $start->diffInDays($now);

        if ($totalDuration === 0) {
            return 100.0;
        }

        return round(($elapsedDays / $totalDuration) * 100, 1);
    }

    /**
     * Get days remaining in season
     * 
     * @return int|null
     */
    private function getDaysRemaining(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $now = Carbon::now();
        
        if ($now->gt($this->end_date)) {
            return 0; // Season has ended
        }

        return $now->diffInDays($this->end_date);
    }

    /**
     * Check if season can be modified
     * 
     * @return bool
     */
    private function canBeModified(): bool
    {
        return !$this->is_closed && 
               !$this->is_historical;
               // && !$this->hasActiveBookings(); // Disabled until database schema includes season_id
    }

    /**
     * Check if season can be deleted
     * 
     * @return bool
     */
    private function canBeDeleted(): bool
    {
        return !$this->is_closed && 
               !$this->is_historical;
               // && !$this->hasAnyBookings() && 
               // !$this->hasAnyCourses(); // Disabled until database schema includes season_id
    }

    /**
     * Check if season has active bookings
     * TODO: Re-enable once database schema includes season_id in bookings table
     * 
     * @return bool
     */
    private function hasActiveBookings(): bool
    {
        // Temporarily return false until database schema is updated
        return false;
        
        // Original code (disabled):
        // // If bookings are loaded, use the collection
        // if ($this->relationLoaded('bookings')) {
        //     return $this->bookings
        //         ->whereIn('status', ['confirmed', 'pending'])
        //         ->isNotEmpty();
        // }
        //
        // // Otherwise query the database
        // return $this->bookings()
        //     ->whereIn('status', ['confirmed', 'pending'])
        //     ->exists();
    }

    /**
     * Check if season has any bookings
     * TODO: Re-enable once database schema includes season_id in bookings table
     * 
     * @return bool
     */
    private function hasAnyBookings(): bool
    {
        // Temporarily return false until database schema is updated
        return false;
        
        // Original code (disabled):
        // // If bookings are loaded, use the collection
        // if ($this->relationLoaded('bookings')) {
        //     return $this->bookings->isNotEmpty();
        // }
        //
        // // Otherwise query the database
        // return $this->bookings()->exists();
    }

    /**
     * Check if season has any courses
     * TODO: Re-enable once database schema includes season_id in courses table
     * 
     * @return bool
     */
    private function hasAnyCourses(): bool
    {
        // Temporarily return false until database schema is updated
        return false;
        
        // Original code (disabled):
        // // If courses are loaded, use the collection
        // if ($this->relationLoaded('courses')) {
        //     return $this->courses->isNotEmpty();
        // }
        //
        // // Otherwise query the database
        // return $this->courses()->exists();
    }
}