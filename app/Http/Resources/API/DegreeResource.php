<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class DegreeResource extends JsonResource
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
            'league' => $this->league,
            'level' => $this->level,
            'name' => $this->name,
            'annotation' => $this->annotation,
            'degree_order' => $this->degree_order,
            'progress' => $this->progress,
            'color' => $this->color,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'active' => $this->active,
            'school_id' => $this->school_id,
            'sport_id' => $this->sport_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
