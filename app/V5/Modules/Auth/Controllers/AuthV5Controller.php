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

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->respondUnauthorized('Usuario no autenticado');
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'school_id' => $user->getCurrentSchoolId(),
            'school' => $user->getCurrentSchool()?->toArray(),
            'authenticated' => true,
            'token_valid' => true
        ];

        return $this->respond($userData);
    }
}
