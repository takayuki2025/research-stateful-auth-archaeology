<?php

namespace App\Modules\Reaction\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Reaction\Application\UseCase\Command\AddFavoriteUseCase;
use App\Modules\Reaction\Application\UseCase\Command\RemoveFavoriteUseCase;
use App\Modules\Reaction\Application\UseCase\Query\IsFavoritedUseCase;
use App\Modules\Reaction\Application\UseCase\Query\ListFavoriteUseCase;
use App\Modules\Reaction\Application\UseCase\Query\CountFavoritesUseCase;

final class FavoriteController extends Controller
{
    public function __construct(
        private ListFavoriteUseCase $listFavorites,
    ) {
    }

    /**
     * GET /api/items/favorite
     * auth.jwt.optional 前提
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'items' => [],
            ], 200);
        }

        $items = $this->listFavorites->execute($user->id);

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * POST /api/items/{itemId}/favorite
     */
    public function add(
        AddFavoriteUseCase $add,
        CountFavoritesUseCase $count,
        Request $request,
        int $itemId
    ) {
        $userId = $request->user()->id;

        $add->execute($userId, $itemId);

        return response()->json([
            'favorited' => true,
            'favorites_count' => $count->execute($itemId),
        ]);
    }

    /**
     * DELETE /api/items/{itemId}/favorite
     */
    public function remove(
        RemoveFavoriteUseCase $remove,
        CountFavoritesUseCase $count,
        Request $request,
        int $itemId
    ) {
        $userId = $request->user()->id;

        $remove->execute($userId, $itemId);

        return response()->json([
            'favorited' => false,
            'favorites_count' => $count->execute($itemId),
        ]);
    }
}
