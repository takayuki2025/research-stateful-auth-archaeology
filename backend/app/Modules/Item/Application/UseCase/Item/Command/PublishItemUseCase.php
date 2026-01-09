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
        DB::transaction(function () use ($input, $principal, $tenantId) {

            $draft = $this->draftRepository->findById($input->draftId);

            if (! $draft || ! $draft->isPublishableV1()) {
                throw new DomainException('Draft is not publishable');
            }

            // 出品主体（SoT）
            $sellerId = $draft->sellerId();

            if (! $this->sellerAuth->canOperate($sellerId, $principal)) {
                throw new DomainException('Not allowed to publish this item');
            }

            // shop_id 整合性
            if ($sellerId->type() === SellerType::SHOP) {
                if ($sellerId->id() === null && $input->shopId === null) {
                    throw new DomainException('shop_id is required to publish');
                }

                if (
                    $sellerId->id() !== null &&
                    $input->shopId !== null &&
                    $sellerId->id() !== $input->shopId
                ) {
                    throw new DomainException('shop_id mismatch');
                }
            }

            /**
             * ★ Publish で「事実」を確定させる
             * - price_amount は必ず int
             * - published_at は必ず now
             */
            $price = $draft->price();
            if ($price === null) {
                throw new DomainException('price is required to publish');
            }

            // Item 生成（Operational Truth）

            // ===== 画像昇格処理 =====


            $draftImageVO = $draft->itemImage(); // ItemImagePath|null
            $itemImage = null;

            if ($draftImageVO !== null) {
                $draftImagePath = $draftImageVO->value(); // string

                $itemImagePath = str_replace(
                    'item_drafts/',
                    'item_images/',
                    $draftImagePath
                );

                if (! Storage::disk('public')->exists($itemImagePath)) {
                    Storage::disk('public')->copy($draftImagePath, $itemImagePath);
                }

                // ★ 正規ルート（factory 経由）
                $itemImage = ItemImagePath::fromRaw($itemImagePath);
            }






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
                itemImage: $itemImage, // ★ ItemImagePath|null
                remain: new StockCount(1),
            );






            // ★ publish 時刻を確定

            $item->markPublished(
                new \DateTimeImmutable('now')
            );


            // 永続化
            $this->itemRepository->save($item);
            $itemId = $item->id();

            // 🔑 rawText を確定（再解析の完全 SoT）
            $rawText = trim(implode(' ', array_filter([
                $draft->name()->value(),
                $draft->explain(),
                $draft->brand()?->value() ?? null,
                $draft->condition(),
            ])));

            Event::dispatch(
                new ItemPublished(
                    itemId: $itemId,
                    rawText: $rawText,
                    tenantId: $tenantId,
                )
            );

            $draft->markPublished();
            $this->draftRepository->save($draft);
        });
    }
}
