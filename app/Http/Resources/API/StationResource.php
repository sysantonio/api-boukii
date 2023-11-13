<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class StationResource extends JsonResource
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
            'name' => $this->name,
            'cp' => $this->cp,
            'city' => $this->city,
            'country' => $this->country,
            'province' => $this->province,
            'address' => $this->address,
            'image' => $this->image,
            'map' => $this->map,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'num_hanger' => $this->num_hanger,
            'num_chairlift' => $this->num_chairlift,
            'num_cabin' => $this->num_cabin,
            'num_cabin_large' => $this->num_cabin_large,
            'num_fonicular' => $this->num_fonicular,
            'show_details' => $this->show_details,
            'active' => $this->active,
            'accuweather' => $this->accuweather,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
