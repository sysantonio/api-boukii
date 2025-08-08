<?php

namespace App\Http\Requests\API\V5;

use App\Http\Requests\API\BaseApiRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class UpdateSeasonV5Request
 * 
 * Validates season update requests for V5 API.
 * School context is automatically injected from bearer token.
 * 
 * @package App\Http\Requests\API\V5
 */
class UpdateSeasonV5Request extends BaseApiRequest
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

        // School and season IDs will be available from ContextMiddleware
        // Just check if user has general permissions - specific school check is done in middleware
        return $user->hasPermission('seasons.update') ||
               $user->hasPermission('seasons.manage') ||
               $user->hasRole('school_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $schoolId = $this->getSchoolId();
        $seasonId = $this->route('season'); // Get season ID from route
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:100',
                // Unique name within the school, excluding current season
                Rule::unique('seasons', 'name')
                    ->where('school_id', $schoolId)
                    ->ignore($seasonId)
                    ->whereNull('deleted_at')
            ],
            'start_date' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:' . now()->subMonth()->format('Y-m-d'),
            ],
            'end_date' => [
                'sometimes',
                'required',
                'date',
                'after:start_date',
                'before_or_equal:' . now()->addYears(2)->format('Y-m-d'),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:500'
            ],
            'is_active' => [
                'sometimes',
                'boolean'
            ],
            'is_closed' => [
                'sometimes',
                'boolean'
            ],
            'max_capacity' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:10000'
            ],
            'price_modifier' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:999.99'
            ]
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
            'is_closed.boolean' => 'El campo cerrado debe ser verdadero o falso.',
            
            'max_capacity.integer' => 'La capacidad máxima debe ser un número entero.',
            'max_capacity.min' => 'La capacidad máxima debe ser al menos 1.',
            'max_capacity.max' => 'La capacidad máxima no puede ser mayor a 10,000.',
            
            'price_modifier.numeric' => 'El modificador de precio debe ser un número.',
            'price_modifier.min' => 'El modificador de precio no puede ser negativo.',
            'price_modifier.max' => 'El modificador de precio no puede ser mayor a 999.99.',
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
        $validator->after(function ($validator) {
            $this->validateSeasonStatus($validator);
            $this->validateDateRange($validator);
            $this->validateActiveSeasonConstraints($validator);
            $this->validateClosedSeasonConstraints($validator);
        });
    }

    /**
     * Validate that the season can be updated based on its current status
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateSeasonStatus($validator): void
    {
        $seasonId = $this->route('season');
        $season = \App\Models\Season::find($seasonId);

        if (!$season) {
            return;
        }

        // Check if season is closed and trying to modify critical fields
        if ($season->is_closed || $season->is_historical) {
            $restrictedFields = ['start_date', 'end_date', 'is_active'];
            
            foreach ($restrictedFields as $field) {
                if ($this->has($field)) {
                    $validator->errors()->add(
                        $field,
                        'No se pueden modificar las fechas o estado activo de una temporada cerrada o histórica.'
                    );
                }
            }
        }

        // Prevent reopening a closed season
        if ($season->is_closed && $this->has('is_closed') && !$this->boolean('is_closed')) {
            $validator->errors()->add(
                'is_closed',
                'No se puede reabrir una temporada que ya ha sido cerrada.'
            );
        }
    }

    /**
     * Validate that the date range doesn't conflict with existing seasons
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateDateRange($validator): void
    {
        if (!$this->has('start_date') && !$this->has('end_date')) {
            return;
        }

        $seasonId = $this->route('season');
        $season = \App\Models\Season::find($seasonId);
        
        if (!$season) {
            return;
        }

        $schoolId = $this->getSchoolId();
        
        // Use existing dates if not provided in update
        $startDate = Carbon::parse($this->input('start_date', $season->start_date));
        $endDate = Carbon::parse($this->input('end_date', $season->end_date));

        // Check for overlapping seasons (excluding current season)
        $overlappingSeasons = \App\Models\Season::where('school_id', $schoolId)
            ->where('id', '!=', $seasonId)
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
        if (!$this->has('is_active') || !$this->boolean('is_active')) {
            return;
        }

        $schoolId = $this->getSchoolId();
        $seasonId = $this->route('season');

        // Check if there's already another active season
        $existingActiveSeason = \App\Models\Season::where('school_id', $schoolId)
            ->where('id', '!=', $seasonId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if ($existingActiveSeason) {
            $validator->errors()->add(
                'is_active',
                'Ya existe otra temporada activa. Solo puede haber una temporada activa por escuela.'
            );
        }
    }

    /**
     * Validate constraints when closing a season
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateClosedSeasonConstraints($validator): void
    {
        if (!$this->has('is_closed') || !$this->boolean('is_closed')) {
            return;
        }

        $seasonId = $this->route('season');
        $season = \App\Models\Season::find($seasonId);
        
        if (!$season) {
            return;
        }

        // Check if season has pending bookings or active courses
        $hasPendingBookings = $season->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasPendingBookings) {
            $validator->errors()->add(
                'is_closed',
                'No se puede cerrar una temporada con reservas pendientes o confirmadas.'
            );
        }

        // If closing the season, ensure it's not active
        if ($this->boolean('is_closed') && ($season->is_active || $this->boolean('is_active'))) {
            $validator->errors()->add(
                'is_closed',
                'No se puede cerrar una temporada que esté marcada como activa.'
            );
        }
    }

    /**
     * Get school ID from request context (set by ContextMiddleware)
     * 
     * @return int|null
     */
    private function getSchoolId(): ?int
    {
        // School ID is set by ContextMiddleware
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