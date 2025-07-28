<?php

namespace App\V5\Modules\Season\Controllers;

use App\V5\BaseV5Controller;
use App\V5\Modules\Season\Services\SeasonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonController extends BaseV5Controller
{
    public function __construct(SeasonService $service)
    {
        parent::__construct($service);
    }

    public function index(): JsonResponse
    {
        $data = $this->service->all();
        return $this->respond($data->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $season = $this->service->createSeason($request->all());
        return $this->respond($season->toArray(), 201);
    }

    public function show(int $id): JsonResponse
    {
        $season = $this->service->find($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $season = $this->service->updateSeason($id, $request->all());
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->deleteSeason($id);
        if (!$deleted) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond(['deleted' => true]);
    }

    public function current(Request $request): JsonResponse
    {
        $season = $this->service->getCurrentSeason($request->get('school_id'));
        return $this->respond($season?->toArray() ?? []);
    }

    public function close(int $id): JsonResponse
    {
        $season = $this->service->closeSeason($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    public function clone(int $id): JsonResponse
    {
        $season = $this->service->cloneSeason($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray(), 201);
    }
}
