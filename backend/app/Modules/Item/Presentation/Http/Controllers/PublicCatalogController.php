<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Item\Application\UseCase\Item\Query\CatalogItemListUseCase;
use App\Modules\Item\Application\Dto\Item\ListItemsInputDto;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\Auth\Application\Service\AuthContext;

final class PublicCatalogController extends Controller
{
    public function __invoke(
        Request $request,
        CatalogItemListUseCase $useCase,
        AuthContext $authContext,
    ) {
        /** @var AuthPrincipal|null $principal */
        $principal = $authContext->principalOrNull(); // ★ 修正点

        $input = new ListItemsInputDto(
            limit: 20,
            page: (int) $request->query('page', 1),
            keyword: null,
            viewerUserId: $principal?->userId,
            viewerShopIds: $principal?->shopIds ?? [],
        );

        $output = $useCase->execute($input);

        return response()->json([
    'items' => array_map(fn ($dto) => $dto->toArray(), $output->items),
    'meta' => [
        'page'    => $output->currentPage,
        'total'   => $output->total,
        'hasNext' => $output->hasNext,
    ],
]);
    }
}