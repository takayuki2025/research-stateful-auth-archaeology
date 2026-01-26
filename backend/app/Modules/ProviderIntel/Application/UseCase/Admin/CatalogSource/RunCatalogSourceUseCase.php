<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Service\ProviderIntelFetcher;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use App\Modules\Review\Application\UseCase\EnqueueReviewQueueItemUseCase;

use App\Modules\ProviderIntel\Application\Service\HtmlTextExtractor;
use App\Modules\ProviderIntel\Application\Service\PdfTextExtractorClient;
use App\Modules\ProviderIntel\Application\Service\SimpleDiffGenerator;

use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use App\Modules\ProviderIntel\Domain\Repository\DocumentDiffRepository;

final class RunCatalogSourceUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
        private ProviderIntelFetcher $fetcher,
        private EnqueueReviewQueueItemUseCase $enqueueReview,

        private HtmlTextExtractor $htmlExtractor,
        private PdfTextExtractorClient $pdfExtractor,

        private ExtractedDocumentRepository $docs,
        private SimpleDiffGenerator $diff,
        private DocumentDiffRepository $diffs,
    ) {}

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

        // “raw body” hash（監査/差分起点）
        $newHash = hash('sha256', $r['body']);
        $oldHash = $source->lastHash();
        $changed = ($oldHash === null) || !hash_equals($oldHash, $newHash);

        if (!$changed) {
            return [
                'changed' => false,
                'old_hash' => $oldHash,
                'new_hash' => $newHash,
                'content_type' => $r['content_type'],
            ];
        }

        // 1) text extract（html / pdf）
        $contentType = (string)($r['content_type'] ?? '');
        $sourceType = (string) $source->sourceType(); // 'html' | 'pdf'

        $text = '';
        $extractMeta = [];

        if ($sourceType === 'pdf') {
            $ex = $this->pdfExtractor->extractFromPdfBytes(
                pdfBytes: (string)$r['body'],
                sourceUrl: (string)$url,
                language: 'ja',
            );
            $text = $ex['text'];
            $extractMeta = $ex['meta'] ?? [];
        } else {
            // html (default)
            $text = $this->htmlExtractor->extract((string)$r['body']);
            $extractMeta = [];
        }

        // 2) save extracted_document (after)
        $afterDocId = $this->docs->save([
            'project_id' => $source->projectId(),
            'domain' => 'providerintel',
            'source_type' => $sourceType, // 'html'|'pdf'
            'source_url' => $source->sourceUrl(),
            'source_url_hash' => $source->sourceUrlHash(),
            'content_text' => $text,
            'content_hash' => hash('sha256', $text),
            'extracted_at' => now(),
        ]);

        // 3) find before doc（同URL hashで直近、ただし自分は除外）
        $before = null;
        if (method_exists($this->docs, 'findLatestBySourceUrlHashExcludingId')) {
            $before = $this->docs->findLatestBySourceUrlHashExcludingId(
                'providerintel',
                $source->sourceUrlHash(),
                $afterDocId
            );
        } else {
            // 旧I/F fallback（v4.1時点の保険）
            $before = $this->docs->findLatestBySourceUrlHash('providerintel', $source->sourceUrlHash());
            if ($before && (int)($before['id'] ?? 0) === $afterDocId) {
                $before = null;
            }
        }

        $beforeId = $before ? (int)($before['id'] ?? 0) : null;
        $beforeText = $before ? (string)($before['content_text'] ?? '') : null;

        // 4) diff summary（SimpleDiffGeneratorは後で差し替え）
        $diffSummary = $this->diff->summarize($beforeText, $text);

        // 初回判定をUIで読みやすくするため、最低限のメタも足す（v4.1）
        $diffSummary = array_merge($diffSummary, [
            'source_type' => $sourceType,
            'content_type' => $contentType,
            'extract_meta' => $extractMeta,
        ]);

        $diffId = $this->diffs->save(
    $source->projectId(),
    $beforeId,
    $afterDocId,
    $diffSummary,
);

        // 5) update last_hash/last_fetched_at（既存）
        $updated = $source->withLastFetch($newHash, new \DateTimeImmutable('now'));
        $this->sources->updateLastFetch($updated);

        // 6) enqueue review with doc/diff pointers
        $this->enqueueReview->handle(
            projectId: $source->projectId(),
            queueType: 'providerintel',
            refType: 'catalog_source',
            refId: $source->id(),
            priority: 50,
            summary: [
                'source_id' => $source->id(),
                'provider_id' => $source->providerId(),
                'source_type' => $sourceType,
                'source_url' => $source->sourceUrl(),
                'old_hash' => $oldHash,
                'new_hash' => $newHash,
                'content_type' => $contentType,

                // v4 pointers
                'after_document_id' => $afterDocId,
                'diff_id' => $diffId,
            ]
        );

        return [
            'changed' => true,
            'old_hash' => $oldHash,
            'new_hash' => $newHash,
            'content_type' => $contentType,
            'after_document_id' => $afterDocId,
            'diff_id' => $diffId,
        ];
    }
}