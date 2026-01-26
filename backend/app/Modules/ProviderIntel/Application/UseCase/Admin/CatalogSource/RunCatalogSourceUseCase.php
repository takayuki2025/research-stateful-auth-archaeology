<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Service\ProviderIntelFetcher;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use App\Modules\Review\Application\UseCase\EnqueueReviewQueueItemUseCase;

final class RunCatalogSourceUseCase
{
    public function __construct(
    private CatalogSourceRepository $sources,
    private ProviderIntelFetcher $fetcher,
    private EnqueueReviewQueueItemUseCase $enqueueReview,
) {}

    /**
     * MVP: fetch -> compute content_hash -> update catalog_sources.last_hash/last_fetched_at if changed.
     * Returns: ['changed' => bool, 'new_hash' => string, 'old_hash' => string|null]
     */
    public function handle(int $sourceId): array
    {
        $source = $this->sources->find($sourceId);
        if (!$source) {
            throw new \DomainException('CatalogSource not found');
        }

        $url = $source->sourceUrl();
        if (!$url) {
            throw new \DomainException('source_url missing');
        }

        $r = $this->fetcher->fetch($url);

        // content hash (raw body)
        $newHash = hash('sha256', $r['body']);
        $oldHash = $source->lastHash();

        $changed = ($oldHash === null) || !hash_equals($oldHash, $newHash);

        if ($changed) {
            $updated = $source->withLastFetch($newHash, new \DateTimeImmutable('now'));
            $this->sources->updateLastFetch($updated);

            // v3.3/v4: here is where you'd enqueue review_queue_items / extracted_documents.
            // v3.3: enqueue review item
// queue_type=providerintel, ref_type=catalog_source
$this->enqueueReview->handle(
    projectId: $source->projectId(),
    queueType: 'providerintel',
    refType: 'catalog_source',
    refId: $source->id(),
    priority: 50,
    summary: [
        'source_id' => $source->id(),
        'provider_id' => $source->providerId(),
        'source_type' => $source->sourceType(),
        'source_url' => $source->sourceUrl(),
        'old_hash' => $oldHash,
        'new_hash' => $newHash,
        'content_type' => $r['content_type'],
    ]
);
        }

        return [
            'changed' => $changed,
            'old_hash' => $oldHash,
            'new_hash' => $newHash,
            'content_type' => $r['content_type'],
        ];
    }
}