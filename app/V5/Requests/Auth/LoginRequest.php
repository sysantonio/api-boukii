<?php

namespace App\V5\Requests\Auth;

use App\V5\Requests\BaseV5Request;
use App\V5\Validation\V5ValidationRules;

class LoginRequest extends BaseV5Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge(
            V5ValidationRules::loginRules(),
            V5ValidationRules::localizationRules()
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateUserExists($validator);
            $this->validateSeasonAccess($validator);
        });
    }

    /**
     * Validate that user exists and is active
     */
    protected function validateUserExists($validator): void
    {
        $email = $this->input('email');

        if (! $email) {
            return;
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (! $user) {
            $validator->errors()->add('email', __('exceptions.auth.user_not_found'));

            return;
        }

        if (! $user->active) {
            $validator->errors()->add('email', __('exceptions.auth.user_inactive'));
        }
    }

    /**
     * Validate that user has access to the season
     */
    protected function validateSeasonAccess($validator): void
    {
        $email = $this->input('email');
        $seasonId = $this->input('season_id');

        if (! $email || ! $seasonId) {
            return;
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (! $user) {
            return; // This will be caught by validateUserExists
        }

        $hasSeasonRole = \App\V5\Models\UserSeasonRole::where('user_id', $user->id)
            ->where('season_id', $seasonId)
            ->exists();

        if (! $hasSeasonRole) {
            $validator->errors()->add('season_id', __('exceptions.auth.no_season_role'));
        }
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => __('exceptions.auth.missing_credentials'),
            'password.required' => __('exceptions.auth.missing_credentials'),
            'season_id.required' => __('exceptions.auth.missing_credentials'),
            'season_id.exists' => __('exceptions.season.not_found'),
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->input('email'))),
            ]);
        }

        // Ensure season_id is integer
        if ($this->has('season_id')) {
            $this->merge([
                'season_id' => (int) $this->input('season_id'),
            ]);
        }
    }
}
