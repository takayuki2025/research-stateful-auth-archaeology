<?php

namespace App\Modules\ProviderIntel\Infrastructure\Persistence\Repository;

use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use Illuminate\Support\Facades\DB;

final class EloquentExtractedDocumentRepository implements ExtractedDocumentRepository
{
    public function save(array $attrs): int
{
    return (int) DB::table('extracted_documents')->insertGetId([
        'project_id' => $attrs['project_id'] ?? null,
        'domain' => $attrs['domain'],
        'source_type' => $attrs['source_type'],
        'source_url' => $attrs['source_url'] ?? null,
        'source_url_hash' => $attrs['source_url_hash'] ?? null,
        'content_text' => $attrs['content_text'],
        'content_hash' => $attrs['content_hash'],
        'extracted_at' => $attrs['extracted_at'] ?? now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

    public function find(int $id): ?array
    {
        $r = DB::table('extracted_documents')->where('id', $id)->first();
        return $r ? (array)$r : null;
    }

    public function findLatestBySourceUrlHash(string $domain, string $sourceUrlHash): ?array
    {
        $r = DB::table('extracted_documents')
            ->where('domain', $domain)
            ->where('source_url_hash', $sourceUrlHash)
            ->orderByDesc('id')
            ->first();

        return $r ? (array)$r : null;
    }

    public function findLatestBySourceUrlHashExcludingId(string $domain, string $sourceUrlHash, int $excludeId): ?array
{
    $r = DB::table('extracted_documents')
        ->where('domain', $domain)
        ->where('source_url_hash', $sourceUrlHash)
        ->where('id', '<>', $excludeId)
        ->orderByDesc('id')
        ->first();

    return $r ? (array)$r : null;
}
}