<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    // Sobrescribir el método de creación
    protected static function booted()
    {
        static::creating(function ($model) {
            // Asignar la IP al campo
            $model->ip_address = request()->ip();
        });
    }
}
