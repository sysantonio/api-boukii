<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseDateResource extends JsonResource
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
            'course_id' => $this->course_id,
            'date' => $this->date,
            'hour_start' => $this->hour_start,
            'hour_end' => $this->hour_end,
            'groups' => CourseGroupResource::collection($this->whenLoaded('courseGroups')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
