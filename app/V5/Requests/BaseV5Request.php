<?php

namespace App\V5\Requests;

use App\V5\Validation\V5ValidationRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseV5Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'code' => 'VALIDATION_ERROR',
                'message' => __('exceptions.validation.failed'),
                'errors' => $validator->errors(),
                'timestamp' => now()->toISOString(),
            ], 422)
        );
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return V5ValidationRules::messages();
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return V5ValidationRules::attributes();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string booleans to actual booleans
        $booleanFields = $this->getBooleanFields();
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ]);
            }
        }

        // Trim string fields
        $stringFields = $this->getStringFields();
        foreach ($stringFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([
                    $field => trim($this->input($field)),
                ]);
            }
        }
    }

    /**
     * Get fields that should be treated as booleans
     */
    protected function getBooleanFields(): array
    {
        return ['is_active', 'is_closed'];
    }

    /**
     * Get fields that should be trimmed
     */
    protected function getStringFields(): array
    {
        return ['name', 'email', 'search'];
    }
}
