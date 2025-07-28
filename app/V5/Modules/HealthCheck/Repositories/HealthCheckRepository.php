<?php

namespace App\V5\Modules\HealthCheck\Repositories;

use App\V5\Repositories\BaseRepository;

class HealthCheckRepository extends BaseRepository
{
    public function status(): array
    {
        return ['status' => 'ok'];
    }
}
