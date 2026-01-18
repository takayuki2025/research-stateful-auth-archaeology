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
        // Brand は v3 で "block dict（canonical+aliases）" に統一する
        // brands_v1.txt は空行区切りブロック前提（あなたが作成した形式）
        $brandDict         = $this->loadDict('brands_v1.txt');                 // extraction用（フラット）
        $brandAliasMap     = $this->loadGroupedAliasMap('brands_v1.txt');      // canonicalize用（alias→canonical）

        $conditionDict     = $this->loadDict('conditions_v1.txt');
        $colorDict         = $this->loadDict('colors_v1.txt');

        $conditionAliasMap = $this->loadGroupedAliasMap('conditions_v1.txt');
        $colorAliasMap     = $this->loadGroupedAliasMap('colors_v1.txt');

        // ======================================================
        // Extraction（raw token を得る）
        // ======================================================
        [$conditionRaw, $text, $confCondition] = $this->extractOne($text, $conditionDict);
        [$colorRaw, $text, $confColor]         = $this->extractOne($text, $colorDict);
        [$brandsRaw, $text]                    = $this->extractMany($text, $brandDict);

        // ======================================================
        // ✅ Canonicalize
        // - tokens: raw（監査）
        // - name: canonical（表示/保存/判断）
        // ======================================================

        // condition/color
        $condition = $conditionRaw ? ($conditionAliasMap[$conditionRaw] ?? $conditionRaw) : null;
        $color     = $colorRaw ? ($colorAliasMap[$colorRaw] ?? $colorRaw) : null;

        // brand: raw list -> canonical list
        $brandsCanonical = [];
        foreach ($brandsRaw as $b) {
            $bn = $this->normalize((string)$b);
            if ($bn === '') continue;
            $brandsCanonical[] = $brandAliasMap[$bn] ?? $bn;
        }
        $brandsCanonical = array_values(array_unique($brandsCanonical));
        $brandName = $brandsCanonical[0] ?? null;

        // ======================================================
        // Provisional tags（削除禁止）
        // ※ display_name は canonical を推奨（UI品質UP）
        // ======================================================
        $tags = [];

        foreach ($brandsCanonical as $b) {
            $tags['brand'][] = [
                'display_name' => $b,         // ★ canonical
                'raw_token'    => null,       // brandは複数なので省略（必要なら拡張可）
                'entity_id'    => null,
                'confidence'   => 0.9,
            ];
        }

        if ($condition) {
            $tags['condition'][] = [
                'display_name' => $condition,    // ★ canonical（新品 等）
                'raw_token'    => $conditionRaw, // ★監査
                'entity_id'    => null,
                'confidence'   => $confCondition,
            ];
        }

        if ($color) {
            $tags['color'][] = [
                'display_name' => $color,     // ★ canonical（ブルー 等）
                'raw_token'    => $colorRaw,  // ★監査
                'entity_id'    => null,
                'confidence'   => $confColor,
            ];
        }

        // ======================================================
        // Confidence（そのまま）
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
                'name'       => $brandName,               // ★ canonical（Apple 等）
                'confidence' => $confidenceMap['brand'],
            ],
            'condition' => [
                'name'       => $condition,               // ★ canonical（新品 等）
                'confidence' => $confidenceMap['condition'],
            ],
            'color' => [
                'name'       => $color,                   // ★ canonical（ブルー 等）
                'confidence' => $confidenceMap['color'],
            ],

            // Review / Learning 用（raw token を保存）
            'tokens' => [
                'brand'     => $brandsRaw,                        // ★ raw tokens（例：あっぷる）
                'condition' => $conditionRaw ? [$conditionRaw] : [], // ★ raw token（シンピン）
                'color'     => $colorRaw ? [$colorRaw] : [],         // ★ raw token（アオ）
            ],

            // provisional tags
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

    /**
     * grouped dict（canonical + aliases）から alias->canonical を生成
     * 前提：ファイルは「空行でブロック区切り」
     */
    private function loadGroupedAliasMap(string $file): array
    {
        $map = [];
        $path = "{$this->assetPath}/{$file}";
        if (!file_exists($path)) {
            logger()->warning('[AtlasKernel] grouped dict not found', ['file' => $file]);
            return $map;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES); // ★空行も保持
        $currentCanonical = null;

        foreach ($lines ?: [] as $line) {
            $raw = (string)$line;
            $trimmed = trim($raw);

            if ($trimmed === '') {
                $currentCanonical = null;
                continue;
            }

            $v = $this->normalize($trimmed);
            if ($v === '') continue;

            if ($currentCanonical === null) {
                $currentCanonical = $v;
                $map[$currentCanonical] = $currentCanonical;
                continue;
            }

            $map[$v] = $currentCanonical;
        }

        return $map;
    }

    // 既存互換：ブランドCSV alias（今後使わないが残してOK）
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