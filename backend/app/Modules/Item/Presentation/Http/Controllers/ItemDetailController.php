<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCase\Item\Query\GetItemDetailUseCase;
use App\Modules\Item\Presentation\Http\Resources\ItemDetailResource;
use App\Modules\Auth\Application\Context\AuthContext;
use Illuminate\Http\Request;

final class ItemDetailController extends Controller
{
    public function __invoke(
        int $id,
        GetItemDetailUseCase $useCase,
        AuthContext $authContext, // ★ 追加
    ) {
        $principal = $authContext->principalOrNull(); // ★ ここが正解

        $viewerUserId = $principal?->userId();

        $output = $useCase->execute($id, $viewerUserId);

        return response()->json([
            'item'            => ItemDetailResource::fromReadModel($output->item),
            'comments'        => $output->comments,
            'is_favorited'    => $output->isFavorited,
            'favorites_count' => $output->favoritesCount,
        ]);
    }
}
