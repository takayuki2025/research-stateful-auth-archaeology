<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Presentation\Http\Presenters\ItemPresenter;
use App\Modules\Shop\Application\Dto\ShopContext;
use Illuminate\Http\Request;

final class ShopItemListController extends Controller
{
    public function __invoke(
        Request $request,
        ItemRepository $items
    ) {
        /** @var ShopContext|null $ctx */
        $ctx = $request->attributes->get(ShopContext::class);

        \Log::info('[API] ShopItemListController called', [
        'has_ctx' => (bool) $ctx,
        'shop_id' => $ctx?->shopId ?? null,
        'path' => $request->path(),
    ]);

        if (!$ctx) {
            abort(500, 'ShopContext not resolved');
        }

        $list = $items->findPublicByShopId($ctx->shopId);
        \Log::info('[API] items fetched', [
        'count' => count($list),
    ]);

        return response()->json([
            'items' => array_map(
                static fn ($item) => ItemPresenter::fromEntity($item),
                $list
            ),
        ]);
    }
}