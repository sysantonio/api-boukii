<?php

namespace App\Services;

use App\Models\Season;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class SeasonService
 * 
 * Business logic service for season management.
 * Handles CRUD operations and business rules for seasons.
 * 
 * @package App\Services
 */
class SeasonService
{
    /**
     * Get all seasons for a specific school
     * 
     * @param int $schoolId
     * @return Collection
     */
    public function getSeasonsForSchool(int $schoolId): Collection
    {
        return Season::where('school_id', $schoolId)
            ->with(['creator', 'closer'])
            // TODO: Add withCount once database schema includes season_id in bookings/courses tables
            // ->withCount(['bookings', 'courses'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get a specific season for a school
     * 
     * @param int $seasonId
     * @param int $schoolId
     * @return Season|null
     */
    public function getSeasonForSchool(int $seasonId, int $schoolId): ?Season
    {
        return Season::where('id', $seasonId)
            ->where('school_id', $schoolId)
            ->with(['creator', 'closer', 'school'])
            // TODO: Add withCount once database schema includes season_id in bookings/courses tables
            // ->withCount(['bookings', 'courses'])
            ->first();
    }

    /**
     * Get the current active season for a school
     * 
     * @param int $schoolId
     * @return Season|null
     */
    public function getCurrentSeasonForSchool(int $schoolId): ?Season
    {
        // First try to find explicitly active season
        $activeSeason = Season::where('school_id', $schoolId)
            ->where('is_active', true)
            ->where('is_closed', false)
            ->with(['creator', 'closer', 'school'])
            // TODO: Add withCount once database schema includes season_id in bookings/courses tables
            // ->withCount(['bookings', 'courses'])
            ->first();

        if ($activeSeason) {
            return $activeSeason;
        }

        // If no active season, find current season by date
        $now = Carbon::now();
        
        return Season::where('school_id', $schoolId)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('is_closed', false)
            ->with(['creator', 'closer', 'school'])
            // TODO: Add withCount once database schema includes season_id in bookings/courses tables
            // ->withCount(['bookings', 'courses'])
            ->orderBy('is_active', 'desc')
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Create a new season
     * 
     * @param array $seasonData
     * @return Season
     * @throws \Exception
     */
    public function createSeason(array $seasonData): Season
    {
        DB::beginTransaction();
        
        try {
            // Validate school exists
            $school = School::find($seasonData['school_id']);
            if (!$school) {
                throw new \Exception('School not found');
            }

            // Validate name uniqueness
            $this->validateSeasonNameUnique($seasonData['name'], $seasonData['school_id']);
            
            // Validate date range
            $this->validateSeasonDateRange($seasonData, $seasonData['school_id']);

            // If creating an active season, deactivate other active seasons
            if ($seasonData['is_active'] ?? false) {
                $this->validateActiveSeasonConstraints($seasonData['school_id']);
                $this->deactivateActiveSeasons($seasonData['school_id']);
            }

            // Create the season
            $season = Season::create([
                'name' => $seasonData['name'],
                'description' => $seasonData['description'] ?? null,
                'start_date' => Carbon::parse($seasonData['start_date']),
                'end_date' => Carbon::parse($seasonData['end_date']),
                'school_id' => $seasonData['school_id'],
                'is_active' => $seasonData['is_active'] ?? false,
                'max_capacity' => $seasonData['max_capacity'] ?? null,
                'price_modifier' => $seasonData['price_modifier'] ?? null,
                'created_by' => $seasonData['created_by'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Load relationships for response
            $season->load(['creator', 'school']);

            DB::commit();

            Log::info('Season created successfully', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'school_id' => $seasonData['school_id'],
                'created_by' => $seasonData['created_by']
            ]);

            return $season;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create season', [
                'error' => $e->getMessage(),
                'season_data' => $seasonData
            ]);
            
            throw $e;
        }
    }

    /**
     * Update an existing season
     * 
     * @param Season $season
     * @param array $updateData
     * @return Season
     * @throws \Exception
     */
    public function updateSeason(Season $season, array $updateData): Season
    {
        DB::beginTransaction();
        
        try {
            // Validate name uniqueness if name is being updated
            if (isset($updateData['name'])) {
                $this->validateSeasonNameUnique($updateData['name'], $season->school_id, $season->id);
            }
            
            // Validate date range if dates are being updated
            if (isset($updateData['start_date']) || isset($updateData['end_date'])) {
                $seasonData = array_merge([
                    'start_date' => $season->start_date,
                    'end_date' => $season->end_date
                ], $updateData);
                $this->validateSeasonDateRange($seasonData, $season->school_id, $season->id);
            }

            // If setting as active, validate and deactivate other active seasons
            if (($updateData['is_active'] ?? false) && !$season->is_active) {
                $this->validateActiveSeasonConstraints($season->school_id, $season->id);
                $this->deactivateActiveSeasons($season->school_id, $season->id);
            }

            // Update the season
            $season->update(array_merge($updateData, [
                'updated_at' => now()
            ]));

            // Reload relationships
            $season->load(['creator', 'closer', 'school']);

            DB::commit();

            Log::info('Season updated successfully', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'updated_fields' => array_keys($updateData)
            ]);

            return $season;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update season', [
                'season_id' => $season->id,
                'error' => $e->getMessage(),
                'update_data' => $updateData
            ]);
            
            throw $e;
        }
    }

    /**
     * Close a season (mark as closed)
     * 
     * @param Season $season
     * @param int $closedBy
     * @return Season
     * @throws \Exception
     */
    public function closeSeason(Season $season, int $closedBy): Season
    {
        DB::beginTransaction();
        
        try {
            // Check if season can be closed
            if ($season->is_closed) {
                throw new \Exception('Season is already closed');
            }

            // TODO: Check for active bookings once database schema includes season_id in bookings table
            // $activeBookingsCount = $season->bookings()
            //     ->whereIn('status', ['pending', 'confirmed'])
            //     ->count();

            // if ($activeBookingsCount > 0) {
            //     throw new \Exception("Cannot close season with {$activeBookingsCount} active bookings");
            // }

            // Close the season
            $season->update([
                'is_closed' => true,
                'is_active' => false, // Cannot be active if closed
                'closed_at' => now(),
                'closed_by' => $closedBy,
                'updated_at' => now(),
            ]);

            // Load relationships
            $season->load(['creator', 'closer', 'school']);

            DB::commit();

            Log::info('Season closed successfully', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'closed_by' => $closedBy
            ]);

            return $season;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to close season', [
                'season_id' => $season->id,
                'error' => $e->getMessage(),
                'closed_by' => $closedBy
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete a season (soft delete)
     * 
     * @param Season $season
     * @return bool
     * @throws \Exception
     */
    public function deleteSeason(Season $season): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if season can be deleted
            if ($season->is_closed || $season->is_historical) {
                throw new \Exception('Cannot delete closed or historical seasons');
            }

            // TODO: Check for associated data once database schema includes season_id in bookings/courses tables
            // $bookingsCount = $season->bookings()->count();
            // $coursesCount = $season->courses()->count();

            // if ($bookingsCount > 0) {
            //     throw new \Exception("Cannot delete season with {$bookingsCount} bookings");
            // }

            // if ($coursesCount > 0) {
            //     throw new \Exception("Cannot delete season with {$coursesCount} courses");
            // }

            // Soft delete the season
            $season->delete();

            DB::commit();

            Log::info('Season deleted successfully', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'school_id' => $season->school_id
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete season', [
                'season_id' => $season->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get season statistics for a school
     * 
     * @param int $schoolId
     * @return array
     */
    public function getSeasonStatistics(int $schoolId): array
    {
        $seasons = Season::where('school_id', $schoolId)
            // TODO: Add withCount once database schema includes season_id in bookings/courses tables
            // ->withCount(['bookings', 'courses'])
            ->get();

        return [
            'total_seasons' => $seasons->count(),
            'active_seasons' => $seasons->where('is_active', true)->count(),
            'closed_seasons' => $seasons->where('is_closed', true)->count(),
            'historical_seasons' => $seasons->where('is_historical', true)->count(),
            'current_seasons' => $seasons->filter(function ($season) {
                $now = Carbon::now();
                return $season->start_date <= $now && $season->end_date >= $now;
            })->count(),
            // TODO: Add counts once database schema includes season_id in bookings/courses tables
            'total_bookings' => 0, // $seasons->sum('bookings_count'),
            'total_courses' => 0, // $seasons->sum('courses_count'),
        ];
    }

    /**
     * Auto-close expired seasons
     * 
     * @param int|null $schoolId Optional school ID to limit scope
     * @return int Number of seasons closed
     */
    public function autoCloseExpiredSeasons(?int $schoolId = null): int
    {
        $query = Season::where('end_date', '<', Carbon::now()->subDays(7)) // 7 days grace period
            ->where('is_closed', false)
            ->where('is_historical', false);

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $expiredSeasons = $query->get();
        $closedCount = 0;

        foreach ($expiredSeasons as $season) {
            try {
                // Auto-close without checking for active bookings (they should be handled separately)
                $season->update([
                    'is_closed' => true,
                    'is_active' => false,
                    'closed_at' => now(),
                    'closed_by' => null, // System closure
                    'updated_at' => now(),
                ]);

                $closedCount++;

                Log::info('Season auto-closed', [
                    'season_id' => $season->id,
                    'season_name' => $season->name,
                    'end_date' => $season->end_date
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to auto-close season', [
                    'season_id' => $season->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $closedCount;
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Deactivate all active seasons for a school
     * 
     * @param int $schoolId
     * @param int|null $excludeSeasonId
     * @return void
     */
    private function deactivateActiveSeasons(int $schoolId, ?int $excludeSeasonId = null): void
    {
        $query = Season::where('school_id', $schoolId)
            ->where('is_active', true);

        if ($excludeSeasonId) {
            $query->where('id', '!=', $excludeSeasonId);
        }

        $activeSeasons = $query->get();

        foreach ($activeSeasons as $activeSeason) {
            $activeSeason->update([
                'is_active' => false,
                'updated_at' => now()
            ]);

            Log::info('Season deactivated', [
                'season_id' => $activeSeason->id,
                'season_name' => $activeSeason->name,
                'reason' => 'New active season created'
            ]);
        }
    }

    /**
     * Validate season date range constraints
     * 
     * @param array $seasonData
     * @param int $schoolId
     * @param int|null $excludeSeasonId
     * @throws \Exception
     */
    private function validateSeasonDateRange(array $seasonData, int $schoolId, ?int $excludeSeasonId = null): void
    {
        if (!isset($seasonData['start_date']) || !isset($seasonData['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($seasonData['start_date']);
        $endDate = Carbon::parse($seasonData['end_date']);

        // Validate minimum duration (at least 7 days)
        if ($startDate->diffInDays($endDate) < 7) {
            throw new \Exception('La temporada debe durar al menos 7 días.');
        }

        // Validate maximum duration (max 18 months)
        if ($startDate->diffInMonths($endDate) > 18) {
            throw new \Exception('La temporada no puede durar más de 18 meses.');
        }

        // Check for overlapping seasons
        $query = Season::where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                      $subQuery->where('start_date', '<=', $startDate)
                               ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeSeasonId) {
            $query->where('id', '!=', $excludeSeasonId);
        }

        if ($query->exists()) {
            throw new \Exception('Las fechas de esta temporada se solapan con una temporada existente.');
        }
    }

    /**
     * Validate active season constraints
     * 
     * @param int $schoolId
     * @param int|null $excludeSeasonId
     * @throws \Exception
     */
    private function validateActiveSeasonConstraints(int $schoolId, ?int $excludeSeasonId = null): void
    {
        $query = Season::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereNull('deleted_at');

        if ($excludeSeasonId) {
            $query->where('id', '!=', $excludeSeasonId);
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe una temporada activa. Solo puede haber una temporada activa por escuela.');
        }
    }

    /**
     * Validate season name uniqueness within school
     * 
     * @param string $name
     * @param int $schoolId
     * @param int|null $excludeSeasonId
     * @throws \Exception
     */
    private function validateSeasonNameUnique(string $name, int $schoolId, ?int $excludeSeasonId = null): void
    {
        $query = Season::where('school_id', $schoolId)
            ->where('name', $name)
            ->whereNull('deleted_at');

        if ($excludeSeasonId) {
            $query->where('id', '!=', $excludeSeasonId);
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe una temporada con este nombre en tu escuela.');
        }
    }
}