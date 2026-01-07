<?php

namespace App\Modules\Item\Application\UseCase\Item\Command;

use App\Modules\Item\Domain\Port\BrandNormalizationPort;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Domain\ValueObject\Brand;

final class NormalizeBrandUseCase
{
    public function __construct(
        private ItemDraftRepository $draftRepository,
        private BrandNormalizationPort $normalizer,
    ) {}

    public function execute(string $draftId): void
    {
        $draft = $this->draftRepository->findById($draftId);

        if (! $draft || $draft->brand() === null) {
            return;
        }

        // 正規化を「実行するだけ」
        $this->normalizer->normalize(
            $draft->brand()
        );

        // v1 では Draft は更新しない
    }
}