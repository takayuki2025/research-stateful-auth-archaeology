<?php

return [
    // venv 内の CLI を絶対パス指定（本番・Docker対応）
    'bin' => base_path('python_batch/atlaskernel/.venv/bin/atlaskernel'),

    // 実行制限（秒）
    'timeout' => 10,

    // ログに出すか
    'log_payload' => true,
];
