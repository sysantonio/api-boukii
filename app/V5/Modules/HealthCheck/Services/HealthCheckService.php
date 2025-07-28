<?php

namespace App\V5\Modules\HealthCheck\Services;

use App\V5\Services\BaseService;
use App\V5\Modules\HealthCheck\Repositories\HealthCheckRepository;

class HealthCheckService extends BaseService
{
    public function __construct(HealthCheckRepository $repository)
    {
        parent::__construct($repository);
    }

    public function check(): array
    {
        /** @var HealthCheckRepository $repo */
        $repo = $this->repository;
        return $repo->status();
    }
}
