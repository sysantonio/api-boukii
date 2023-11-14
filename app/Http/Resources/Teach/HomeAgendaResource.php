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
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'client_id' => $this->client_id,
            'price' => $this->price,
            'currency' => $this->currency,
            'course_subgroup_id' => $this->course_subgroup_id,
            'course_id' => $this->course_id,
            'course_date_id' => $this->course_date_id,
            'degree_id' => $this->degree_id,
            'course_group_id' => $this->course_group_id,
            'monitor_id' => $this->monitor_id,
            'date' => $this->date,
            'hour_start' => $this->hour_start,
            'hour_end' => $this->hour_end,
            'attended' => $this->attended,
            'color' => $this->color,
            'booking' => $this->booking,
            'course' => $this->course,
            'client' => $this->client,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
