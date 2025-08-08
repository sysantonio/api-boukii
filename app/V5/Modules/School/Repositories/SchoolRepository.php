<?php

namespace App\V5\Modules\School\Repositories;

use App\Models\School;
use App\V5\Models\SchoolSeasonSettings;
use App\V5\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class SchoolRepository extends BaseRepository
{
    public function __construct(School $model)
    {
        parent::__construct($model);
    }

    /**
     * Get schools filtered by season with their season settings
     */
    public function getSchoolsBySeason(int $seasonId): Collection
    {
        return $this->model
            ->with(['schoolSeasonSettings' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId);
            }])
            ->whereHas('seasons', function ($query) use ($seasonId) {
                $query->where('seasons.id', $seasonId);
            })
            ->get();
    }

    /**
     * Get school with current season context
     */
    public function getSchoolWithCurrentSeason(int $schoolId): ?School
    {
        return $this->model
            ->with(['currentSeason', 'schoolSeasonSettings'])
            ->find($schoolId);
    }

    /**
     * Get schools for a user in a specific season
     */
    public function getSchoolsForUserInSeason(int $userId, int $seasonId): Collection
    {
        return $this->model
            ->with(['schoolSeasonSettings' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId);
            }])
            ->whereHas('userSeasonRoles', function ($query) use ($userId, $seasonId) {
                $query->where('user_id', $userId)
                    ->where('season_id', $seasonId);
            })
            ->get();
    }

    /**
     * Update school season settings
     */
    public function updateSeasonSettings(int $schoolId, int $seasonId, array $settings): void
    {
        foreach ($settings as $key => $value) {
            SchoolSeasonSettings::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'season_id' => $seasonId,
                    'key' => $key,
                ],
                ['value' => $value]
            );
        }
    }

    /**
     * Get school season settings
     */
    public function getSeasonSettings(int $schoolId, int $seasonId): Collection
    {
        return SchoolSeasonSettings::where('school_id', $schoolId)
            ->where('season_id', $seasonId)
            ->get();
    }
}
