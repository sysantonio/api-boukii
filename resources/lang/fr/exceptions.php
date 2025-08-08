<?php

return [
    // Season exceptions
    'season' => [
        'not_found' => 'Saison non trouvée',
        'no_active_season' => 'Aucune saison active trouvée pour l\'école',
        'invalid_date_range' => 'Plage de dates invalide : la date de fin doit être postérieure à la date de début',
        'overlapping_seasons' => 'Les dates de la saison se chevauchent avec les saisons existantes',
        'cannot_close_active' => 'Impossible de fermer une saison active',
        'already_closed' => 'La saison est déjà fermée',
    ],

    // Authentication exceptions
    'auth' => [
        'invalid_credentials' => 'Email ou mot de passe incorrect',
        'user_not_found' => 'Utilisateur non trouvé',
        'user_inactive' => 'Le compte utilisateur est inactif',
        'no_season_role' => 'L\'utilisateur n\'a pas de rôle assigné pour cette saison',
        'missing_credentials' => 'Email, mot de passe et saison sont requis',
        'missing_permission' => 'Permissions insuffisantes pour accéder à cette ressource',
        'invalid_role' => 'Rôle spécifié invalide',
        'no_season_access' => 'L\'utilisateur n\'a pas accès à cette saison',
        'season_context_required' => 'Le contexte de saison est requis pour cette opération',
    ],

    // School exceptions
    'school' => [
        'not_found' => 'École non trouvée',
        'no_schools_for_season' => 'Aucune école trouvée pour la saison spécifiée',
    ],

    // Validation exceptions
    'validation' => [
        'failed' => 'Les données fournies sont invalides',
        'required' => 'Le champ :attribute est requis',
        'email' => 'Le :attribute doit être une adresse email valide',
        'date' => 'Le :attribute doit être une date valide',
        'integer' => 'Le :attribute doit être un nombre entier',
        'min' => 'Le :attribute doit avoir au moins :min caractères',
        'max' => 'Le :attribute ne peut pas dépasser :max caractères',
        'unique' => 'Le :attribute a déjà été pris',
        'exists' => 'Le :attribute sélectionné est invalide',
    ],

    // Resource exceptions
    'resource' => [
        'not_found' => 'La :resource demandée n\'a pas été trouvée',
        'access_denied' => 'Accès refusé à :resource',
        'creation_failed' => 'Échec de la création de :resource',
        'update_failed' => 'Échec de la mise à jour de :resource',
        'deletion_failed' => 'Échec de la suppression de :resource',
    ],

    // Route exceptions
    'route' => [
        'not_found' => 'Le endpoint demandé n\'a pas été trouvé',
        'method_not_allowed' => 'Méthode HTTP non autorisée pour ce endpoint',
    ],

    // Server exceptions
    'server' => [
        'internal_error' => 'Une erreur interne du serveur s\'est produite',
        'service_unavailable' => 'Service temporairement indisponible',
        'maintenance_mode' => 'L\'application est en mode maintenance',
    ],
];