<?php

namespace App\Modules\Item\Application\UseCase\Item\Command;

use App\Modules\Item\Domain\Repository\ItemRepository;


final class DeleteItemUseCase
{
    public function __construct(
        private ItemRepository $itemRepository,
        private SellerAuthorizationService $authorization,
    ) {
    }

    public function execute(
        int $itemId,
        AuthPrincipal $principal,
    ): void {
        $item = $this->itemRepository->findById($itemId);

        if (! $item) {
            throw new DomainException('Item not found');
        }

        if (! $this->authorization->canOperate(
            $item->sellerId(),
            $principal
        )) {
            throw new DomainException('Forbidden');
        }

        $this->itemRepository->delete($itemId);
    }
}
