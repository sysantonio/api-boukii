<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Solo endpoints específicos que necesitan excepción CSRF
        'api/payrexxNotification',  // Webhook de pagos (debe usar signature verification)
        'api/payrexx/finish',       // Finalización de pagos
        // Endpoints de webhook que usan verification por signature
        'api/admin/integrations/webhook/realtime-update',
    ];
}
