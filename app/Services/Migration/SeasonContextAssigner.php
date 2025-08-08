<?php

namespace App\Services\Migration;

use App\Models\Season;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeasonContextAssigner
{
    public function assignSeasonToUser($legacyUser)
    {
        // Strategy 1: Find season by user creation date
        if ($legacyUser->created_at) {
            $season = $this->findSeasonByDate($legacyUser->school_id, $legacyUser->created_at);
            if ($season) {
                Log::channel('migration')->debug('Assigned season by creation date', [
                    'user_id' => $legacyUser->id,
                    'season_id' => $season->id,
                    'strategy' => 'creation_date'
                ]);
                return $season->id;
            }
        }

        // Strategy 2: Find most recent season for the school
        $season = $this->getRecentSeasonForSchool($legacyUser->school_id);
        if ($season) {
            Log::channel('migration')->debug('Assigned recent season', [
                'user_id' => $legacyUser->id,
                'season_id' => $season->id,
                'strategy' => 'recent_season'
            ]);
            return $season->id;
        }

        // Strategy 3: Create or get default legacy season
        $seasonId = $this->getOrCreateLegacySeason($legacyUser->school_id);
        Log::channel('migration')->debug('Assigned legacy season', [
            'user_id' => $legacyUser->id,
            'season_id' => $seasonId,
            'strategy' => 'legacy_season'
        ]);
        return $seasonId;
    }

    public function assignSeasonToCourse($legacyCourse)
    {
        // Strategy 1: Find season by course date range
        if ($legacyCourse->date_start && $legacyCourse->date_end) {
            $season = $this->findSeasonByDateRange(
                $legacyCourse->school_id,
                $legacyCourse->date_start,
                $legacyCourse->date_end
            );
            if ($season) {
                return $season->id;
            }
        }

        // Strategy 2: Find season by course start date
        if ($legacyCourse->date_start) {
            $season = $this->findSeasonByDate($legacyCourse->school_id, $legacyCourse->date_start);
            if ($season) {
                return $season->id;
            }
        }

        // Strategy 3: Find season by creation date
        if ($legacyCourse->created_at) {
            $season = $this->findSeasonByDate($legacyCourse->school_id, $legacyCourse->created_at);
            if ($season) {
                return $season->id;
            }
        }

        // Fallback: Create or get legacy season
        return $this->getOrCreateLegacySeason($legacyCourse->school_id);
    }

    public function assignSeasonToBooking($legacyBooking)
    {
        // Strategy 1: Find season by booking date
        if ($legacyBooking->date_start) {
            $season = $this->findSeasonByDate($legacyBooking->school_id, $legacyBooking->date_start);
            if ($season) {
                return $season->id;
            }
        }

        // Strategy 2: Find season by creation date
        if ($legacyBooking->created_at) {
            $season = $this->findSeasonByDate($legacyBooking->school_id, $legacyBooking->created_at);
            if ($season) {
                return $season->id;
            }
        }

        // Strategy 3: Try to get season from related course
        if ($legacyBooking->course_id) {
            $legacyCourse = DB::connection('old')->table('courses2')->find($legacyBooking->course_id);
            if ($legacyCourse) {
                return $this->assignSeasonToCourse($legacyCourse);
            }
        }

        // Fallback: Create or get legacy season
        return $this->getOrCreateLegacySeason($legacyBooking->school_id);
    }

    public function createDefaultSeason($schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            throw new \Exception("School not found: {$schoolId}");
        }

        // Check if a legacy season already exists
        $existingSeason = Season::where('school_id', $schoolId)
                                ->where('name', 'LIKE', 'Legacy Season%')
                                ->first();

        if ($existingSeason) {
            return $existingSeason;
        }

        // Get date range from legacy data
        $dateRange = $this->getLegacyDataDateRange($schoolId);

        $season = Season::create([
            'school_id' => $schoolId,
            'name' => 'Legacy Season ' . date('Y'),
            'start_date' => $dateRange['start'] ?? '2020-01-01',
            'end_date' => $dateRange['end'] ?? '2030-12-31',
            'is_active' => false,
            'hour_start' => '08:00',
            'hour_end' => '18:00',
            'vacation_days' => json_encode([])
        ]);

        Log::channel('migration')->info('Created default legacy season', [
            'school_id' => $schoolId,
            'season_id' => $season->id,
            'date_range' => $dateRange
        ]);

        return $season;
    }

    private function findSeasonByDate($schoolId, $date)
    {
        $carbonDate = Carbon::parse($date);
        
        return Season::where('school_id', $schoolId)
                     ->where('start_date', '<=', $carbonDate)
                     ->where('end_date', '>=', $carbonDate)
                     ->first();
    }

    private function findSeasonByDateRange($schoolId, $startDate, $endDate)
    {
        $carbonStart = Carbon::parse($startDate);
        $carbonEnd = Carbon::parse($endDate);

        // Find season that overlaps with the given date range
        return Season::where('school_id', $schoolId)
                     ->where(function ($query) use ($carbonStart, $carbonEnd) {
                         $query->whereBetween('start_date', [$carbonStart, $carbonEnd])
                               ->orWhereBetween('end_date', [$carbonStart, $carbonEnd])
                               ->orWhere(function ($q) use ($carbonStart, $carbonEnd) {
                                   $q->where('start_date', '<=', $carbonStart)
                                     ->where('end_date', '>=', $carbonEnd);
                               });
                     })
                     ->first();
    }

    private function getRecentSeasonForSchool($schoolId)
    {
        return Season::where('school_id', $schoolId)
                     ->orderBy('start_date', 'desc')
                     ->first();
    }

    private function getOrCreateLegacySeason($schoolId)
    {
        $season = Season::where('school_id', $schoolId)
                        ->where('name', 'LIKE', 'Legacy Season%')
                        ->first();

        if (!$season) {
            $season = $this->createDefaultSeason($schoolId);
        }

        return $season->id;
    }

    private function getLegacyDataDateRange($schoolId)
    {
        $ranges = [];

        try {
            // Get date range from users
            $userRange = DB::connection('old')
                          ->table('users')
                          ->where('school_id', $schoolId)
                          ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
                          ->first();
            
            if ($userRange && $userRange->min_date) {
                $ranges[] = ['start' => $userRange->min_date, 'end' => $userRange->max_date];
            }

            // Get date range from courses
            $courseRange = DB::connection('old')
                            ->table('courses2')
                            ->where('school_id', $schoolId)
                            ->selectRaw('MIN(date_start) as min_date, MAX(date_end) as max_date')
                            ->first();
            
            if ($courseRange && $courseRange->min_date) {
                $ranges[] = ['start' => $courseRange->min_date, 'end' => $courseRange->max_date];
            }

            // Get date range from bookings
            $bookingRange = DB::connection('old')
                             ->table('bookings2')
                             ->where('school_id', $schoolId)
                             ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
                             ->first();
            
            if ($bookingRange && $bookingRange->min_date) {
                $ranges[] = ['start' => $bookingRange->min_date, 'end' => $bookingRange->max_date];
            }

        } catch (\Exception $e) {
            Log::channel('migration')->warning('Could not determine legacy date range', [
                'school_id' => $schoolId,
                'error' => $e->getMessage()
            ]);
        }

        if (empty($ranges)) {
            return ['start' => '2020-01-01', 'end' => '2030-12-31'];
        }

        // Find overall min and max dates
        $allStartDates = array_column($ranges, 'start');
        $allEndDates = array_column($ranges, 'end');

        return [
            'start' => min($allStartDates),
            'end' => max($allEndDates)
        ];
    }

    public function getSeasonAssignmentReport($schoolId = null)
    {
        $query = DB::table('legacy_id_mappings as lim')
                   ->join('seasons as s', 's.id', '=', 'lim.additional_data->season_id')
                   ->join('schools as sch', 'sch.id', '=', 's.school_id')
                   ->select(
                       'lim.entity_type',
                       'sch.name as school_name',
                       's.name as season_name',
                       DB::raw('COUNT(*) as record_count')
                   )
                   ->groupBy('lim.entity_type', 'sch.name', 's.name');

        if ($schoolId) {
            $query->where('s.school_id', $schoolId);
        }

        return $query->get();
    }

    public function validateSeasonAssignments()
    {
        $issues = [];

        // Check for records without season assignment
        $entitiesWithSeasons = ['users', 'courses', 'bookings'];
        
        foreach ($entitiesWithSeasons as $entity) {
            $unassigned = DB::table($entity)
                           ->whereNull('season_id')
                           ->count();
            
            if ($unassigned > 0) {
                $issues[] = [
                    'type' => 'missing_season',
                    'entity' => $entity,
                    'count' => $unassigned
                ];
            }
        }

        // Check for orphaned season references
        $orphanedSeasons = DB::table('users')
                            ->leftJoin('seasons', 'users.season_id', '=', 'seasons.id')
                            ->whereNotNull('users.season_id')
                            ->whereNull('seasons.id')
                            ->count();

        if ($orphanedSeasons > 0) {
            $issues[] = [
                'type' => 'orphaned_season_reference',
                'entity' => 'users',
                'count' => $orphanedSeasons
            ];
        }

        return $issues;
    }
}