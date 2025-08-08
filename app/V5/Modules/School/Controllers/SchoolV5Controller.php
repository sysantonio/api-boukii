<?php

namespace App\V5\Modules\School\Controllers;

use App\V5\BaseV5Controller;
use App\V5\Modules\School\Services\SchoolV5Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolV5Controller extends BaseV5Controller
{
    public function __construct(SchoolV5Service $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $seasonId = (int) $request->get('season_id');
        $data = $this->service->listBySeason($seasonId);

        return $this->respond($data);
    }
}
