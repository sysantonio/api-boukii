<?php

namespace App\Http\Requests\API\V5;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['required', 'string', 'in:debug,info,warning,error,critical'],
            'message' => ['required', 'string', 'max:2048'],
            'context' => ['sometimes', 'array'],
            'clientTime' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => 'Invalid log level provided.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'The provided data is invalid.',
            'errors' => $validator->errors(),
            'error_code' => 'VALIDATION_ERROR'
        ], 422));
    }
}
