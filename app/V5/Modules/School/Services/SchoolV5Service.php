<?php

namespace App\V5\Modules\School\Services;

use App\Models\School;
use App\V5\Services\BaseService;

class SchoolV5Service extends BaseService
{
    /**
     * Retrieve schools for the given season including season settings.
     */
    public function listBySeason(int $seasonId): array
    {
        if (!$seasonId) {
            return [];
        }

        return School::query()
            ->select('schools.*')
            ->join('seasons', 'seasons.school_id', '=', 'schools.id')
            ->where('seasons.id', $seasonId)
            ->with([
                'seasonSettings' => function ($query) use ($seasonId) {
                    $query->where('season_id', $seasonId);
                },
            ])
            ->get()
            ->toArray();
    }
}
