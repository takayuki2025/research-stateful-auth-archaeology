<?php

namespace App\Modules\Item\Application\Support;

final class AtlasIdempotency
{
    /**
     * rawTextを安定化（日本語含む）。hashの揺れを極小化する。
     */
    public static function normalizeRawText(string $rawText): string
    {
        $text = trim($rawText);

        // Unicode正規化（intl拡張があればNFKC）
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_KC) ?? $text;
        }

        // 改行/タブ/連続空白を単一スペースへ
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        // 両端トリム再度
        return trim($text);
    }

    /**
     * payloadを安定化してsha256（hex）生成
     */
    public static function payloadHash(array $payload): string
    {
        // 重要：キー順を固定（ksort）。JSONはUNESCAPED_UNICODEで安定化
        ksort($payload);

        // 文字列化の揺れを抑える
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            // JSONにできない場合でも必ず同じ方針で落とす
            $json = serialize($payload);
        }

        return hash('sha256', $json);
    }

    public static function idempotencyKey(?int $tenantId, int $itemId, string $analysisVersion, string $payloadHash): string
    {
        $tenant = $tenantId === null ? 'null' : (string)$tenantId;
        return "akv3:{$tenant}:{$itemId}:{$analysisVersion}:{$payloadHash}";
    }
}