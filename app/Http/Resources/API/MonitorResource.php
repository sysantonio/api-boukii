<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class MonitorResource extends JsonResource
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
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birth_date' => $this->birth_date,
            'phone' => $this->phone,
            'telephone' => $this->telephone,
            'address' => $this->address,
            'cp' => $this->cp,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'language1_id' => $this->language1_id,
            'language2_id' => $this->language2_id,
            'language3_id' => $this->language3_id,
            'image' => $this->image,
            'avs' => $this->avs,
            'work_license' => $this->work_license,
            'bank_details' => $this->bank_details,
            'children' => $this->children,
            'civil_status' => $this->civil_status,
            'family_allowance' => $this->family_allowance,
            'partner_work_license' => $this->partner_work_license,
            'partner_works' => $this->partner_works,
            'partner_percentaje' => $this->partner_percentaje,
            'user_id' => $this->user_id,
            'active_school' => $this->active_school,
            'active_station' => $this->active_station,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
