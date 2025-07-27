<?php

namespace App\Http\Requests\Admin\V3;

use InfyOm\Generator\Request\APIRequest;

class DashboardWeatherRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'school_id' => 'nullable|integer|exists:schools,id',
            'station_id' => 'nullable|integer|exists:stations,id',
        ];
    }
}
