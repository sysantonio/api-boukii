<?php

namespace App\V5\Modules\Booking\Requests;

use App\V5\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Booking Status Request Validation
 */
class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', Booking::getValidStatuses())
            ],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status. Valid statuses are: ' . implode(', ', Booking::getValidStatuses()),
            'reason.max' => 'Reason cannot exceed 500 characters',
            'notes.max' => 'Notes cannot exceed 1000 characters',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'booking status',
            'reason' => 'status change reason',
            'notes' => 'additional notes',
        ];
    }
}