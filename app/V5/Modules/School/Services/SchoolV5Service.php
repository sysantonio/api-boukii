<?php

namespace App\V5\Modules\School\Services;

use App\V5\Modules\School\Repositories\SchoolRepository;
use App\V5\Services\BaseService;

class SchoolV5Service extends BaseService
{
    public function __construct(SchoolRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Retrieve schools for the given season including season settings.
     */
    public function listBySeason(int $seasonId): array
    {
        if (! $seasonId) {
            return [];
        }

        /** @var SchoolRepository $repo */
        $repo = $this->repository;

        return $repo->getSchoolsBySeason($seasonId)->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'current_season_id' => $school->current_season_id,
                'season_settings' => $school->schoolSeasonSettings->map(function ($setting) {
                    return [
                        'key' => $setting->key,
                        'value' => $setting->value,
                    ];
                }),
            ];
        })->toArray();
    }

    /**
     * Get schools for a user in a specific season
     */
    public function getSchoolsForUserInSeason(int $userId, int $seasonId): array
    {
        /** @var SchoolRepository $repo */
        $repo = $this->repository;

        return $repo->getSchoolsForUserInSeason($userId, $seasonId)->toArray();
    }

    /**
     * Update season settings for a school
     */
    public function updateSeasonSettings(int $schoolId, int $seasonId, array $settings): void
    {
        /** @var SchoolRepository $repo */
        $repo = $this->repository;
        $repo->updateSeasonSettings($schoolId, $seasonId, $settings);
    }
}
