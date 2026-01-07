<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | AtlasKernel Execution Mode
    |--------------------------------------------------------------------------
    |
    | AtlasKernel の実行方式を切り替える設定です。
    |
    | - sync  : 同期実行（即時に AtlasKernelService::analyze を呼ぶ）
    | - queue : 非同期実行（Job に dispatch して Queue で処理）
    |
    | 本番では queue を推奨しますが、
    | デバッグ・障害切り分け・一時回避のために
    | sync に即座に戻せるようにしています。
    |
    */

    'mode' => env('ATLAS_KERNEL_MODE', 'queue'),

];
