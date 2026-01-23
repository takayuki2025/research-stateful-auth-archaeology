<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost'))))),

    // 'allowed_origins' => [
    //     env('FRONTEND_URL', 'http://localhost:3000'),
    // ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // JWT
    'supports_credentials' =>  false,
    // 'supports_credentials' => true,

];