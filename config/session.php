<?php

/*
| The API is token-based and no route uses sessions, but Laravel's routing
| service provider resolves the session store when it builds the Redirector,
| so this config must exist. The `array` driver keeps it entirely in memory
| and writes nothing to disk or the database.
*/
return [
    'driver' => env('SESSION_DRIVER', 'array'),
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => 'ielts_speaking_session',
    'path' => '/',
    'domain' => null,
    'secure' => null,
    'http_only' => true,
    'same_site' => 'lax',
];
