<?php

namespace App\Modules\ProviderIntel\Domain\Repository;

interface ExtractedDocumentRepository
{
    /**
     * Save extracted document and return id.
     * If same domain+content_hash exists, returns existing id.
     */
    public function save(array $attrs): int;

    public function find(int $id): ?array;

    public function findLatestBySourceUrlHash(string $domain, string $sourceUrlHash): ?array;
}