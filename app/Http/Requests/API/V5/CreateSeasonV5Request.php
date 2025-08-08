<?php

namespace App\Http\Requests\API\V5;

use App\Http\Requests\API\BaseApiRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class CreateSeasonV5Request
 * 
 * Validates season creation requests for V5 API.
 * School context is automatically injected from bearer token.
 * 
 * @package App\Http\Requests\API\V5
 */
class CreateSeasonV5Request extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        // School ID will be available from SchoolContextMiddleware
        // For now, allow any authenticated user - authorization is handled in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Simplified rules - advanced validation is done in the service layer
        return [
            'name' => 'required|string|min:3|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'sometimes|nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'max_capacity' => 'sometimes|nullable|integer|min:1|max:10000',
            'price_modifier' => 'sometimes|nullable|numeric|min:0|max:999.99'
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la temporada es obligatorio.',
            'name.string' => 'El nombre debe ser un texto válido.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
            'name.unique' => 'Ya existe una temporada con este nombre en tu escuela.',
            
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser más de un mes en el pasado.',
            
            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'end_date.before_or_equal' => 'La fecha de fin no puede ser más de 2 años en el futuro.',
            
            'description.string' => 'La descripción debe ser un texto válido.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.',
            
            'is_active.boolean' => 'El campo activo debe ser verdadero o falso.',
            
            'max_capacity.integer' => 'La capacidad máxima debe ser un número entero.',
            'max_capacity.min' => 'La capacidad máxima debe ser al menos 1.',
            'max_capacity.max' => 'La capacidad máxima no puede ser mayor a 10,000.',
            
            'price_modifier.numeric' => 'El modificador de precio debe ser un número.',
            'price_modifier.min' => 'El modificador de precio no puede ser negativo.',
            'price_modifier.max' => 'El modificador de precio no puede ser mayor a 999.99.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre de la temporada',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'description' => 'descripción',
            'is_active' => 'estado activo',
            'max_capacity' => 'capacidad máxima',
            'price_modifier' => 'modificador de precio',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        // Skip advanced validations here - they will be done in the service layer
        // because they require school context which may not be available during request validation
    }

    /**
     * Validate that the date range doesn't conflict with existing seasons
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateDateRange($validator): void
    {
        if (!$this->start_date || !$this->end_date) {
            return;
        }

        $schoolId = $this->getSchoolId();
        
        // Skip validation if school ID is not available yet (middleware hasn't run)
        if (!$schoolId) {
            return;
        }
        
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // Check for overlapping seasons
        $overlappingSeasons = \App\Models\Season::where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->where('start_date', '<=', $startDate)
                                 ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlappingSeasons) {
            $validator->errors()->add(
                'start_date',
                'Las fechas de esta temporada se solapan con una temporada existente.'
            );
        }

        // Validate minimum duration (at least 1 week)
        if ($startDate->diffInDays($endDate) < 7) {
            $validator->errors()->add(
                'end_date',
                'La temporada debe durar al menos 7 días.'
            );
        }

        // Validate maximum duration (max 18 months)
        if ($startDate->diffInMonths($endDate) > 18) {
            $validator->errors()->add(
                'end_date',
                'La temporada no puede durar más de 18 meses.'
            );
        }
    }

    /**
     * Validate constraints when setting a season as active
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateActiveSeasonConstraints($validator): void
    {
        if (!$this->boolean('is_active')) {
            return;
        }

        $schoolId = $this->getSchoolId();
        
        // Skip validation if school ID is not available yet (middleware hasn't run)
        if (!$schoolId) {
            return;
        }

        // Check if there's already an active season
        $existingActiveSeason = \App\Models\Season::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if ($existingActiveSeason) {
            $validator->errors()->add(
                'is_active',
                'Ya existe una temporada activa. Solo puede haber una temporada activa por escuela.'
            );
        }
    }

    /**
     * Get school ID from request context (set by SchoolContextMiddleware)
     * 
     * @return int|null
     */
    private function getSchoolId(): ?int
    {
        // School ID is set by SchoolContextMiddleware
        return $this->get('context_school_id');
    }

    /**
     * Get the data that should be validated.
     * 
     * @return array
     */
    public function validationData(): array
    {
        $data = parent::validationData();
        
        // Ensure dates are properly formatted
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date'])->format('Y-m-d');
        }
        
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date'])->format('Y-m-d');
        }
        
        return $data;
    }
}