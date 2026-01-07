<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    // アプリケーションが使用するデフォルトのキャッシュストア
    "default" => env("CACHE_STORE", "file"),

    // キャッシュを格納する様々な設定
    "stores" => [
        "apc" => [
            "driver" => "apc",
        ],
        "array" => [
            "driver" => "array",
            "serialize" => false,
        ],
        "database" => [
            "driver" => "database",
            "table" => "cache",
            "connection" => null,
            "lock_table" => "cache_locks",
        ],
        "file" => [
            "driver" => "file",
            "path" => storage_path("framework/cache/data"),
            "lock_path" => storage_path("framework/cache/data"),
        ],
        "memcached" => [
            "driver" => "memcached",
            "persistent_id" => env("MEMCACHED_PERSISTENT_ID"),
            "sasl" => [
                env("MEMCACHED_USERNAME"),
                env("MEMCACHED_PASSWORD"),
            ],
            "options" => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            "servers" => [
                [
                    "host" => env("MEMCACHED_HOST", "127.0.0.1"),
                    "port" => env("MEMCACHED_PORT", 11211),
                    "weight" => 100,
                ],
            ],
        ],
        "redis" => [
            "driver" => "redis",
            "connection" => "cache",
            "lock_connection" => "default",
        ],
        "dynamodb" => [
            "driver" => "dynamodb",
            "key" => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION", "us-east-1"),
            "table" => env("DYNAMODB_CACHE_TABLE", "cache"),
            "endpoint" => env("DYNAMODB_ENDPOINT"),
        ],
        "octane" => [
            "driver" => "octane",
        ],
    ],

    // キャッシュロックの設定
    "lock_stores" => [
        "redis" => [
            "driver" => "redis",
            "connection" => "default",
        ],
        "dynamodb" => [
            "driver" => "dynamodb",
            "key" => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION", "us-east-1"),
            "table" => env("DYNAMODB_CACHE_LOCK_TABLE", "cache_locks"),
            "endpoint" => env("DYNAMODB_ENDPOINT"),
        ],
    ],

    // キャッシュキーのプレフィックス
    "prefix" => env(
        "CACHE_PREFIX",
        "laravel_cache" // <- この行を修正
    ),
];
