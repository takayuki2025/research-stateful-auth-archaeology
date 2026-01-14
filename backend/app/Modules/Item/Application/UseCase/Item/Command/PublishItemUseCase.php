<?php

namespace App\Modules\Item\Application\UseCase\Item\Command;

use App\Modules\Item\Application\Dto\Item\PublishItemInput;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\ValueObject\ItemImagePath;
use App\Modules\Item\Domain\Entity\Item;
use App\Modules\Item\Domain\Service\SellerAuthorizationService;
use App\Modules\Item\Domain\ValueObject\StockCount;
use App\Modules\Item\Domain\ValueObject\SellerType;
use App\Modules\Item\Domain\ValueObject\ItemOrigin;
use App\Modules\Item\Domain\Event\ItemPublished;
use App\Modules\Item\Domain\ValueObject\ItemOrigin as ItemOriginVO;
// use App\Modules\Item\Domain\Enum\ItemOrigin as ItemOriginEnum;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Modules\Item\Application\Event\ItemImported;
use DomainException;

final class PublishItemUseCase
{
    public function __construct(
        private ItemDraftRepository $draftRepository,
        private ItemRepository $itemRepository,
        private SellerAuthorizationService $sellerAuth,
    ) {
    }

    public function execute(
    PublishItemInput $input,
    AuthPrincipal $principal,
    ?int $tenantId,
): void {
    $itemId = null;
    $rawText = null;

    DB::transaction(function () use ($input, $principal, &$itemId, &$rawText) {

        $draft = $this->draftRepository->findById($input->draftId);

        if (! $draft || ! $draft->isPublishableV1()) {
            throw new DomainException('Draft is not publishable');
        }

        $sellerId = $draft->sellerId();

        if (! $this->sellerAuth->canOperate($sellerId, $principal)) {
            throw new DomainException('Not allowed to publish this item');
        }

        if ($sellerId->type() === SellerType::SHOP) {
            if ($sellerId->id() === null && $input->shopId === null) {
                throw new DomainException('shop_id is required to publish');
            }
            if ($sellerId->id() !== null && $input->shopId !== null && $sellerId->id() !== $input->shopId) {
                throw new DomainException('shop_id mismatch');
            }
        }

        $price = $draft->price();
        if ($price === null) {
            throw new DomainException('price is required to publish');
        }

        // 画像昇格
        $itemImage = null;
        if ($draftImageVO = $draft->itemImage()) {
            $draftImagePath = $draftImageVO->value();
            $itemImagePath = str_replace('item_drafts/', 'item_images/', $draftImagePath);

            if (! Storage::disk('public')->exists($itemImagePath)) {
                Storage::disk('public')->copy($draftImagePath, $itemImagePath);
            }

            $itemImage = ItemImagePath::fromRaw($itemImagePath);
        }

        // Item 作成
        $item = Item::createNew(
            itemOrigin: ItemOriginVO::from(
                $sellerId->type() === SellerType::SHOP
                    ? ItemOriginVO::SHOP_MANAGED
                    : ItemOriginVO::USER_PERSONAL
            ),
            shopId: $sellerId->type() === SellerType::SHOP
                ? ($sellerId->id() ?? $input->shopId)
                : null,
            createdByUserId: $sellerId->type() === SellerType::SHOP
                ? null
                : $principal->userId(),
            name: $draft->name()->value(),
            price: $price,
            explain: $draft->explain(),
            condition: $draft->condition(),
            category: $draft->category(),
            itemImage: $itemImage,
            remain: new StockCount(1),
        );

        $item->markPublished(new \DateTimeImmutable('now'));

        $this->itemRepository->save($item);
        $itemId = $item->id();

        // 🔑 rawText（純粋データのみ）
        $rawText = trim(implode(' ', array_filter([
            $draft->name()->value(),
            $draft->explain(),
            $draft->brand()?->value(),
            $draft->condition(),
        ])));

        // Draft publish
        $draft->markPublished();
        $this->draftRepository->save($draft);
    });

    // 🔥 transaction 完了後に dispatch（最重要）
    Event::dispatch(
        new ItemImported(
            itemId: $itemId,
            rawText: $rawText,
            tenantId: $tenantId,
            source: 'publish',
        )
    );
}
}
