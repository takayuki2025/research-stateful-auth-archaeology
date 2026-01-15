<?php

namespace App\Modules\Item\Application\Service;

use App\Modules\Item\Infrastructure\Persistence\Query\ItemReadRepository;
use App\Modules\Item\Domain\Exception\ItemNotFoundException;
use App\Modules\Item\Infrastructure\Persistence\Query\AnalysisResultReadRepository;

final class ItemDetailReadService
{
    public function __construct(
        private readonly ItemReadRepository $items,
        private readonly AnalysisResultReadRepository $analysisResults,
    ) {
    }



    /**
     * 商品詳細（表示用 ReadModel）
     */
    public function get(int $itemId): array
{
    $row = $this->items->findWithDisplayEntities($itemId);

    if (! $row) {
        throw new ItemNotFoundException();
    }

    // 🔍 AI / 人手解析結果（あれば）
    $analysis = $this->analysisResults
        ->findLatestActiveByItemId($itemId);

    if ($analysis) {
        // display 配下に統一（フロント互換）
        $row['display'] = array_merge(
            $row['display'] ?? [],
            $analysis
        );
    }

    return $row;
}
}
