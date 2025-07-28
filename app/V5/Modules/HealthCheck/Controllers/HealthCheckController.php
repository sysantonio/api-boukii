<?php

namespace App\V5\Modules\HealthCheck\Controllers;

use App\V5\BaseV5Controller;
use App\V5\Modules\HealthCheck\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends BaseV5Controller
{
    public function __construct(HealthCheckService $service)
    {
        parent::__construct($service);
    }

    public function index(): JsonResponse
    {
        $data = $this->service->check();
        return $this->respond($data);
    }
}
