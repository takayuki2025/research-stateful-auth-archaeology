<?php

namespace App\Modules\ProviderIntel\Infrastructure\Persistence\Repository;

use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use Illuminate\Support\Facades\DB;

final class EloquentExtractedDocumentRepository implements ExtractedDocumentRepository
{
    public function save(array $attrs): int
    {
        $domain = $attrs['domain'];
        $contentHash = $attrs['content_hash'];

        $existing = DB::table('extracted_documents')
            ->where('domain', $domain)
            ->where('content_hash', $contentHash)
            ->first();

        if ($existing) {
            return (int)$existing->id;
        }

        return (int) DB::table('extracted_documents')->insertGetId([
            'project_id' => $attrs['project_id'] ?? null,
            'domain' => $domain,
            'source_type' => $attrs['source_type'],
            'source_url' => $attrs['source_url'] ?? null,
            'source_url_hash' => $attrs['source_url_hash'] ?? null,
            'content_text' => $attrs['content_text'],
            'content_hash' => $contentHash,
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
}