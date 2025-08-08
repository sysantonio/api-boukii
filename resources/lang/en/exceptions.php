<?php

return [
    // Season exceptions
    'season' => [
        'not_found' => 'Season not found',
        'no_active_season' => 'No active season found for school',
        'invalid_date_range' => 'Invalid date range: end date must be after start date',
        'overlapping_seasons' => 'Season dates overlap with existing seasons',
        'cannot_close_active' => 'Cannot close an active season',
        'already_closed' => 'Season is already closed',
    ],

    // Authentication exceptions
    'auth' => [
        'invalid_credentials' => 'Invalid email or password',
        'user_not_found' => 'User not found',
        'user_inactive' => 'User account is inactive',
        'no_season_role' => 'User has no role assigned for this season',
        'missing_credentials' => 'Email, password, and season are required',
        'missing_permission' => 'Insufficient permissions to access this resource',
        'invalid_role' => 'Invalid role specified',
        'no_season_access' => 'User does not have access to this season',
        'season_context_required' => 'Season context is required for this operation',
    ],

    // School exceptions
    'school' => [
        'not_found' => 'School not found',
        'no_schools_for_season' => 'No schools found for the specified season',
    ],

    // Validation exceptions
    'validation' => [
        'failed' => 'The given data was invalid',
        'required' => 'The :attribute field is required',
        'email' => 'The :attribute must be a valid email address',
        'date' => 'The :attribute must be a valid date',
        'integer' => 'The :attribute must be an integer',
        'min' => 'The :attribute must be at least :min characters',
        'max' => 'The :attribute may not be greater than :max characters',
        'unique' => 'The :attribute has already been taken',
        'exists' => 'The selected :attribute is invalid',
    ],

    // Resource exceptions
    'resource' => [
        'not_found' => 'The requested :resource was not found',
        'access_denied' => 'Access denied to :resource',
        'creation_failed' => 'Failed to create :resource',
        'update_failed' => 'Failed to update :resource',
        'deletion_failed' => 'Failed to delete :resource',
    ],

    // Route exceptions
    'route' => [
        'not_found' => 'The requested endpoint was not found',
        'method_not_allowed' => 'HTTP method not allowed for this endpoint',
    ],

    // Server exceptions
    'server' => [
        'internal_error' => 'An internal server error occurred',
        'service_unavailable' => 'Service temporarily unavailable',
        'maintenance_mode' => 'Application is in maintenance mode',
    ],
];