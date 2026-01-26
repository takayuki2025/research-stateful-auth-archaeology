<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Service\ProviderIntelFetcher;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use App\Modules\Review\Application\UseCase\EnqueueReviewQueueItemUseCase;

use App\Modules\ProviderIntel\Application\Service\HtmlTextExtractor;
use App\Modules\ProviderIntel\Application\Service\SimpleDiffGenerator;
use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use App\Modules\ProviderIntel\Domain\Repository\DocumentDiffRepository;

final class RunCatalogSourceUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
        private ProviderIntelFetcher $fetcher,
        private EnqueueReviewQueueItemUseCase $enqueueReview,

        private HtmlTextExtractor $extractor,
        private ExtractedDocumentRepository $docs,
        private SimpleDiffGenerator $diff,
        private DocumentDiffRepository $diffs,
    ) {
    }

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

        // raw body hash（差分判定の一次）
        $rawBody = (string)($r['body'] ?? '');
        $contentType = (string)($r['content_type'] ?? '');

        $newHash = hash('sha256', $rawBody);
        $oldHash = $source->lastHash();

        $changed = ($oldHash === null) || !hash_equals($oldHash, $newHash);

        if ($changed) {
            // 1) fulltext_extract（HTML→テキスト）
            $text = $this->extractor->extract($rawBody);

            // 2) extracted_documents(after) 保存
            $afterDocId = $this->docs->save([
                'project_id' => $source->projectId(),
                'domain' => 'providerintel',
                'source_type' => 'html',
                'source_url' => $source->sourceUrl(),
                'source_url_hash' => $source->sourceUrlHash(),
                'content_text' => $text,
                'content_hash' => hash('sha256', $text),
                'extracted_at' => now(),
            ]);

            // 3) before doc を同URLで探す（MVP）
            $before = $this->docs->findLatestBySourceUrlHash('providerintel', $source->sourceUrlHash());

            $beforeText = null;
            $beforeId = null;
            if ($before && (int)($before['id'] ?? 0) !== $afterDocId) {
                $beforeId = (int)$before['id'];
                $beforeText = (string)($before['content_text'] ?? '');
            }

            // 4) diff 生成 & 保存
            $diffSummary = $this->diff->summarize($beforeText, $text);
            $diffId = $this->diffs->save(
                $source->projectId(),
                $beforeId,
                $afterDocId,
                $diffSummary
            );

            // 5) catalog_sources の last_hash/last_fetched_at 更新（既存挙動）
            $updated = $source->withLastFetch($newHash, new \DateTimeImmutable('now'));
            $this->sources->updateLastFetch($updated);

            // 6) review_queue へ enqueue（doc/diff ポインタ付き）
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
                    'content_type' => $contentType,

                    // ✅ v4 pointers
                    'after_document_id' => $afterDocId,
                    'diff_id' => $diffId,
                ]
            );
        }

        return [
            'changed' => $changed,
            'old_hash' => $oldHash,
            'new_hash' => $newHash,
            'content_type' => $contentType,
        ];
    }
}