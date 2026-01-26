<?php

namespace App\Modules\Review\Application\UseCase;

final class GenerateMissingInfoChecklistUseCase
{
    /**
     * MVP: ProviderIntel (catalog_source) の summary を見て不足を返す。
     * v6: evidence/fulltext/diff から厳密に生成する。
     */
    public function handle(array $reviewQueueItem): array
    {
        $summary = $reviewQueueItem['summary'] ?? [];
        $url = $summary['source_url'] ?? null;

        $items = [];

        // URLが無いのは致命
        if (!is_string($url) || $url === '') {
            $items[] = [
                'key' => 'source_url',
                'label' => '出典URL',
                'severity' => 'required',
                'hint' => '公式ページのURLを提示してください。',
            ];
        }

        // content_typeが取れてないなら取得失敗の可能性
        if (empty($summary['content_type'])) {
            $items[] = [
                'key' => 'content_type',
                'label' => '取得結果',
                'severity' => 'required',
                'hint' => 'ページ取得に失敗している可能性があります。URLの再確認または別URLを提示してください。',
            ];
        }

        // v4前なので“何が変わったか”は説明できない → diff根拠要求を入れる
        $items[] = [
            'key' => 'evidence',
            'label' => '根拠（スクショ/PDF/該当箇所）',
            'severity' => 'recommended',
            'hint' => '料金表や手数料の該当箇所（スクショ/PDFページ）を添付してください。v4で自動抽出に移行します。',
        ];

        return [
            'type' => 'providerintel',
            'message' => '承認に必要な追加情報があります。',
            'items' => $items,
        ];
    }
}