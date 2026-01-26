<?php

namespace App\Modules\ProviderIntel\Infrastructure\Persistence\Repository;

use App\Modules\ProviderIntel\Domain\Entity\CatalogSource;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use Illuminate\Support\Facades\DB;

final class EloquentCatalogSourceRepository implements CatalogSourceRepository
{
    public function list(?int $providerId, ?string $status, int $limit, int $offset): array
    {
        $q = DB::table('catalog_sources')
            ->orderByDesc('id')
            ->limit($limit)
            ->offset($offset);

        if ($providerId !== null) $q->where('provider_id', $providerId);
        if ($status !== null) $q->where('status', $status);

        return $q->get()->map(fn($r) => $this->toEntity($r))->all();
    }

    public function find(int $id): ?CatalogSource
    {
        $r = DB::table('catalog_sources')->where('id', $id)->first();
        return $r ? $this->toEntity($r) : null;
    }

    public function upsert(CatalogSource $source): int
    {
        // Prefer unique key: (provider_id, source_url_hash)
        // If $source has id, update by id; else upsert by unique.
        if ($source->id() !== null) {
            DB::table('catalog_sources')
                ->where('id', $source->id())
                ->update([
                    'project_id' => $source->projectId(),
                    'provider_id' => $source->providerId(),
                    'source_type' => $source->sourceType(),
                    'source_url' => $source->sourceUrl(),
                    'source_url_hash' => $source->sourceUrlHash(),
                    'update_frequency' => $source->updateFrequency(),
                    'status' => $source->status(),
                    'notes' => $source->notes(),
                    'updated_at' => now(),
                ]);
            return (int)$source->id();
        }

        // Try find by unique
        $existing = DB::table('catalog_sources')
            ->where('provider_id', $source->providerId())
            ->where('source_url_hash', $source->sourceUrlHash())
            ->first();

        if ($existing) {
            DB::table('catalog_sources')
                ->where('id', $existing->id)
                ->update([
                    'project_id' => $source->projectId(),
                    'source_type' => $source->sourceType(),
                    'source_url' => $source->sourceUrl(),
                    'update_frequency' => $source->updateFrequency(),
                    'status' => $source->status(),
                    'notes' => $source->notes(),
                    'updated_at' => now(),
                ]);
            return (int)$existing->id;
        }

        return (int) DB::table('catalog_sources')->insertGetId([
            'project_id' => $source->projectId(),
            'provider_id' => $source->providerId(),
            'source_type' => $source->sourceType(),
            'source_url' => $source->sourceUrl(),
            'source_url_hash' => $source->sourceUrlHash(),
            'update_frequency' => $source->updateFrequency(),
            'status' => $source->status(),
            'last_fetched_at' => null,
            'last_hash' => null,
            'notes' => $source->notes(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updateLastFetch(CatalogSource $source): void
    {
        if ($source->id() === null) {
            throw new \InvalidArgumentException('CatalogSource id missing');
        }

        DB::table('catalog_sources')
            ->where('id', $source->id())
            ->update([
                'last_hash' => $source->lastHash(),
                'last_fetched_at' => $source->lastFetchedAt()?->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);
    }

    private function toEntity(object $r): CatalogSource
    {
        return new CatalogSource(
            id: (int)$r->id,
            projectId: $r->project_id !== null ? (int)$r->project_id : null,
            providerId: (int)$r->provider_id,
            sourceType: (string)$r->source_type,
            sourceUrl: $r->source_url !== null ? (string)$r->source_url : null,
            sourceUrlHash: (string)$r->source_url_hash,
            updateFrequency: (string)$r->update_frequency,
            status: (string)$r->status,
            lastFetchedAt: $r->last_fetched_at ? new \DateTimeImmutable((string)$r->last_fetched_at) : null,
            lastHash: $r->last_hash ? (string)$r->last_hash : null,
            notes: $r->notes !== null ? (string)$r->notes : null,
        );
    }
}