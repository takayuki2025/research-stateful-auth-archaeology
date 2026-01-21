<?php

return [

    // どれを有効にするか（順に試す）
    'enabled' => explode(',', env('JWT_PROVIDERS', 'custom,firebase,cognito,auth0')),

    'custom' => [
        'type' => 'hs256',
        'secret' => env('JWT_SECRET'),
        'issuer' => 'custom',
        'provider' => 'custom',
        'leeway' => 60,
    ],

    'firebase' => [
        'type' => 'jwks',
        'provider' => 'firebase',
        'project_id' => env('FIREBASE_PROJECT_ID'),
        // FirebaseのJWKS（RFC7517形式）
        'jwks_uri' => 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com',
        // iss/aud は project_id から導出（Firebaseの要件）:contentReference[oaicite:1]{index=1}
        'issuer' => null,
        'audience' => null,
        'leeway' => 60,
    ],

    'cognito' => [
        'type' => 'jwks',
        'provider' => 'cognito',
        'region' => env('COGNITO_REGION'),
        'user_pool_id' => env('COGNITO_USER_POOL_ID'),
        'client_id' => env('COGNITO_APP_CLIENT_ID'),
        'leeway' => 60,
        // token_use をチェック（id/access を区別）:contentReference[oaicite:2]{index=2}
        'expected_token_use' => env('COGNITO_TOKEN_USE', 'id'), // 'id' or 'access'
    ],

    'auth0' => [
        'type' => 'jwks',
        'provider' => 'auth0',
        'domain' => env('AUTH0_DOMAIN'),           // 例: xxx.jp.auth0.com
        'audience' => env('AUTH0_AUDIENCE'),       // API Identifier
        'leeway' => 60,
    ],
];