<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Item\Application\UseCase\Item\Query\ListMyItemsUseCase;
use App\Modules\Item\Presentation\Http\Resources\ItemResource;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;

final class ItemListController extends Controller
{
    public function __invoke(
        Request $request,
        ListMyItemsUseCase $useCase
    ) {
        /** @var AuthPrincipal $principal */
        $principal = $request->attributes->get('auth_principal');

        if (!$principal) {
            abort(401);
        }

        // Domain の UseCase を必ず通す
        $items = $useCase->execute(
            userId: $principal->userId(),
            shopId: $principal->shopId
        );

        return response()->json([
            'items' => array_map(
                fn ($item) => ItemResource::fromDomain($item),
                $items->all()
            ),
        ]);
    }
}
