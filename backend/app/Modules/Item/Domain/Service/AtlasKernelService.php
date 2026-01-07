<?php

namespace App\Modules\Item\Domain\Service;

use DomainException;
use Illuminate\Support\Facades\DB;

final class AtlasKernelService
{
    private string $assetPath;

    /** AtlasKernel generated version */
    private const GENERATED_VERSION = 'v1.6-stable';

    public function __construct()
    {
        $this->assetPath = base_path('../python_batch/atlaskernel/src/atlaskernel/assets');
    }

    /**
     * AtlasKernel v1.6-stable
     *
     * - items の「事実」を SoT としつつ
     * - item_entities / item_entity_tags を Operational Truth として生成する
     *
     * @param int $itemId
     * @param string $rawText 解析対象（例: "Apple ほぼ新品 黒" + explain 等を合成して渡す想定）
     * @param ?int $tenantId 現状未使用でもOK（将来 multi-tenant 用）
     */
    public function analyze(
        int $itemId,
        string $rawText,
        ?int $tenantId = null
    ): void {
        if ($itemId <= 0) {
            throw new DomainException('Invalid itemId');
        }

        DB::transaction(function () use ($itemId, $rawText, $tenantId) {
            // --------------------------------------------------
            // 0) 前提：items の存在確認（最低限）
            // --------------------------------------------------
            $itemRow = DB::table('items')->where('id', $itemId)->first();
            if (! $itemRow) {
                throw new DomainException('Item not found');
            }

            // --------------------------------------------------
            // 1) 正規化 & RawText 構築（空でも破綻しない）
            // --------------------------------------------------
            $rawText = (string) $rawText;
            $rawText = trim($rawText);

            // rawText が薄い場合は explain/name/brand 等の「事実」も混ぜて解析精度を底上げ
            // （ただし items の各列が null の場合もあるので安全に連結）
            if ($rawText === '') {
                $rawText = trim(implode(' ', array_filter([
                    (string)($itemRow->name ?? ''),
                    (string)($itemRow->explain ?? ''),
                    (string)($itemRow->brand ?? ''),
                    (string)($itemRow->condition ?? ''),
                    (string)($itemRow->color ?? ''),
                ])));
            }

            $text = $this->normalize($rawText);

            // --------------------------------------------------
            // 2) Confidence 初期値（必ず保存する）
            // --------------------------------------------------
            $confidence = [
                'brand'     => 0.0,
                'condition' => 0.0,
                'color'     => 0.0,
                'category'  => 0.0,
            ];

            // --------------------------------------------------
            // 3) 辞書ロード（存在しなくても落とさない）
            // --------------------------------------------------
            // brand
            $brandDict  = $this->loadDict('brands_v1.txt');
            $brandAlias = $this->loadAlias('brand_alias.txt');

            // condition / color
            $conditionDict = $this->loadDict('conditions_v1.txt');
            $colorDict     = $this->loadDict('colors_v1.txt');

            // category（items.category を SoT として mirror するので抽出は補助扱い）
            $categoryDict  = $this->loadDict('categories_v1.txt');
            $categoryAlias = $this->loadAlias('category_alias.txt');

            // brand search dict = dict + alias keys
            $brandSearchDict = array_values(array_unique(array_merge(
                $brandDict,
                array_keys($brandAlias)
            )));

            // condition/color も alias 追加したくなったら同様に merge できる構造
            // --------------------------------------------------
            // 4) カテゴリ（SoT: items.category）を確定（漏れ防止）
            // --------------------------------------------------
            $categories = $this->readCategoriesFromItemsRow($itemRow);

            // category alias（canonical 化）
            $categories = $this->applyAliasToList($categories, $categoryAlias);

            // --------------------------------------------------
            // 5) 前処理ログ
            // --------------------------------------------------
            logger()->info('[AtlasKernel] input', [
                'item_id' => $itemId,
                'raw'     => $rawText,
                'norm'    => $text,
                'categories' => $categories,
                'tenant_id'  => $tenantId,
            ]);

            // --------------------------------------------------
            // 6) 抽出（優先順：condition → color → brands）
            // --------------------------------------------------
            // condition
            [$condition, $textAfterCondition, $confCondition] = $this->extractOne($text, $conditionDict);
            $confidence['condition'] = $confCondition;
            $text = $textAfterCondition;

            // color
            [$color, $textAfterColor, $confColor] = $this->extractOne($text, $colorDict);
            $confidence['color'] = $confColor;
            $text = $textAfterColor;

            // brands（複数）
            [$brands, $textAfterBrands] = $this->extractMany($text, $brandSearchDict);
            $text = $textAfterBrands;
            $confidence['brand'] = count($brands) > 0 ? 0.9 : 0.0;

            // brand alias 正規化（canonical 化）
            $brands = $this->applyAliasToList($brands, $brandAlias);

            // primary brand = 先頭
            $primaryBrand = $brands[0] ?? null;

            // category confidence：items.category から mirror なので最大
            $confidence['category'] = count($categories) > 0 ? 1.0 : 0.0;

            logger()->info('[AtlasKernel] extracted', [
                'item_id'    => $itemId,
                'brands'     => $brands,
                'condition'  => $condition,
                'color'      => $color,
                'categories' => $categories,
                'confidence' => $confidence,
            ]);

            // --------------------------------------------------
            // 7) entity resolve（未知は null でも良い / 必要なら作る）
            // --------------------------------------------------
            // 方針：
            // - brand_entities は「未知ブランド」が多いので、必要なら entity を作成しても良い
            // - condition/color/category は運用上「辞書に寄せる」想定が多いので、まずは null 許容
            //
            // ※ ここはプロジェクト方針に合わせて toggle できるようにしてある
            $autoCreateBrandEntity = true;

            $brandId = $this->resolveEntityId('brand_entities', $primaryBrand, $autoCreateBrandEntity);
            $conditionId = $this->resolveEntityId('condition_entities', $condition, false);
            $colorId = $this->resolveEntityId('color_entities', $color, false);

            // --------------------------------------------------
            // 8) item_entities（latest スナップショット）確定
            // --------------------------------------------------
            DB::table('item_entities')
                ->where('item_id', $itemId)
                ->where('is_latest', true)
                ->update(['is_latest' => false, 'updated_at' => now()]);

            DB::table('item_entities')->insert([
                'item_id'             => $itemId,
                'brand_entity_id'     => $brandId,
                'condition_entity_id' => $conditionId,
                'color_entity_id'     => $colorId,
                'confidence'          => json_encode($confidence, JSON_UNESCAPED_UNICODE),
                'is_latest'           => true,
                'generated_version'   => self::GENERATED_VERSION,
                'generated_at'        => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // --------------------------------------------------
            // 9) item_entity_tags（完全に作り直す：漏れ防止）
            // --------------------------------------------------
            DB::table('item_entity_tags')->where('item_id', $itemId)->delete();

            // 9-1) brands (複数)
            foreach ($brands as $b) {
                $entityId = $this->resolveEntityId('brand_entities', $b, $autoCreateBrandEntity);

                $displayName = $this->resolveDisplayName('brand_entities', $entityId, $b);

                DB::table('item_entity_tags')->insert([
                    'item_id'      => $itemId,
                    'tag_type'     => 'brand',
                    'entity_id'    => $entityId,
                    'display_name' => $displayName,
                    'confidence'   => 0.9,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // 9-2) condition
            if ($condition !== null && $condition !== '') {
                DB::table('item_entity_tags')->insert([
                    'item_id'      => $itemId,
                    'tag_type'     => 'condition',
                    'entity_id'    => $conditionId,
                    'display_name' => $condition,
                    'confidence'   => $confidence['condition'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // 9-3) color
            if ($color !== null && $color !== '') {
                DB::table('item_entity_tags')->insert([
                    'item_id'      => $itemId,
                    'tag_type'     => 'color',
                    'entity_id'    => $colorId,
                    'display_name' => $color,
                    'confidence'   => $confidence['color'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // 9-4) categories（SoT mirror）
            $insertedCategories = 0;
            foreach ($categories as $cat) {
                $entityId = $this->resolveEntityId('category_entities', $cat, false);

                DB::table('item_entity_tags')->insert([
                    'item_id'      => $itemId,
                    'tag_type'     => 'category',
                    'entity_id'    => $entityId,
                    'display_name' => $cat,
                    'confidence'   => 1.0, // mirror は最大
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $insertedCategories++;
            }

            logger()->info('[AtlasKernel] category mirror inserted', [
                'item_id' => $itemId,
                'count'   => $insertedCategories,
            ]);

            // --------------------------------------------------
            // 10) audit（必ず残す）
            // --------------------------------------------------
            DB::table('item_entity_audits')->insert([
                'item_id'    => $itemId,
                'confidence' => json_encode($confidence, JSON_UNESCAPED_UNICODE),
                'raw_text'   => $rawText,
                'created_at' => now(),
            ]);
        });
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
