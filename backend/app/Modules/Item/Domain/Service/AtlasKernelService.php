<?php

namespace App\Modules\Item\Domain\Service;

use DomainException;
use Illuminate\Support\Facades\DB;

final class AtlasKernelService
{
    private string $assetPath;
    private const GENERATED_VERSION = 'v3.0-review';

    public function __construct()
    {
        $this->assetPath = config('atlaskernel.assets_path');
    }

    /**
     * v3 固定：
     * - return は「下流全体の唯一の SoT」
     * - integration / extraction 等は内部処理に限定
     */
    public function requestAnalysis(
        int $itemId,
        string $rawText,
        ?int $tenantId = null
    ): array {
        if ($itemId <= 0) {
            throw new DomainException('Invalid itemId');
        }

        $text = $this->normalize($rawText);

        // ======================================================
        // Dict load
        // ======================================================
        $brandDict     = $this->loadDict('brands_v1.txt');
        $brandAlias    = $this->loadAlias('brand_alias.txt');
        $conditionDict = $this->loadDict('conditions_v1.txt');
        $colorDict     = $this->loadDict('colors_v1.txt');

        // ======================================================
        // Extraction
        // ======================================================
        [$condition, $text, $confCondition] = $this->extractOne($text, $conditionDict);
        [$color, $text, $confColor]         = $this->extractOne($text, $colorDict);
        [$brands, $text]                    = $this->extractMany($text, $brandDict);

        $brands = $this->applyAliasToList($brands, $brandAlias);
        $brandName = $brands[0] ?? null;

        // ======================================================
        // Provisional tags（削除禁止）
        // ======================================================
        $tags = [];

        foreach ($brands as $b) {
            $tags['brand'][] = [
                'display_name' => $b,
                'entity_id'    => null,
                'confidence'   => 0.9,
            ];
        }

        if ($condition) {
            $tags['condition'][] = [
                'display_name' => $condition,
                'entity_id'    => null,
                'confidence'   => $confCondition,
            ];
        }

        if ($color) {
            $tags['color'][] = [
                'display_name' => $color,
                'entity_id'    => null,
                'confidence'   => $confColor,
            ];
        }

        // ======================================================
        // Confidence
        // ======================================================
        $confidenceMap = [
            'brand'     => $brandName ? 0.9 : 0.0,
            'condition' => $confCondition ?? 0.0,
            'color'     => $confColor ?? 0.0,
        ];

        $overallConfidence = max(
            $confidenceMap['brand'],
            $confidenceMap['condition'],
            $confidenceMap['color'],
        );

        // ======================================================
        // ✅ v3 正式 SoT（これだけを返す）
        // ======================================================
        return [
            'brand' => [
                'name'       => $brandName,
                'confidence' => $confidenceMap['brand'],
            ],
            'condition' => [
                'name'       => $condition,
                'confidence' => $confidenceMap['condition'],
            ],
            'color' => [
                'name'       => $color,
                'confidence' => $confidenceMap['color'],
            ],

            // Review / Learning 用
            'tokens' => [
                'brand'     => $brands,
                'condition' => $condition ? [$condition] : [],
                'color'     => $color ? [$color] : [],
            ],

            // provisional tags（Entity / UI 即時反映用）
            'tags' => $tags,

            'confidence_map'     => $confidenceMap,
            'overall_confidence' => $overallConfidence,
        ];
    }

    /* ======================================================
       Helpers（すべて保持）
    ====================================================== */

    private function normalize(string $text): string
    {
        $text = trim($text);
        $text = mb_convert_kana($text, 'asVC', 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function loadDict(string $file): array
    {
        $path = "{$this->assetPath}/{$file}";
        if (!file_exists($path)) {
            logger()->warning('[AtlasKernel] dict not found', ['file' => $file]);
            return [];
        }

        return array_values(array_unique(array_map(
            fn ($v) => $this->normalize((string)$v),
            file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []
        )));
    }

    private function loadAlias(string $file): array
    {
        $map = [];
        $path = "{$this->assetPath}/{$file}";
        if (!file_exists($path)) {
            logger()->warning('[AtlasKernel] alias not found', ['file' => $file]);
            return $map;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (!str_contains($line, ',')) {
                continue;
            }
            [$from, $to] = array_map('trim', explode(',', $line, 2));
            if ($from !== '' && $to !== '') {
                $map[$this->normalize($from)] = $this->normalize($to);
            }
        }

        return $map;
    }

    private function applyAliasToList(array $values, array $alias): array
    {
        $out = [];
        foreach ($values as $v) {
            $n = $this->normalize((string)$v);
            if ($n !== '') {
                $out[] = $alias[$n] ?? $n;
            }
        }
        return array_values(array_unique($out));
    }

    private function extractOne(string $text, array $dict): array
    {
        if (empty($dict)) {
            return [null, $text, 0.0];
        }

        usort($dict, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($dict as $word) {
            if ($word !== '' && mb_strpos($text, $word) !== false) {
                $confidence = min(1.0, 0.5 + mb_strlen($word) / max(mb_strlen($text), 1));
                return [
                    $word,
                    $this->normalize(str_replace($word, '', $text)),
                    $confidence,
                ];
            }
        }

        return [null, $text, 0.0];
    }

    private function extractMany(string $text, array $dict): array
    {
        if (empty($dict)) {
            return [[], $text];
        }

        $found = [];
        usort($dict, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($dict as $word) {
            if ($word !== '' && mb_strpos($text, $word) !== false) {
                $found[] = $word;
                $text = str_replace($word, '', $text);
            }
        }

        return [array_values(array_unique($found)), $this->normalize($text)];
    }

    private function resolveEntityId(string $table, ?string $value, bool $autoCreate): ?int
    {
        if (!$value) {
            return null;
        }

        $value = $this->normalize($value);
        if ($value === '') {
            return null;
        }

        $q = DB::table($table)->where('canonical_name', $value);

        if ($table === 'brand_entities') {
            $q->orWhere('display_name', $value);
        }

        $id = $q->value('id');
        if ($id) {
            return (int)$id;
        }

        if (!$autoCreate) {
            return null;
        }

        try {
            return (int) DB::table($table)->insertGetId([
                'canonical_name' => $value,
                'display_name'   => $value,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable) {
            return DB::table($table)
                ->where('canonical_name', $value)
                ->value('id');
        }
    }
}