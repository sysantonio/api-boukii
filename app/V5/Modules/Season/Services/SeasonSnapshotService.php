<?php

namespace App\V5\Modules\Season\Services;

use App\V5\Modules\Season\Repositories\SeasonSnapshotRepository;
use App\V5\Services\BaseService;
use App\V5\Models\Season;
use App\V5\Models\SeasonSnapshot;

class SeasonSnapshotService extends BaseService
{
    public function __construct(SeasonSnapshotRepository $repository)
    {
        parent::__construct($repository);
    }

    public function createImmutableSnapshot(Season $season, string $type, array $data, array $extra = []): SeasonSnapshot
    {
        /** @var SeasonSnapshotRepository $repo */
        $repo = $this->repository;
        $extra['is_immutable'] = true;
        return $repo->createSeasonSnapshot($season, $type, $data, $extra);
    }

    public function validateSnapshot(SeasonSnapshot $snapshot): bool
    {
        return $snapshot->verifyIntegrity();
    }
}
