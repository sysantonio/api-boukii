<?php

namespace App\Http\Requests\API;

use InfyOm\Generator\Request\APIRequest;

class ValidateWizardStepRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'step' => 'required|integer|min:1',
            'data' => 'required|array',
            'context.sessionId' => 'required|string',
        ];
    }
}
