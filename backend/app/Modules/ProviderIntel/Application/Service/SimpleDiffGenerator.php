<?php

namespace App\Modules\ProviderIntel\Application\Service;

final class SimpleDiffGenerator
{
    public function summarize(?string $beforeText, string $afterText): array
    {
        $beforeLines = $beforeText ? preg_split("/\r\n|\r|\n/", $beforeText) : [];
        $afterLines  = preg_split("/\r\n|\r|\n/", $afterText);

        $beforeCount = is_array($beforeLines) ? count($beforeLines) : 0;
        $afterCount  = is_array($afterLines) ? count($afterLines) : 0;

        // MVP: 先頭500文字だけサマリ（監査用）
        return [
            'before_line_count' => $beforeCount,
            'after_line_count' => $afterCount,
            'before_head' => $beforeText ? mb_substr($beforeText, 0, 500) : null,
            'after_head' => mb_substr($afterText, 0, 500),
        ];
    }
}