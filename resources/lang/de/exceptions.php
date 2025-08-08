<?php

return [
    // Season exceptions
    'season' => [
        'not_found' => 'Saison nicht gefunden',
        'no_active_season' => 'Keine aktive Saison für die Schule gefunden',
        'invalid_date_range' => 'Ungültiger Datumsbereich: Enddatum muss nach Startdatum liegen',
        'overlapping_seasons' => 'Saisondaten überschneiden sich mit bestehenden Saisons',
        'cannot_close_active' => 'Aktive Saison kann nicht geschlossen werden',
        'already_closed' => 'Saison ist bereits geschlossen',
    ],

    // Authentication exceptions
    'auth' => [
        'invalid_credentials' => 'Ungültige E-Mail oder Passwort',
        'user_not_found' => 'Benutzer nicht gefunden',
        'user_inactive' => 'Benutzerkonto ist inaktiv',
        'no_season_role' => 'Benutzer hat keine Rolle für diese Saison',
        'missing_credentials' => 'E-Mail, Passwort und Saison sind erforderlich',
        'missing_permission' => 'Unzureichende Berechtigung für diese Ressource',
        'invalid_role' => 'Ungültige Rolle angegeben',
        'no_season_access' => 'Benutzer hat keinen Zugang zu dieser Saison',
        'season_context_required' => 'Saisonkontext ist für diese Operation erforderlich',
    ],

    // School exceptions
    'school' => [
        'not_found' => 'Schule nicht gefunden',
        'no_schools_for_season' => 'Keine Schulen für die angegebene Saison gefunden',
    ],

    // Validation exceptions
    'validation' => [
        'failed' => 'Die angegebenen Daten sind ungültig',
        'required' => 'Das Feld :attribute ist erforderlich',
        'email' => 'Das :attribute muss eine gültige E-Mail-Adresse sein',
        'date' => 'Das :attribute muss ein gültiges Datum sein',
        'integer' => 'Das :attribute muss eine ganze Zahl sein',
        'min' => 'Das :attribute muss mindestens :min Zeichen haben',
        'max' => 'Das :attribute darf nicht länger als :max Zeichen sein',
        'unique' => 'Das :attribute wurde bereits verwendet',
        'exists' => 'Das ausgewählte :attribute ist ungültig',
    ],

    // Resource exceptions
    'resource' => [
        'not_found' => 'Die angeforderte :resource wurde nicht gefunden',
        'access_denied' => 'Zugriff auf :resource verweigert',
        'creation_failed' => 'Erstellen von :resource fehlgeschlagen',
        'update_failed' => 'Aktualisierung von :resource fehlgeschlagen',
        'deletion_failed' => 'Löschen von :resource fehlgeschlagen',
    ],

    // Route exceptions
    'route' => [
        'not_found' => 'Der angeforderte Endpunkt wurde nicht gefunden',
        'method_not_allowed' => 'HTTP-Methode für diesen Endpunkt nicht erlaubt',
    ],

    // Server exceptions
    'server' => [
        'internal_error' => 'Ein interner Serverfehler ist aufgetreten',
        'service_unavailable' => 'Service vorübergehend nicht verfügbar',
        'maintenance_mode' => 'Anwendung befindet sich im Wartungsmodus',
    ],
];