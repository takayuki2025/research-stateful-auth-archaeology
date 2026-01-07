<?php


return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://localhost',
        'https://localhost:3000',
        'https://127.0.0.1',
        'https://127.0.0.1:3000',
        'https://laravel.test',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true, // ★ Sanctum では絶対 true
];
