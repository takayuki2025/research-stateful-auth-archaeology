<?php

namespace App\Modules\Item\Domain\Service;

use DomainException;
use Illuminate\Support\Facades\DB;
use App\Modules\Item\Domain\Dto\AtlasAnalysisResult;

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
    ): AtlasAnalysisResult {
        if ($itemId <= 0) {
            throw new DomainException('Invalid itemId');
        }

        $rawText = trim((string)$rawText);
        $text = $this->normalize($rawText);

        // --- dict load ---
        $brandDict  = $this->loadDict('brands_v1.txt');
        $brandAlias = $this->loadAlias('brand_alias.txt');
        $conditionDict = $this->loadDict('conditions_v1.txt');
        $colorDict     = $this->loadDict('colors_v1.txt');

        // --- extract ---
        [$condition, $text, $confCondition] = $this->extractOne($text, $conditionDict);
        [$color, $text, $confColor] = $this->extractOne($text, $colorDict);
        [$brands, $text] = $this->extractMany($text, $brandDict);

        $brands = $this->applyAliasToList($brands, $brandAlias);

        // --- build tags ---
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

        return new AtlasAnalysisResult([
    'integration' => [
        'brand_identity' => [
            'canonical' => $brands[0] ?? null,
            'aliases' => array_keys($brandAlias),
            'confidence' => count($brands) ? 0.9 : 0.0,
        ],
    ],

    'extraction' => [
        'condition' => $condition ? [$condition] : [],
        'color' => $color ? [$color] : [],
    ],

    'partitioning' => [
        'facts' => [
            'category' => [],
        ],
        'ai_inference' => [],
    ],

    'normalization' => [
        'brand_entity_id' =>
            $this->resolveEntityId('brand_entities', $brands[0] ?? null, true),
    ],

    'lineage' => [
        'model' => 'AtlasKernel-v3',
        'generated_at' => now()->toIso8601String(),
    ],
]);
    }

    /* ======================================================
       Helpers
    ====================================================== */

    private function normalize(string $text): string
    {
        // - 全角/半角
        // - スペース/タブ
        // - 前後 trim
        $text = trim($text);
        $text = mb_convert_kana($text, 'asVC', 'UTF-8');
        // 連続スペースを 1 に
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }

    private function loadDict(string $file): array
    {
        $path = "{$this->assetPath}/{$file}";
        if (! file_exists($path)) {
            logger()->warning('[AtlasKernel] dict not found', ['file' => $file, 'path' => $path]);
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines)) {
            return [];
        }

        return array_values(array_unique(array_map(
            fn ($v) => $this->normalize((string)$v),
            $lines
        )));
    }

    /**
     * alias file format:
     *   from,to
     *   アップル,Apple
     */
    private function loadAlias(string $file): array
    {
        $map = [];
        $path = "{$this->assetPath}/{$file}";

        if (! file_exists($path)) {
            logger()->warning('[AtlasKernel] alias not found', ['file' => $file, 'path' => $path]);
            return $map;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines)) {
            return $map;
        }

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || ! str_contains($line, ',')) {
                continue;
            }

            [$from, $to] = array_map('trim', explode(',', $line, 2));
            if ($from === '' || $to === '') {
                continue;
            }

            $map[$this->normalize($from)] = $this->normalize($to);
        }

        return $map;
    }

    /**
     * items.category は
     * - JSON 文字列
     * - 二重 JSON（string を json_decode したら string が出る）
     * - すでに array 形式
     *
     * など揺れるので必ず安全に読む。
     *
     * @return string[]
     */
    private function readCategoriesFromItemsRow(object $itemRow): array
    {
        $raw = $itemRow->category ?? null;
        if ($raw === null) {
            return [];
        }

        // すでに配列ならそのまま
        if (is_array($raw)) {
            return array_values(array_filter(array_map(
                fn ($c) => $this->normalize((string)$c),
                $raw
            ), fn ($c) => $c !== ''));
        }

        // string 想定
        $rawStr = (string)$raw;
        $rawStr = trim($rawStr);
        if ($rawStr === '') {
            return [];
        }

        $decoded = json_decode($rawStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // JSON でない場合は 1要素扱い（運用で混入しても落とさない）
            return [$this->normalize($rawStr)];
        }

        // 二重エンコード対策
        if (is_string($decoded)) {
            $decoded2 = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $decoded2;
            }
        }

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($c) => $this->normalize((string)$c),
            $decoded
        ), fn ($c) => $c !== ''));
    }

    /**
     * @param string[] $values
     * @param array<string,string> $alias
     * @return string[]
     */
    private function applyAliasToList(array $values, array $alias): array
    {
        $out = [];
        foreach ($values as $v) {
            $n = $this->normalize((string)$v);
            if ($n === '') {
                continue;
            }
            $out[] = $alias[$n] ?? $n;
        }
        // unique + keep order
        $out = array_values(array_unique($out));
        return $out;
    }

    /**
     * extractOne: 1つだけ抽出（最長一致優先）
     *
     * @return array{0:?string,1:string,2:float}
     */
    private function extractOne(string $text, array $dict): array
    {
        if (empty($dict)) {
            return [null, $text, 0.0];
        }

        usort($dict, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($dict as $word) {
            $word = $this->normalize((string)$word);
            if ($word === '') {
                continue;
            }

            if (mb_strpos($text, $word) !== false) {
                $len = mb_strlen($word);
                $confidence = min(1.0, 0.5 + ($len / max(mb_strlen($text), 1)));

                $newText = str_replace($word, '', $text);
                $newText = $this->normalize($newText);

                return [$word, $newText, $confidence];
            }
        }

        return [null, $text, 0.0];
    }

    /**
     * extractMany: 複数抽出（最長一致優先）
     *
     * @return array{0:array<int,string>,1:string}
     */
    private function extractMany(string $text, array $dict): array
    {
        if (empty($dict)) {
            return [[], $text];
        }

        $found = [];
        usort($dict, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($dict as $word) {
            $word = $this->normalize((string)$word);
            if ($word === '') {
                continue;
            }

            if (mb_strpos($text, $word) !== false) {
                $found[] = $word;
                $text = str_replace($word, '', $text);
            }
        }

        $text = $this->normalize($text);

        // unique（順序保持）
        $found = array_values(array_unique($found));

        return [$found, $text];
    }

    /**
     * Entity ID を解決する
     *
     * - canonical_name で一致
     * - brand_entities だけ display_name でも一致（後方互換）
     * - autoCreate=true の場合は存在しなければ INSERT（未知ブランドを救う）
     */
    private function resolveEntityId(string $table, ?string $value, bool $autoCreate): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = $this->normalize($value);
        if ($value === '') {
            return null;
        }

        $q = DB::table($table)->where('canonical_name', $value);

        if ($table === 'brand_entities') {
            // 後方互換：display_name でも拾う
            $q->orWhere('display_name', $value);
        }

        $id = $q->value('id');
        if ($id) {
            return (int)$id;
        }

        if (! $autoCreate) {
            return null;
        }

        // autoCreate: ここでは brand_entities など「未知が多い」テーブルで使う想定
        // テーブルに必須カラムがある場合はプロジェクトに合わせて調整
        try {
            $newId = DB::table($table)->insertGetId([
                'canonical_name' => $value,
                'display_name'   => $value,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return (int)$newId;
        } catch (\Throwable $e) {
            // 競合（別トランザクションで先に作られた）などを吸収して再解決
            $retry = DB::table($table)->where('canonical_name', $value)->value('id');
            return $retry ? (int)$retry : null;
        }
    }

    /**
     * display_name を DB から引く（なければ fallback）
     */
    private function resolveDisplayName(string $table, ?int $entityId, string $fallback): string
    {
        $fallback = $this->normalize($fallback);
        if (! $entityId) {
            return $fallback;
        }

        $name = DB::table($table)->where('id', $entityId)->value('display_name');
        $name = is_string($name) ? trim($name) : '';
        return $name !== '' ? $name : $fallback;
    }
}
