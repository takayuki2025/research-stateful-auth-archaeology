<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

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

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost')),

    // 'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', [
    //     'localhost',
    //     'localhost:3000',
    //     'localhost:9000',
    //     // '127.0.0.1',
    //     // '127.0.0.1:8000',
    //     // '::1',
    //     // 'nuxt.test',
    //     // 'nuxt.test:4440',
    //     // 'laravel.test',
    //     // 'laravel.test:4430',
    //     // 'laravel.test:4431',
    // ]))),

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


    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS')),

    'middleware' => [
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Secure Cookies
    |--------------------------------------------------------------------------
    |
    | This value controls whether the cookies set by Sanctum will be set with
    | the `secure` flag, which requires a HTTPS connection to transmit.
    | This is generally only needed in a production environment.
    |
    */
    // ★★★ 修正箇所: ここを修正し、.envの値（SESSION_SECURE_COOKIE）を参照するように変更 ★★★
    'secure_cookies' => env('SANCTUM_SECURE_COOKIES', env('SESSION_SECURE_COOKIE', false)),

];
