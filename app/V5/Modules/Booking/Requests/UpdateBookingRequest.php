<?php

namespace App\V5\Modules\Booking\Requests;

use App\V5\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Booking Request Validation
 */
class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            // Basic booking info (all optional for updates)
            'type' => ['sometimes', 'string', 'in:' . implode(',', Booking::getValidTypes())],
            'client_id' => ['sometimes', 'integer', 'exists:clients,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'monitor_id' => ['nullable', 'integer', 'exists:monitors,id'],
            
            // Schedule
            'start_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            
            // Participants
            'participants' => ['sometimes', 'array', 'min:1', 'max:20'],
            'participants.*.first_name' => ['required_with:participants', 'string', 'max:100'],
            'participants.*.last_name' => ['required_with:participants', 'string', 'max:100'],
            'participants.*.date_of_birth' => ['required_with:participants', 'date', 'before:today'],
            'participants.*.level' => ['required_with:participants', 'string', 'in:Principiante,Intermedio,Avanzado,Experto'],
            'participants.*.special_requests' => ['nullable', 'string', 'max:500'],
            'participants.*.medical_conditions' => ['nullable', 'string', 'max:500'],
            'participants.*.emergency_contact' => ['required_with:participants', 'array'],
            'participants.*.emergency_contact.name' => ['required_with:participants.*.emergency_contact', 'string', 'max:100'],
            'participants.*.emergency_contact.phone' => ['required_with:participants.*.emergency_contact', 'string', 'max:20'],
            'participants.*.emergency_contact.relationship' => ['required_with:participants.*.emergency_contact', 'string', 'max:50'],
            
            // Extras (can be updated/synced)
            'extras' => ['sometimes', 'array'],
            'extras.*.id' => ['nullable', 'integer'], // For existing extras
            'extras.*.extra_type' => ['required_with:extras', 'string', 'in:insurance,equipment,transport,meal,photo,video,certificate,special_service,other'],
            'extras.*.name' => ['required_with:extras', 'string', 'max:100'],
            'extras.*.description' => ['nullable', 'string', 'max:500'],
            'extras.*.unit_price' => ['required_with:extras', 'numeric', 'min:0'],
            'extras.*.quantity' => ['required_with:extras', 'integer', 'min:1', 'max:100'],
            'extras.*.is_required' => ['boolean'],
            'extras.*.is_active' => ['boolean'],
            
            // Equipment (can be updated/synced)
            'equipment' => ['sometimes', 'array'],
            'equipment.*.id' => ['nullable', 'integer'], // For existing equipment
            'equipment.*.equipment_type' => ['required_with:equipment', 'string', 'in:skis,boots,poles,helmet,goggles,snowboard,bindings,clothing,protection,other'],
            'equipment.*.name' => ['required_with:equipment', 'string', 'max:100'],
            'equipment.*.brand' => ['nullable', 'string', 'max:50'],
            'equipment.*.model' => ['nullable', 'string', 'max:50'],
            'equipment.*.size' => ['nullable', 'string', 'max:20'],
            'equipment.*.participant_name' => ['required_with:equipment', 'string', 'max:200'],
            'equipment.*.participant_index' => ['required_with:equipment', 'integer', 'min:0'],
            'equipment.*.daily_rate' => ['required_with:equipment', 'numeric', 'min:0'],
            'equipment.*.rental_days' => ['required_with:equipment', 'integer', 'min:1', 'max:30'],
            
            // Optional fields
            'has_insurance' => ['boolean'],
            'has_equipment' => ['boolean'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            
            // Booking data (for wizard state)
            'booking_data' => ['sometimes', 'array'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Invalid booking type. Must be: course, activity, or material',
            'client_id.exists' => 'Selected client does not exist',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.date_format' => 'End time must be in HH:MM format',
            'end_time.after' => 'End time must be after start time',
            'participants.min' => 'At least one participant is required',
            'participants.max' => 'Maximum 20 participants allowed',
            'course_id.exists' => 'Selected course does not exist',
            'monitor_id.exists' => 'Selected monitor does not exist',
        ];
    }

    /**
     * Configure the validator instance
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate course is required for course type bookings
            if ($this->input('type') === 'course' && $this->input('course_id') === null && !$this->has('course_id')) {
                $validator->errors()->add('course_id', 'Course selection is required for course bookings');
            }

            // Validate equipment participant indices
            if ($this->has('equipment') && $this->has('participants')) {
                $participantCount = count($this->input('participants', []));
                foreach ($this->input('equipment', []) as $index => $equipment) {
                    $participantIndex = $equipment['participant_index'] ?? -1;
                    if ($participantIndex >= $participantCount) {
                        $validator->errors()->add(
                            "equipment.{$index}.participant_index",
                            'Equipment participant index exceeds number of participants'
                        );
                    }
                }
            }
        });
    }
}