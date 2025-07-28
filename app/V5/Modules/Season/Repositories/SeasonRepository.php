<?php

namespace App\V5\Modules\Season\Repositories;

use App\V5\Models\Season;
use App\V5\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class SeasonRepository extends BaseRepository
{
    public function __construct(Season $model = null)
    {
        parent::__construct($model ?? new Season());
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function find(int $id): ?Season
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Season
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Season $season, array $data): Season
    {
        $season->fill($data);
        $season->save();
        return $season;
    }

    public function delete(Season $season): bool
    {
        return (bool) $season->delete();
    }

    public function findBySeason(int $id): ?Season
    {
        return $this->find($id);
    }

    public function getCurrentSeason(int $schoolId): ?Season
    {
        return $this->model->newQuery()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->first();
    }
}
