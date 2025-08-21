<?php

namespace App\Http\Requests\V5\Context;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SwitchSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => 'required|integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException($this->problem('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $errors));
    }

    private function problem(string $detail, int $status, array $errors = null)
    {
        $problem = [
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
        ];

        if ($errors) {
            $problem['errors'] = $errors;
        }

        return response()->json($problem, $status, ['Content-Type' => 'application/problem+json']);
    }
}
