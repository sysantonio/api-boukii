<?php

namespace App\V5\Modules\Season\Repositories;

use App\V5\Models\Season;
use App\V5\Models\SeasonSnapshot;
use App\V5\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class SeasonSnapshotRepository extends BaseRepository
{
    public function __construct(SeasonSnapshot $model = null)
    {
        parent::__construct($model ?? new SeasonSnapshot());
    }

    public function find(int $id): ?SeasonSnapshot
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): SeasonSnapshot
    {
        return $this->model->newQuery()->create($data);
    }

    public function findBySeason(int $seasonId): Collection
    {
        return $this->model->newQuery()
            ->where('season_id', $seasonId)
            ->orderByDesc('snapshot_date')
            ->get();
    }

    public function createSeasonSnapshot(Season $season, string $type, array $data, array $extra = []): SeasonSnapshot
    {
        $payload = array_merge([
            'season_id' => $season->id,
            'snapshot_type' => $type,
            'snapshot_data' => $data,
            'snapshot_date' => now(),
        ], $extra);

        return $this->create($payload);
    }
}
