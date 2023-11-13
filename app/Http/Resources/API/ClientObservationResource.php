<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientObservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'general' => $this->general,
            'notes' => $this->notes,
            'historical' => $this->historical,
            'client_id' => $this->client_id,
            'school_id' => $this->school_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
