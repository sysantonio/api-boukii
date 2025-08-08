<?php

return [
    // Season exceptions
    'season' => [
        'not_found' => 'Stagione non trovata',
        'no_active_season' => 'Nessuna stagione attiva trovata per la scuola',
        'invalid_date_range' => 'Intervallo di date non valido: la data di fine deve essere successiva alla data di inizio',
        'overlapping_seasons' => 'Le date della stagione si sovrappongono con stagioni esistenti',
        'cannot_close_active' => 'Impossibile chiudere una stagione attiva',
        'already_closed' => 'La stagione è già chiusa',
    ],

    // Authentication exceptions
    'auth' => [
        'invalid_credentials' => 'Email o password non valide',
        'user_not_found' => 'Utente non trovato',
        'user_inactive' => 'Account utente inattivo',
        'no_season_role' => 'L\'utente non ha un ruolo assegnato per questa stagione',
        'missing_credentials' => 'Email, password e stagione sono obbligatori',
        'missing_permission' => 'Permessi insufficienti per accedere a questa risorsa',
        'invalid_role' => 'Ruolo specificato non valido',
        'no_season_access' => 'L\'utente non ha accesso a questa stagione',
        'season_context_required' => 'Il contesto della stagione è richiesto per questa operazione',
    ],

    // School exceptions
    'school' => [
        'not_found' => 'Scuola non trovata',
        'no_schools_for_season' => 'Nessuna scuola trovata per la stagione specificata',
    ],

    // Validation exceptions
    'validation' => [
        'failed' => 'I dati forniti non sono validi',
        'required' => 'Il campo :attribute è obbligatorio',
        'email' => 'Il :attribute deve essere un indirizzo email valido',
        'date' => 'Il :attribute deve essere una data valida',
        'integer' => 'Il :attribute deve essere un numero intero',
        'min' => 'Il :attribute deve avere almeno :min caratteri',
        'max' => 'Il :attribute non può superare :max caratteri',
        'unique' => 'Il :attribute è già stato utilizzato',
        'exists' => 'Il :attribute selezionato non è valido',
    ],

    // Resource exceptions
    'resource' => [
        'not_found' => 'La :resource richiesta non è stata trovata',
        'access_denied' => 'Accesso negato a :resource',
        'creation_failed' => 'Creazione di :resource fallita',
        'update_failed' => 'Aggiornamento di :resource fallito',
        'deletion_failed' => 'Eliminazione di :resource fallita',
    ],

    // Route exceptions
    'route' => [
        'not_found' => 'L\'endpoint richiesto non è stato trovato',
        'method_not_allowed' => 'Metodo HTTP non consentito per questo endpoint',
    ],

    // Server exceptions
    'server' => [
        'internal_error' => 'Si è verificato un errore interno del server',
        'service_unavailable' => 'Servizio temporaneamente non disponibile',
        'maintenance_mode' => 'L\'applicazione è in modalità manutenzione',
    ],
];