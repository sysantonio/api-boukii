<?php

namespace App\Http\Requests\V5\Context;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class SwitchSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schoolId = $this->input('school_id');
        if (! $schoolId) {
            return false;
        }

        $school = School::find($schoolId);
        if (! $school) {
            return false;
        }

        return $this->user()->can('switch', $school);
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException($this->problem('Access denied', Response::HTTP_FORBIDDEN));
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
