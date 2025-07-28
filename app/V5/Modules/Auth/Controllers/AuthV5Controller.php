<?php

namespace App\V5\Modules\Auth\Controllers;

use App\V5\BaseV5Controller;
use App\V5\Modules\Auth\Services\AuthV5Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthV5Controller extends BaseV5Controller
{
    public function __construct(AuthV5Service $service)
    {
        parent::__construct($service);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $this->service->loginWithSeasonContext($request->all());
        return $this->respond($data);
    }

    public function permissions(Request $request): JsonResponse
    {
        $userId = $request->user()->id ?? 0;
        $seasonId = (int) $request->get('season_id');
        $data = $this->service->checkSeasonPermissions($userId, $seasonId);
        return $this->respond($data);
    }

    public function switch(Request $request): JsonResponse
    {
        $userId = $request->user()->id ?? 0;
        $seasonId = (int) $request->get('season_id');
        $this->service->assignSeasonRole($userId, $seasonId, 'active');
        return $this->respond(['switched' => true]);
    }
}
