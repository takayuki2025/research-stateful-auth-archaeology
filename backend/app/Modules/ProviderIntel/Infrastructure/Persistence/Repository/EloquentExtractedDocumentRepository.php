<?php

namespace App\Modules\ProviderIntel\Infrastructure\Persistence\Repository;

use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use Illuminate\Support\Facades\DB;

final class EloquentExtractedDocumentRepository implements ExtractedDocumentRepository
{
    public function save(array $attrs): int
    {
        $domain = (string)($attrs['domain'] ?? '');
        $contentHash = (string)($attrs['content_hash'] ?? '');

        if ($domain === '' || $contentHash === '') {
            throw new \InvalidArgumentException('domain/content_hash is required');
        }

        // Fast path
        $existingId = DB::table('extracted_documents')
            ->where('domain', $domain)
            ->where('content_hash', $contentHash)
            ->value('id');

        if ($existingId) {
            return (int)$existingId;
        }

        // Race-safe insert
        try {
            return (int) DB::table('extracted_documents')->insertGetId([
                'project_id'      => $attrs['project_id'] ?? null,
                'domain'          => $domain,
                'source_type'     => (string)($attrs['source_type'] ?? ''),
                'source_url'      => $attrs['source_url'] ?? null,
                'source_url_hash' => $attrs['source_url_hash'] ?? null,
                'content_text'    => (string)($attrs['content_text'] ?? ''),
                'content_hash'    => $contentHash,
                'extracted_at'    => $attrs['extracted_at'] ?? now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // If duplicate key (domain, content_hash), re-fetch and return id
            // MySQL duplicate key: SQLSTATE 23000
            $code = (string)($e->getCode() ?? '');
            if ($code === '23000') {
                $id = DB::table('extracted_documents')
                    ->where('domain', $domain)
                    ->where('content_hash', $contentHash)
                    ->value('id');

                if ($id) {
                    return (int)$id;
                }
            }

            throw $e;
        }
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