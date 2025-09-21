<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Allow configuring stateful domains via the SANCTUM_STATEFUL_DOMAINS env
    | variable. If not provided, try to derive a sensible default from
    | FRONTEND_URL (or APP_URL) and common localhost entries so local dev and
    | simple hosting work without editing this file.
    |
    */
    'stateful' => (function () {
        $envList = env('SANCTUM_STATEFUL_DOMAINS');
        if (!empty($envList)) {
            return explode(',', $envList);
        }

        // Try FRONTEND_URL first, then APP_URL, then fall back to common dev hosts
        $frontend = env('FRONTEND_URL', env('APP_URL', 'http://localhost'));
        $host = parse_url($frontend, PHP_URL_HOST) ?: 'localhost';
        $port = parse_url($frontend, PHP_URL_PORT);
        $hostAndPort = $port ? $host.':'.$port : $host;

        return array_values(array_unique(array_filter([
            $host,
            $hostAndPort,
            'localhost',
            'localhost:3000',
            '127.0.0.1',
            '127.0.0.1:8000',
            '::1',
            '*.test',
        ])));
    })(),

    'prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
