<?php

namespace App\Services\Item;

use App\Jobs\AnalyzeItemEntityWithAtlasKernel;
use App\Jobs\AnalyzeItemExplainTerms;
use App\Models\Item;


// TODO: promote to UseCase when domain stabilizes
class ItemAnalysisService
{
    public function analyzeAfterCreate(Item $item): void
    {

            throw new \RuntimeException('ItemAnalysisService is deprecated');
}
}
        // brand 正規化
//         if (!empty($item->brand)) {
//             AnalyzeItemEntityWithAtlasKernel::dispatch(
//                 itemId: $item->id,
//                 entityType: 'brand',
//                 rawValue: (string)$item->brand,
//                 knownAssetsRef: 'brands_v1'
//             );
//         }

//         // 商品説明（document_term）
//         if (!empty($item->explain)) {
//             AnalyzeItemExplainTerms::dispatch(
//                 itemId: $item->id,
//                 explain: (string)$item->explain,
//                 knownAssetsRef: 'terms_mech_v1'
//             );
//         }
//     }

//     public function analyzeAfterUpdate(Item $item): void
//     {
//         // 更新時も同じ（将来は差分検知してもOK）
//         $this->analyzeAfterCreate($item);
//     }
// }
