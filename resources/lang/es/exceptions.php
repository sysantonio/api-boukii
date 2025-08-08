<?php

return [
    // Season exceptions
    'season' => [
        'not_found' => 'Temporada no encontrada',
        'no_active_season' => 'No se encontró temporada activa para la escuela',
        'invalid_date_range' => 'Rango de fechas inválido: la fecha de fin debe ser posterior a la de inicio',
        'overlapping_seasons' => 'Las fechas de la temporada se superponen con temporadas existentes',
        'cannot_close_active' => 'No se puede cerrar una temporada activa',
        'already_closed' => 'La temporada ya está cerrada',
    ],

    // Authentication exceptions
    'auth' => [
        'invalid_credentials' => 'Email o contraseña incorrectos',
        'user_not_found' => 'Usuario no encontrado',
        'user_inactive' => 'La cuenta de usuario está inactiva',
        'no_season_role' => 'El usuario no tiene rol asignado para esta temporada',
        'missing_credentials' => 'Email, contraseña y temporada son requeridos',
        'missing_permission' => 'Permisos insuficientes para acceder a este recurso',
        'invalid_role' => 'Rol especificado inválido',
        'no_season_access' => 'El usuario no tiene acceso a esta temporada',
        'season_context_required' => 'El contexto de temporada es requerido para esta operación',
    ],

    // School exceptions
    'school' => [
        'not_found' => 'Escuela no encontrada',
        'no_schools_for_season' => 'No se encontraron escuelas para la temporada especificada',
    ],

    // Validation exceptions
    'validation' => [
        'failed' => 'Los datos proporcionados son inválidos',
        'required' => 'El campo :attribute es requerido',
        'email' => 'El :attribute debe ser una dirección de email válida',
        'date' => 'El :attribute debe ser una fecha válida',
        'integer' => 'El :attribute debe ser un número entero',
        'min' => 'El :attribute debe tener al menos :min caracteres',
        'max' => 'El :attribute no puede ser mayor a :max caracteres',
        'unique' => 'El :attribute ya ha sido tomado',
        'exists' => 'El :attribute seleccionado es inválido',
    ],

    // Resource exceptions
    'resource' => [
        'not_found' => 'El :resource solicitado no fue encontrado',
        'access_denied' => 'Acceso denegado al :resource',
        'creation_failed' => 'Error al crear :resource',
        'update_failed' => 'Error al actualizar :resource',
        'deletion_failed' => 'Error al eliminar :resource',
    ],

    // Route exceptions
    'route' => [
        'not_found' => 'El endpoint solicitado no fue encontrado',
        'method_not_allowed' => 'Método HTTP no permitido para este endpoint',
    ],

    // Server exceptions
    'server' => [
        'internal_error' => 'Ocurrió un error interno del servidor',
        'service_unavailable' => 'Servicio temporalmente no disponible',
        'maintenance_mode' => 'La aplicación está en modo de mantenimiento',
    ],
];