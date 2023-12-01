<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'course_type' => $this->course_type,
            'is_flexible' => $this->is_flexible,
            'sport_id' => $this->sport_id,
            'school_id' => $this->school_id,
            'station_id' => $this->station_id,
            'name' => $this->name,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'max_participants' => $this->max_participants,
            'duration' => $this->duration,
            'duration_flexible' => $this->duration_flexible,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'date_start_res' => $this->date_start_res,
            'date_end_res' => $this->date_end_res,
            'hour_min' => $this->hour_min,
            'hour_max' => $this->hour_max,
            'confirm_attendance' => $this->confirm_attendance,
            'active' => $this->active,
            'online' => $this->online,
            'image' => $this->image,
            'translations' => $this->translations,
            'price_range' => $this->price_range,
            'discounts' => $this->discounts,
            'settings' => $this->settings,
            'sport' => $this->sport,
            'course_dates' => $this->courseDates,
            'course_extras' => $this->courseExtras,
            'total_reservations' => $this->total_reservations,
            'total_available_places' => $this->total_available_places,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
