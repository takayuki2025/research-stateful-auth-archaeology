<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that will be utilized
    | by the application. By default, we will use the highly lightweight
    | file driver, which works well for the vast majority of applications.
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that the session should be
    | allowed to remain idle before it expires. If you ask the user to
    | remember him, the session will not expire until after this time.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When using the file session driver, we need a location where session
    | files may be stored. A default has been provided, but you are free
    | to change this to any other location you wish.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the database session driver, you may specify the connection
    | that should be used to store your sessions in the database. Of course
    | set this option to null defaults to the default database connection.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the database session driver, you may specify the table that
    | should be used to store your sessions. By default, we assume that
    | the table is named "sessions" but you may change this to any name.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must be manually swept to clean out old sessions.
    | Here are the chances to sweep a session on any given request.
    | By default, the odds are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify the name of the cookie stored on the clients machine
    | and used to maintain your session. The name specified here will be
    | used as the prefix for the session cookie name.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        'laravel_app_session' // ★★★ ここを 'laravel_app_session' に固定 ★★★
        // Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as being available. Typically, this will be set to the
    | root path of your application, but you are free to change it.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | Here you may specify the domain that the session cookie should be
    | available to. An empty string means the cookie will be available
    | to the current domain. You may also set it to a sub-domain.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | When this value is true, session cookies will only be sent back to
    | the server if the browser has a HTTPS connection. This will help
    | prevent the cookie from being accessed via nefarious connections.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Only Cookies
    |--------------------------------------------------------------------------
    |
    | By default, sessions are stored in a cookie that is accessible only
    | over HTTP connections. This prevents client side JavaScript from
    | accessing the cookie value and potentially stealing the session.
    |
    */

    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | This option controls how the same-site attribute of your session cookie
    | is set. 'Lax' and 'Strict' are the most common values. If you need
    | to disable the same-site setting, use 'None'.
    |
    */

    'same_site' => env('SESSION_SAME_SITE', null),

];