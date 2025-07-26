<?php

namespace App\Http\Requests\API;

use App\Models\BookingDraft;
use InfyOm\Generator\Request\APIRequest;

class BookingDraftRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return BookingDraft::$rules;
    }
}
