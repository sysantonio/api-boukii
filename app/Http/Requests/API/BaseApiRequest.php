<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

/**
 * Class BaseApiRequest
 * 
 * Base class for API form requests with standardized error handling.
 * 
 * @package App\Http\Requests\API
 */
abstract class BaseApiRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'error_code' => 'VALIDATION_ERROR',
            'errors' => $validator->errors()->toArray()
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Unauthorized to perform this action',
            'error_code' => 'AUTHORIZATION_FAILED'
        ], Response::HTTP_FORBIDDEN));
    }
}