<?php

namespace App\V5\Validation;

class V5ValidationRules
{
    /**
     * Season validation rules
     */
    public static function seasonRules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'hour_start' => 'nullable|date_format:H:i',
            'hour_end' => 'nullable|date_format:H:i|after:hour_start',
            'is_active' => 'boolean',
            'vacation_days' => 'nullable|string|max:1000',
            'school_id' => 'required|integer|exists:schools,id',
            'is_closed' => 'boolean',
            'closed_at' => 'nullable|date',
        ];
    }

    /**
     * Season update rules (more permissive)
     */
    public static function seasonUpdateRules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'hour_start' => 'sometimes|nullable|date_format:H:i',
            'hour_end' => 'sometimes|nullable|date_format:H:i|after:hour_start',
            'is_active' => 'sometimes|boolean',
            'vacation_days' => 'sometimes|nullable|string|max:1000',
            'school_id' => 'sometimes|integer|exists:schools,id',
            'is_closed' => 'sometimes|boolean',
            'closed_at' => 'sometimes|nullable|date',
        ];
    }

    /**
     * Authentication validation rules
     */
    public static function loginRules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'season_id' => 'required|integer|exists:seasons,id',
        ];
    }

    /**
     * User season role validation rules
     */
    public static function userSeasonRoleRules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'season_id' => 'required|integer|exists:seasons,id',
            'role' => 'required|string|max:255|exists:roles,name',
        ];
    }

    /**
     * School season settings validation rules
     */
    public static function schoolSeasonSettingsRules(): array
    {
        return [
            'school_id' => 'required|integer|exists:schools,id',
            'season_id' => 'required|integer|exists:seasons,id',
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'required|string|max:1000',
        ];
    }

    /**
     * Pagination validation rules
     */
    public static function paginationRules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|max:255',
            'sort_order' => 'sometimes|in:asc,desc',
        ];
    }

    /**
     * Localization validation rules
     */
    public static function localizationRules(): array
    {
        return [
            'lang' => 'sometimes|string|in:en,es,fr,de,it',
        ];
    }

    /**
     * Common search validation rules
     */
    public static function searchRules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'filters' => 'sometimes|array',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ];
    }

    /**
     * File upload validation rules
     */
    public static function fileUploadRules(): array
    {
        return [
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'sometimes|string|in:image,document,video',
        ];
    }

    /**
     * Get combined rules for complex validations
     */
    public static function getCombinedRules(array $ruleGroups): array
    {
        $combinedRules = [];

        foreach ($ruleGroups as $group) {
            if (method_exists(static::class, $group.'Rules')) {
                $rules = static::{$group.'Rules'}();
                $combinedRules = array_merge($combinedRules, $rules);
            }
        }

        return $combinedRules;
    }

    /**
     * Custom validation messages
     */
    public static function messages(): array
    {
        return [
            'required' => 'exceptions.validation.required',
            'email' => 'exceptions.validation.email',
            'date' => 'exceptions.validation.date',
            'integer' => 'exceptions.validation.integer',
            'min' => 'exceptions.validation.min',
            'max' => 'exceptions.validation.max',
            'unique' => 'exceptions.validation.unique',
            'exists' => 'exceptions.validation.exists',
            'after' => 'The :attribute must be a date after :date.',
            'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
            'date_format' => 'The :attribute does not match the format :format.',
            'in' => 'The selected :attribute is invalid.',
            'file' => 'The :attribute must be a file.',
            'array' => 'The :attribute must be an array.',
        ];
    }

    /**
     * Custom attribute names for better error messages
     */
    public static function attributes(): array
    {
        return [
            'start_date' => 'start date',
            'end_date' => 'end date',
            'hour_start' => 'starting hour',
            'hour_end' => 'ending hour',
            'is_active' => 'active status',
            'vacation_days' => 'vacation days',
            'school_id' => 'school',
            'season_id' => 'season',
            'user_id' => 'user',
            'per_page' => 'items per page',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
            'date_from' => 'from date',
            'date_to' => 'to date',
        ];
    }
}
