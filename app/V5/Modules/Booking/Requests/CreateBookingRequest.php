<?php

namespace App\V5\Modules\Booking\Requests;

use App\V5\Modules\Booking\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Booking Request Validation
 */
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            // Basic booking info
            'type' => ['required', 'string', 'in:' . implode(',', Booking::getValidTypes())],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'monitor_id' => ['nullable', 'integer', 'exists:monitors,id'],
            
            // Schedule
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            
            // Participants
            'participants' => ['required', 'array', 'min:1', 'max:20'],
            'participants.*.first_name' => ['required', 'string', 'max:100'],
            'participants.*.last_name' => ['required', 'string', 'max:100'],
            'participants.*.date_of_birth' => ['required', 'date', 'before:today'],
            'participants.*.level' => ['required', 'string', 'in:Principiante,Intermedio,Avanzado,Experto'],
            'participants.*.special_requests' => ['nullable', 'string', 'max:500'],
            'participants.*.medical_conditions' => ['nullable', 'string', 'max:500'],
            'participants.*.emergency_contact' => ['required', 'array'],
            'participants.*.emergency_contact.name' => ['required', 'string', 'max:100'],
            'participants.*.emergency_contact.phone' => ['required', 'string', 'max:20'],
            'participants.*.emergency_contact.relationship' => ['required', 'string', 'max:50'],
            
            // Extras
            'extras' => ['nullable', 'array'],
            'extras.*.extra_type' => ['required', 'string', 'in:insurance,equipment,transport,meal,photo,video,certificate,special_service,other'],
            'extras.*.name' => ['required', 'string', 'max:100'],
            'extras.*.description' => ['nullable', 'string', 'max:500'],
            'extras.*.unit_price' => ['required', 'numeric', 'min:0'],
            'extras.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'extras.*.is_required' => ['boolean'],
            
            // Equipment
            'equipment' => ['nullable', 'array'],
            'equipment.*.equipment_type' => ['required', 'string', 'in:skis,boots,poles,helmet,goggles,snowboard,bindings,clothing,protection,other'],
            'equipment.*.name' => ['required', 'string', 'max:100'],
            'equipment.*.brand' => ['nullable', 'string', 'max:50'],
            'equipment.*.model' => ['nullable', 'string', 'max:50'],
            'equipment.*.size' => ['nullable', 'string', 'max:20'],
            'equipment.*.participant_name' => ['required', 'string', 'max:200'],
            'equipment.*.participant_index' => ['required', 'integer', 'min:0'],
            'equipment.*.daily_rate' => ['required', 'numeric', 'min:0'],
            'equipment.*.rental_days' => ['required', 'integer', 'min:1', 'max:30'],
            
            // Optional fields
            'has_insurance' => ['boolean'],
            'has_equipment' => ['boolean'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            
            // Booking data (for wizard state)
            'booking_data' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Booking type is required',
            'type.in' => 'Invalid booking type. Must be: course, activity, or material',
            'client_id.required' => 'Client selection is required',
            'client_id.exists' => 'Selected client does not exist',
            'start_date.required' => 'Start date is required',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'start_time.date_format' => 'Start time must be in HH:MM format',
            'end_time.date_format' => 'End time must be in HH:MM format',
            'end_time.after' => 'End time must be after start time',
            'participants.required' => 'At least one participant is required',
            'participants.min' => 'At least one participant is required',
            'participants.max' => 'Maximum 20 participants allowed',
            'participants.*.first_name.required' => 'Participant first name is required',
            'participants.*.last_name.required' => 'Participant last name is required',
            'participants.*.date_of_birth.required' => 'Participant date of birth is required',
            'participants.*.date_of_birth.before' => 'Participant date of birth must be in the past',
            'participants.*.level.required' => 'Participant level is required',
            'participants.*.level.in' => 'Invalid participant level',
            'participants.*.emergency_contact.required' => 'Emergency contact is required for each participant',
            'course_id.exists' => 'Selected course does not exist',
            'monitor_id.exists' => 'Selected monitor does not exist',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'booking type',
            'client_id' => 'client',
            'course_id' => 'course',
            'monitor_id' => 'monitor',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'meeting_point' => 'meeting point',
            'participants' => 'participants',
            'special_requests' => 'special requests',
            'notes' => 'notes',
            'promo_code' => 'promotional code',
        ];
    }

    /**
     * Configure the validator instance
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate course is required for course type bookings
            if ($this->input('type') === 'course' && !$this->input('course_id')) {
                $validator->errors()->add('course_id', 'Course selection is required for course bookings');
            }

            // Validate equipment matches participants
            if ($this->input('equipment')) {
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

            // Validate end_date is provided for multi-day bookings
            if ($this->input('equipment')) {
                $hasMultiDayEquipment = collect($this->input('equipment', []))
                    ->some(fn($eq) => ($eq['rental_days'] ?? 1) > 1);
                
                if ($hasMultiDayEquipment && !$this->input('end_date')) {
                    $validator->errors()->add('end_date', 'End date is required for multi-day equipment rentals');
                }
            }

            // Validate time slots are provided for time-specific bookings
            if ($this->input('course_id') && !$this->input('start_time')) {
                $validator->errors()->add('start_time', 'Start time is required for course bookings');
            }
        });
    }
}