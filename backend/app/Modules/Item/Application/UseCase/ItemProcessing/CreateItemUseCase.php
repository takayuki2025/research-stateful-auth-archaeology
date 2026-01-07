<?php

namespace App\Modules\Item\Application\UseCase\ItemProcessing;


use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Application\Dto\CreateItemInput;

class CreateItemUseCase
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly ItemAnalysisPort $analysisPort
    ) {
    }

    public function execute(CreateItemInput $input)
    {
        $item = $this->itemRepository->create([
            'user_id' => $input->userId,
            'shop_id' => $input->shopId,
            'name' => $input->name,
            'price' => $input->price,
            'brand' => $input->brand,
            'explain' => $input->explain,
        ]);

        // 解析依頼（非同期）
        if ($item->brand) {
            $this->analysisPort->dispatchBrandNormalization(
                itemId: $item->id,
                rawBrand: (string) $item->brand,
                knownAssetsRef: 'brands_v1'
            );
        }

        if ($item->explain) {
            $this->analysisPort->dispatchExplainTermsExtraction(
                itemId: $item->id,
                explain: (string) $item->explain,
                knownAssetsRef: null
            );
        }

        return $item;
    }
}
