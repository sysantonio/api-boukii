<?php

namespace App\V5\Services;

use App\V5\Repositories\BaseRepository;

class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }
}
