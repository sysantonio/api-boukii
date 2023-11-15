<?php

namespace App\Http\Resources\Teach;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeAgendaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $bookings = $this->resource['bookings'] ?? [];
        $nwd = $this->resource['nwd'] ?? [];

        return [
            'bookings' => $bookings,
            'nwd' => $nwd,
            // Aquí puedes añadir más campos si es necesario
        ];
    }
}
