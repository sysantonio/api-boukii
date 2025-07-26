<?php

namespace App\Http\Requests\API;

use InfyOm\Generator\Request\APIRequest;

class DuplicateSmartBookingRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'modifications' => 'sometimes|array',
            'modifications.clientId' => 'sometimes|integer',
            'modifications.dates' => 'sometimes|array',
            'modifications.dates.*' => 'string',
            'modifications.participantCount' => 'sometimes|integer',
            'options.optimizeForNewDate' => 'required|boolean',
            'options.suggestBestSlots' => 'required|boolean',
            'options.applyCurrentPricing' => 'required|boolean',
            'options.copyNotes' => 'required|boolean',
        ];
    }
}
