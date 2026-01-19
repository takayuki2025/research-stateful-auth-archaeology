<?php

return [
    'ledger' => [
        // local | http
        'driver' => env('TRUSTLEDGER_LEDGER_DRIVER', 'local'),

        // http driver settings
        'http' => [
            'base_url' => env('TRUSTLEDGER_LEDGER_HTTP_BASE_URL', 'http://payment-core:8080'),
            'timeout_seconds' => (int) env('TRUSTLEDGER_LEDGER_HTTP_TIMEOUT', 5),
            // 任意：簡易認証（最小）
            'api_key' => env('TRUSTLEDGER_LEDGER_HTTP_API_KEY', null),
        ],
    ],

    'admin_user_ids' => array_values(array_filter(array_map(
        fn ($v) => is_numeric($v) ? (int)$v : null,
        explode(',', env('TRUSTLEDGER_ADMIN_USER_IDS', '1'))
    ))),
];