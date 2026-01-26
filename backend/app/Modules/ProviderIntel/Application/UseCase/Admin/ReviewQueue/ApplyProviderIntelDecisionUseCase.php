<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\ReviewQueue;

use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;

final class ApplyProviderIntelDecisionUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
    ) {
    }

    /**
     * MVP: approveされた review_queue_item(ref=catalog_source) を確定扱いにする。
     *
     * - catalog_sources.approved_hash = catalog_sources.last_hash（またはsummary.new_hash）
     * - approved_at/approved_by を保存
     *
     * v4拡張:
     * - extracted_documents/document_diffs を参照して「根拠のdoc_id」を記録
     *
     * v6拡張:
     * - proposed_change（提案）に落として、approveで apply を実行する形に移行
     */
    public function handle(int $catalogSourceId, ?string $newHash, ?int $approvedBy): void
    {
        $source = $this->sources->find($catalogSourceId);
        if (!$source) {
            throw new \DomainException('CatalogSource not found');
        }

        // newHash は summary 由来。無い場合は DB の last_hash を使う
        $hash = $newHash ?: $source->lastHash();
        if (!$hash || $hash === '') {
            throw new \DomainException('No hash to approve');
        }

        $this->sources->markApproved($catalogSourceId, $hash, $approvedBy);
    }
}