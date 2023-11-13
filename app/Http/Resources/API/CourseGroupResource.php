<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseGroupResource extends JsonResource
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
            'course_date_id' => $this->course_date_id,
            'degree_id' => $this->degree_id,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'recommended_age' => $this->recommended_age,
            'teachers_min' => $this->teachers_min,
            'teachers_max' => $this->teachers_max,
            'observations' => $this->observations,
            'teacher_min_degree' => $this->teacher_min_degree,
            'auto' => $this->auto,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
