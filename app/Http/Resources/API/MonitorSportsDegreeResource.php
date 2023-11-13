<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class MonitorSportsDegreeResource extends JsonResource
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
            'sport_id' => $this->sport_id,
            'school_id' => $this->school_id,
            'degree_id' => $this->degree_id,
            'monitor_id' => $this->monitor_id,
            'salary_level' => $this->salary_level,
            'allow_adults' => $this->allow_adults,
            'is_default' => $this->is_default,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
