<?php

namespace App\Modules\ProviderIntel\Application\Service;

final class HtmlTextExtractor
{
    /**
     * MVP: HTMLからタグを落としてテキスト化
     * v4.1で readability / DOM段落抽出へ強化可能
     */
    public function extract(string $html): string
    {
        // script/style除去（雑でも効果大）
        $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', ' ', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>(.*?)</style>#is', ' ', $html) ?? $html;

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 連続空白/改行を整理
        $text = preg_replace("/[ \t]+/", " ", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
