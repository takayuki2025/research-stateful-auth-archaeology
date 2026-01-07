<?php

return [
    'secret' => env('JWT_SECRET', 'local_dev_secret_change_me'),
    'issuer' => env('JWT_ISSUER', 'omnicommerce-core'),
    'ttl'    => env('JWT_TTL', 3600), // seconds
];
