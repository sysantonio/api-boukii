<?php

namespace App\Services\Admin\V3;

use Illuminate\Http\Request;

class ReservationService
{
    public function getReservationData(Request $request): array
    {
        return [
            'pending' => 5,
            'confirmed' => 20,
            'cancelled' => 2,
        ];
    }
}
