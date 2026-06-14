<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The frontend and API run on separate origins in production
| (https://champions.ferzendervarli.com -> https://api.champions.ferzendervarli.com),
| so the browser needs explicit CORS allowances. Origins come from the
| FRONTEND_URLS env var (comma-separated) when set, otherwise from the
| sensible defaults below. Only API routes are exposed and no credentials are
| used (the project has no authentication). Wildcard origins are never used.
|
*/

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('FRONTEND_URLS', '')),
)));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins !== [] ? $origins : [
        'https://champions.ferzendervarli.com',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-Id'],

    'max_age' => 0,

    'supports_credentials' => false,

];
