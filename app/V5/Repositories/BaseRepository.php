<?php

namespace App\V5\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected ?Model $model;

    public function __construct(?Model $model = null)
    {
        $this->model = $model;
    }
}
