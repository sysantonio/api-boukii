<?php

return [
    'api' => env('API_RATE_LIMIT', '120,1'),
    'auth' => env('AUTH_RATE_LIMIT', '20,1'),
    'logging' => env('LOGGING_RATE_LIMIT', '30,1'),
];

