<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class BrandEntitySeeder extends Seeder
{
    private function tsvPath(): string
    {
        $assetsDir = config('atlaskernel.assets_path');
        return rtrim((string)$assetsDir, '/').'/brands_canon_v1.tsv';
    }

    public function run(): void
    {
        $path = $this->tsvPath();
        if (!file_exists($path)) {
            throw new \RuntimeException("brands_canon_v1.tsv not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            throw new \RuntimeException("brands_canon_v1.tsv empty: {$path}");
        }

        DB::transaction(function () use ($lines) {

            // =========================================================
            // 1) canonical upsert（正）
            // - canonical行は (canonical_name, display_name=canonical) で一意
            // - synonyms_json に aliases を保存（Python正規化と整合）
            // =========================================================
            foreach ($lines as $line) {
                $parsed = $this->parseLineRobust($line);
                if ($parsed === null) continue;

                [$canonical, $normalizedKey, $aliases] = $parsed;

                DB::table('brand_entities')->updateOrInsert(
                    [
                        'canonical_name' => $canonical,
                        'display_name'   => $canonical, // ★ canonical行の固定キー
                    ],
                    [
                        'normalized_key' => $normalizedKey ?: $this->normalizeKey($canonical),
                        'is_primary'     => true,
                        'merged_to_id'   => null,
                        // ★ aliases を canonical行に詰める（resolveCanonicalByName の JSON検索で使える）
                        'synonyms_json'  => !empty($aliases)
                            ? json_encode(array_values($aliases), JSON_UNESCAPED_UNICODE)
                            : null,
                        'created_from'   => 'seed',
                        'source'         => 'ai',
                        'updated_at'     => now(),
                        'created_at'     => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            // canonical id map（canonical_name -> canonical row id）
            // canonical行だけに絞る（display_name=canonical_name）
            $canonIdMap = DB::table('brand_entities')
                ->where('is_primary', true)
                ->whereColumn('canonical_name', 'display_name')
                ->pluck('id', 'canonical_name')
                ->all();

            // =========================================================
            // 2) alias upsert（display_name に alias）
            // - alias行は (canonical_name, display_name=alias) で一意
            // - merged_to_id は canonical id
            // =========================================================
            foreach ($lines as $line) {
                $parsed = $this->parseLineRobust($line);
                if ($parsed === null) continue;

                [$canonical, $normalizedKey, $aliases] = $parsed;

                $canonicalId = $canonIdMap[$canonical] ?? null;
                if (!$canonicalId) continue;

                foreach ($aliases as $alias) {
                    $alias = trim($alias);
                    if ($alias === '' || $alias === $canonical) continue;

                    DB::table('brand_entities')->updateOrInsert(
                        [
                            'canonical_name' => $canonical,
                            'display_name'   => $alias,
                        ],
                        [
                            'normalized_key' => $this->normalizeKey($alias),
                            'is_primary'     => false,
                            'merged_to_id'   => (int)$canonicalId,
                            'synonyms_json'  => null,
                            'created_from'   => 'seed_alias',
                            'source'         => 'ai',
                            'updated_at'     => now(),
                            'created_at'     => DB::raw('COALESCE(created_at, NOW())'),
                        ]
                    );
                }
            }

            // =========================================================
            // 3) 修復フェーズ（壊れデータを強制整合）
            // =========================================================

            // 3-1) self-merged を除去（merged_to_id = id は壊れ）
            DB::table('brand_entities')
                ->whereColumn('merged_to_id', 'id')
                ->update(['merged_to_id' => null, 'updated_at' => now()]);

            // 3-2) canonical行（display_name=canonical_name）を primary に固定
            DB::table('brand_entities')
                ->whereColumn('canonical_name', 'display_name')
                ->update([
                    'is_primary'   => 1,
                    'merged_to_id' => null,
                    'updated_at'   => now(),
                ]);

            // 3-3) alias行（display_name!=canonical_name）を non-primary に固定
            DB::table('brand_entities')
                ->whereRaw('canonical_name <> display_name')
                ->update([
                    'is_primary' => 0,
                    'updated_at' => now(),
                ]);

            // 3-4) alias行の merged_to_id が null のものを canonical に寄せる（保険）
            $canonMap = DB::table('brand_entities')
                ->where('is_primary', 1)
                ->whereColumn('canonical_name', 'display_name')
                ->pluck('id', 'canonical_name')
                ->all();

            $aliases = DB::table('brand_entities')
                ->where('is_primary', 0)
                ->whereRaw('canonical_name <> display_name')
                ->get(['id', 'canonical_name', 'merged_to_id']);

            foreach ($aliases as $a) {
                $canonId = $canonMap[$a->canonical_name] ?? null;
                if (!$canonId) continue;

                $merged = $a->merged_to_id ? (int)$a->merged_to_id : null;
                if ($merged === null) {
                    DB::table('brand_entities')
                        ->where('id', (int)$a->id)
                        ->update([
                            'merged_to_id' => (int)$canonId,
                            'updated_at'   => now(),
                        ]);
                }
            }
        });
    }

    /**
     * 正常: canonical<TAB>normalized_key<TAB>aliases(comma)
     * 崩れ: "Starbucks    スターバックス" (space)
     *
     * returns [canonical, normalizedKey, aliases[]]
     */
    private function parseLineRobust(string $line): ?array
    {
        $raw = $line;
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) return null;

        // NBSP 等を通常スペースへ
        $line = str_replace("\xC2\xA0", " ", $line);

        // TSV 優先
        if (str_contains($line, "\t")) {
            $parts = explode("\t", $line);

            $canonical = trim($parts[0] ?? '');
            if ($canonical === '') return null;

            $normalizedKey = trim($parts[1] ?? '');
            $aliasesRaw = trim($parts[2] ?? '');

            $aliases = [];
            if ($aliasesRaw !== '') {
                $aliases = array_values(array_filter(array_map('trim', explode(',', $aliasesRaw))));
            }

            return [$canonical, $normalizedKey, $aliases];
        }

        // space fallback
        $tokens = preg_split('/\s{2,}|\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($tokens) >= 2) {
            $canonical = trim($tokens[0]);
            $second = trim($tokens[1]);
            if ($canonical === '') return null;

            $normalizedKey = $this->normalizeKey($canonical);
            $aliases = array_values(array_filter(array_map('trim', explode(',', $second))));
            return [$canonical, $normalizedKey, $aliases];
        }

        logger()->warning('[BrandEntitySeeder] skipped malformed line', ['line' => $raw]);
        return null;
    }

    private function normalizeKey(string $s): string
    {
        $s = trim($s);
        $s = mb_convert_kana($s, 'as', 'UTF-8');
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        $s = trim($s);
        return Str::of($s)->lower()->toString();
    }
}