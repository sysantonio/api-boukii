<?php

namespace App\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\V5\Services\BaseService;

class BaseV5Controller extends Controller
{
    protected BaseService $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    protected function respond(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }
}
