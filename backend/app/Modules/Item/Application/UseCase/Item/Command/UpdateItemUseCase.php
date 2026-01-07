<?php

namespace App\Modules\Item\Application\UseCase\Item\Command;

use App\Modules\Item\Application\Dto\Item\UpdateItemInputDto;
use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\ValueObject\CategoryList;
use App\Modules\Item\Domain\ValueObject\ItemImagePath;
use App\Modules\Item\Domain\ValueObject\Price;
use App\Modules\Item\Domain\ValueObject\StockCount;
use App\Modules\Item\Domain\Exception\TenantMismatchException;
use App\Modules\Item\Domain\Service\BrandNormalizationService;
use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use RuntimeException;

final class UpdateItemUseCase
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly BrandNormalizationService $brandNormalizer,
        private readonly ItemEntityTagRepository $itemEntityTagRepository,
    ) {
    }

    public function execute(UpdateItemInputDto $dto): void
    {
        $tenantId = request()->attributes->get('tenant_id');

        $item = $this->itemRepository->findById($dto->itemId);
        if (! $item) {
            throw new RuntimeException('Item not found');
        }

        // owner check
        if ($item->getUserId() !== $dto->userId) {
            throw new RuntimeException('Forbidden');
        }

        // tenant check
        if ($item->getShopId() !== null
            && $tenantId !== null
            && (int)$item->getShopId() !== (int)$tenantId) {
            throw new TenantMismatchException((int)$tenantId, (int)$item->getShopId());
        }

        // Item 自体は「事実データ」のみ更新
        $updated = $item->withUpdatedFields(
            name: $dto->name,
            price: new Price($dto->price),
            explain: $dto->explain,
            category: new CategoryList($dto->category),
            itemImage: ItemImagePath::fromRaw(
                $dto->itemImagePath ?? $item->getItemImage()?->value()
            ),
            remain: new StockCount($dto->remain),
        );

        $this->itemRepository->save($updated);

        // ★ AtlasKernel 正規化（INSERT 相当）
        $brands = $this->brandNormalizer->normalize($dto->brandsRaw);

        $this->itemEntityTagRepository->replaceByItemId(
            itemId: $updated->getId()->getValue(),
            tagType: 'brand',
            entities: $brands
        );
    }
}
