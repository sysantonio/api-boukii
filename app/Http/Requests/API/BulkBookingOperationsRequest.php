<?php

namespace App\Http\Requests\API;

use InfyOm\Generator\Request\APIRequest;

class BulkBookingOperationsRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'operations' => 'required|array|min:1',
            'operations.*.type' => 'required|string|in:update,cancel,reschedule,notify,refund',
            'operations.*.bookingIds' => 'required|array|min:1',
            'operations.*.bookingIds.*' => 'integer',
            'operations.*.parameters' => 'sometimes|array',
            'operations.*.conditions' => 'sometimes|array',
            'options.parallel' => 'required|boolean',
            'options.rollbackOnError' => 'required|boolean',
            'options.generateReport' => 'required|boolean',
        ];
    }
}
