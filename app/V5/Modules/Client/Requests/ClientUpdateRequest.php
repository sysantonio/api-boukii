<?php

namespace App\V5\Modules\Client\Requests;

use App\V5\Requests\BaseV5Request;
use App\V5\Modules\Client\Models\Client;

/**
 * V5 Client Update Request
 * 
 * Validates data for updating existing clients in the V5 system.
 */
class ClientUpdateRequest extends BaseV5Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $clientId = $this->route('client'); // Get client ID from route
        $schoolId = request()->header('X-School-ID');

        return [
            // Basic information
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/',
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/',
            ],

            // Contact information
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                "unique:v5_clients,email,{$clientId},id,school_id,{$schoolId}",
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            'telephone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],

            // Personal information
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:' . now()->subYears(120)->toDateString(),
            ],
            'gender' => [
                'nullable',
                'string',
                'in:male,female,other,prefer_not_to_say',
            ],
            'nationality' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            ],
            'preferred_language' => [
                'nullable',
                'string',
                'in:es,en,fr,de,it',
            ],

            // Status and profile (some fields should be restricted)
            'status' => [
                'sometimes',
                'string',
                'in:' . implode(',', Client::getValidStatuses()),
            ],

            // Address information
            'address' => [
                'nullable',
                'array',
            ],
            'address.street' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address.city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'address.postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address.country' => [
                'nullable',
                'string',
                'max:100',
            ],

            // Emergency contact
            'emergency_contact' => [
                'nullable',
                'array',
            ],
            'emergency_contact.name' => [
                'nullable',
                'string',
                'max:200',
            ],
            'emergency_contact.phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
            ],
            'emergency_contact.relationship' => [
                'nullable',
                'string',
                'max:50',
            ],

            // Medical conditions
            'medical_conditions' => [
                'nullable',
                'array',
            ],
            'medical_conditions.*' => [
                'string',
                'max:255',
            ],

            // Preferences
            'preferences' => [
                'nullable',
                'array',
            ],
            'preferences.level' => [
                'nullable',
                'string',
                'in:' . implode(',', Client::getValidLevels()),
            ],
            'preferences.instructor_gender' => [
                'nullable',
                'string',
                'in:male,female,no_preference',
            ],
            'preferences.group_size' => [
                'nullable',
                'string',
                'in:private,small_group,large_group,no_preference',
            ],
            'preferences.communication_method' => [
                'nullable',
                'string',
                'in:email,sms,phone,whatsapp',
            ],

            // Tags
            'tags' => [
                'nullable',
                'array',
                'max:10',
            ],
            'tags.*' => [
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9\-_\s]+$/',
            ],

            // Additional fields
            'avatar' => [
                'nullable',
                'string',
                'max:500',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],

            // Read-only fields that should not be updated directly
            // These are included to prevent accidental updates
            'client_reference' => 'prohibited',
            'school_id' => 'prohibited',
            'profile_type' => 'prohibited',
            'loyalty_tier' => 'prohibited',
            'total_spent' => 'prohibited',
            'total_bookings' => 'prohibited',
            'average_rating' => 'prohibited',
            'last_booking_at' => 'prohibited',
            'last_activity_at' => 'prohibited',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.regex' => 'El nombre solo puede contener letras, espacios, guiones y apostrofes.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.regex' => 'El apellido solo puede contener letras, espacios, guiones y apostrofes.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado para otro cliente.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'telephone.regex' => 'El formato del teléfono no es válido.',
            'date_of_birth.before' => 'La fecha de nacimiento no puede ser futura.',
            'date_of_birth.after' => 'La fecha de nacimiento no es válida.',
            'gender.in' => 'El género seleccionado no es válido.',
            'nationality.regex' => 'La nacionalidad solo puede contener letras y espacios.',
            'preferred_language.in' => 'El idioma seleccionado no es válido.',
            'status.in' => 'El estado seleccionado no es válido.',
            'tags.max' => 'No se pueden agregar más de 10 etiquetas.',
            'tags.*.regex' => 'Las etiquetas solo pueden contener letras, números, guiones y espacios.',
            'emergency_contact.phone.regex' => 'El formato del teléfono de emergencia no es válido.',
            'preferences.level.in' => 'El nivel seleccionado no es válido.',
            'preferences.instructor_gender.in' => 'La preferencia de género del instructor no es válida.',
            'preferences.group_size.in' => 'La preferencia de tamaño de grupo no es válida.',
            'preferences.communication_method.in' => 'El método de comunicación seleccionado no es válido.',
            'client_reference.prohibited' => 'La referencia del cliente no se puede modificar.',
            'school_id.prohibited' => 'El ID de la escuela no se puede modificar.',
            'profile_type.prohibited' => 'El tipo de perfil se actualiza automáticamente.',
            'loyalty_tier.prohibited' => 'El nivel de lealtad se actualiza automáticamente.',
            'total_spent.prohibited' => 'El total gastado se actualiza automáticamente.',
            'total_bookings.prohibited' => 'El total de reservas se actualiza automáticamente.',
            'average_rating.prohibited' => 'La calificación promedio se actualiza automáticamente.',
            'last_booking_at.prohibited' => 'La fecha de última reserva se actualiza automáticamente.',
            'last_activity_at.prohibited' => 'La fecha de última actividad se actualiza automáticamente.',
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'email',
            'phone' => 'teléfono',
            'telephone' => 'teléfono alternativo',
            'date_of_birth' => 'fecha de nacimiento',
            'gender' => 'género',
            'nationality' => 'nacionalidad',
            'preferred_language' => 'idioma preferido',
            'status' => 'estado',
            'address.street' => 'dirección',
            'address.city' => 'ciudad',
            'address.postal_code' => 'código postal',
            'address.country' => 'país',
            'emergency_contact.name' => 'nombre del contacto de emergencia',
            'emergency_contact.phone' => 'teléfono de emergencia',
            'emergency_contact.relationship' => 'relación del contacto de emergencia',
            'medical_conditions' => 'condiciones médicas',
            'preferences.level' => 'nivel',
            'preferences.instructor_gender' => 'preferencia de género del instructor',
            'preferences.group_size' => 'preferencia de tamaño de grupo',
            'preferences.communication_method' => 'método de comunicación',
            'tags' => 'etiquetas',
            'avatar' => 'avatar',
            'notes' => 'notas',
            'metadata' => 'metadatos',
        ]);
    }

    /**
     * Get fields that should be treated as booleans
     */
    protected function getBooleanFields(): array
    {
        return [];
    }

    /**
     * Get fields that should be trimmed
     */
    protected function getStringFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'phone',
            'telephone',
            'nationality',
            'preferred_language',
            'status',
            'address.street',
            'address.city',
            'address.postal_code',
            'address.country',
            'emergency_contact.name',
            'emergency_contact.phone',
            'emergency_contact.relationship',
            'notes',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that emergency contact has both name and phone if provided
            $emergencyContact = $this->input('emergency_contact');
            if ($emergencyContact && 
                ((!empty($emergencyContact['name']) && empty($emergencyContact['phone'])) ||
                 (empty($emergencyContact['name']) && !empty($emergencyContact['phone'])))) {
                $validator->errors()->add('emergency_contact', 'El contacto de emergencia debe incluir tanto el nombre como el teléfono.');
            }

            // Validate address completeness if provided
            $address = $this->input('address');
            if ($address && !empty(array_filter($address))) {
                if (empty($address['street']) || empty($address['city'])) {
                    $validator->errors()->add('address', 'Si se proporciona una dirección, debe incluir al menos la calle y la ciudad.');
                }
            }

            // Prevent certain status changes if client has active bookings
            if ($this->has('status') && $this->input('status') === Client::STATUS_BLOCKED) {
                // This would require checking active bookings, but we'll handle it in the service layer
                // for now, just add a warning in the validation context
            }
        });
    }
}