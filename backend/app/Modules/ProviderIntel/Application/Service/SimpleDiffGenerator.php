<?php

namespace App\Modules\ProviderIntel\Application\Service;

final class SimpleDiffGenerator
{
    public function summarize(?string $beforeText, string $afterText): array
    {
        $beforeNorm = $beforeText !== null ? $this->normalize($beforeText) : null;
        $afterNorm  = $this->normalize($afterText);

        $beforeLines = $beforeNorm ? preg_split("/\r\n|\r|\n/", $beforeNorm) : [];
        $afterLines  = preg_split("/\r\n|\r|\n/", $afterNorm);

        $beforeCount = is_array($beforeLines) ? count($beforeLines) : 0;
        $afterCount  = is_array($afterLines) ? count($afterLines) : 0;

        // MVP: 先頭500文字を正規化後に保存
        return [
            'before_line_count' => $beforeCount,
            'after_line_count'  => $afterCount,
            'before_head'       => $beforeNorm ? mb_substr($beforeNorm, 0, 500) : null,
            'after_head'        => mb_substr($afterNorm, 0, 500),

            // ✅ v4.0.1: 追加の短い要約（任意だが効果大）
            'summary' => $this->simpleSummary($beforeCount, $afterCount),
        ];
    }

    private function normalize(string $text): string
    {
        // ① 改行コード統一
        $t = str_replace(["\r\n", "\r"], "\n", $text);

        // ② タブ→スペース
        $t = str_replace("\t", " ", $t);

        // ③ 連続スペース圧縮
        $t = preg_replace("/[ ]{2,}/", " ", $t) ?? $t;

        // ④ 連続改行を最大2つへ
        $t = preg_replace("/\n{3,}/", "\n\n", $t) ?? $t;

        // ⑤ 行頭/行末の空白除去（行単位）
        $lines = preg_split("/\n/", $t) ?: [];
        $lines = array_map(fn($l) => trim((string)$l), $lines);

        // 空行も残すが、連続空行は上で圧縮済み
        $t = implode("\n", $lines);

        return trim($t);
    }

    private function simpleSummary(int $beforeLines, int $afterLines): string
    {
        if ($beforeLines === 0) {
            return 'Initial extraction (no before document).';
        }
        if ($beforeLines === $afterLines) {
            return 'Line count unchanged (content may still have changed).';
        }
        return "Line count changed: before={$beforeLines} → after={$afterLines}.";
    }
}
