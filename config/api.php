<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API path patterns
    |--------------------------------------------------------------------------
    |
    | Path patterns that should receive API-style JSON error responses from
    | App\Exceptions\Handler. Used so that all backend API routes (e.g.
    | super-admin, v1) get consistent error format even if the URL path
    | does not start with "api/".
    |
    */
    'path_patterns' => [
        'api/*',
        'v1/*',
        '*super-admin*',
    ],

];
