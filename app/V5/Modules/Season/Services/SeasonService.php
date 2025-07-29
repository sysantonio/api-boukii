<?php

namespace App\V5\Modules\Season\Services;

use App\V5\Modules\Season\Repositories\SeasonRepository;
use App\V5\Services\BaseService;
use App\V5\Models\Season;
use Illuminate\Support\Collection;

class SeasonService extends BaseService
{
    public function __construct(SeasonRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return Collection<int,Season>
     */
    public function all(): Collection
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        return $repo->all();
    }

    public function find(int $id): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        return $repo->find($id);
    }

    public function createSeason(array $data): Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        return $repo->create($data);
    }

    public function updateSeason(int $id, array $data): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        $season = $repo->find($id);
        if (!$season) {
            return null;
        }
        return $repo->update($season, $data);
    }

    public function deleteSeason(int $id): bool
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        $season = $repo->find($id);
        return $season ? $repo->delete($season) : false;
    }

    public function cloneSeason(int $id): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        $season = $repo->find($id);
        if (!$season) {
            return null;
        }
        $data = $season->replicate()->toArray();
        unset($data['id']);
        $data['is_active'] = false;
        return $repo->create($data);
    }

    public function closeSeason(int $id): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        $season = $repo->find($id);
        if (!$season) {
            return null;
        }
        $repo->update($season, [
            'is_active' => false,
            'is_closed' => true,
            'closed_at' => now(),
        ]);
        return $season->fresh();
    }

    public function activateSeason(int $id): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        $season = $repo->find($id);
        if (!$season) {
            return null;
        }
        $repo->update($season, ['is_active' => true]);
        return $season->fresh();
    }

    public function getCurrentSeason(int $schoolId): ?Season
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        return $repo->getCurrentSeason($schoolId);
    }

    /**
     * @return Collection<int,Season>
     */
    public function getActiveSeasons(int $schoolId): Collection
    {
        /** @var SeasonRepository $repo */
        $repo = $this->repository;
        return $repo->getActiveSeasons($schoolId);
    }
}
