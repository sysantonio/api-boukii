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

    /**
     * @OA\Get(
     *     path="/api/v5/seasons",
     *     tags={"Season"},
     *     summary="List seasons",
     *     @OA\Response(response=200, description="List of seasons")
     * )
     */
    public function index(): JsonResponse
    {
        $data = $this->service->all();
        return $this->respond($data->toArray());
    }

    /**
     * @OA\Post(
     *     path="/api/v5/seasons",
     *     tags={"Season"},
     *     summary="Create season",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/V5Season")),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $season = $this->service->createSeason($request->all());
        return $this->respond($season->toArray(), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v5/seasons/{id}",
     *     tags={"Season"},
     *     summary="Show season",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Season data"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $season = $this->service->find($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    /**
     * @OA\Put(
     *     path="/api/v5/seasons/{id}",
     *     tags={"Season"},
     *     summary="Update season",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/V5Season")),
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $season = $this->service->updateSeason($id, $request->all());
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    /**
     * @OA\Delete(
     *     path="/api/v5/seasons/{id}",
     *     tags={"Season"},
     *     summary="Delete season",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->deleteSeason($id);
        if (!$deleted) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond(['deleted' => true]);
    }

    /**
     * @OA\Get(
     *     path="/api/v5/seasons/current",
     *     tags={"Season"},
     *     summary="Current season",
     *     @OA\Parameter(name="school_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Season data")
     * )
     */
    public function current(Request $request): JsonResponse
    {
        $season = $this->service->getCurrentSeason($request->get('school_id'));
        return $this->respond($season?->toArray() ?? []);
    }

    /**
     * @OA\Post(
     *     path="/api/v5/seasons/{id}/close",
     *     tags={"Season"},
     *     summary="Close season",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Closed"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function close(int $id): JsonResponse
    {
        $season = $this->service->closeSeason($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray());
    }

    /**
     * @OA\Post(
     *     path="/api/v5/seasons/{id}/clone",
     *     tags={"Season"},
     *     summary="Clone season",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Cloned"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function clone(int $id): JsonResponse
    {
        $season = $this->service->cloneSeason($id);
        if (!$season) {
            return $this->respond(['message' => 'Season not found'], 404);
        }
        return $this->respond($season->toArray(), 201);
    }
}
