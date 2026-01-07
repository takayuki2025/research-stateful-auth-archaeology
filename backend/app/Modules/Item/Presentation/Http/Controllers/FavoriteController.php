<?php

namespace App\Modules\Item\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Item\Application\UseCase\Favorite\ToggleFavoriteUseCase;
use App\Modules\Item\Application\UseCase\Favorite\ListFavoriteUseCase;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧
     */
    public function index(ListFavoriteUseCase $useCase, Request $request)
    {
        $userId = $request->user()->id;

        $favorites = $useCase->execute($userId);

        return response()->json([
            'favorites' => $favorites
        ]);
    }

    /**
     * お気に入り登録
     */
    public function add(ToggleFavoriteUseCase $useCase, Request $request, int $itemId)
    {
        $userId = $request->user()->id;

        $result = $useCase->execute($userId, $itemId, true);

        return response()->json([
            'favorited' => true,
            'message' => 'お気に入りに追加しました',
            'favorites_count' => $result['favorites_count'],
        ]);
    }

    /**
     * お気に入り解除
     */
    public function remove(ToggleFavoriteUseCase $useCase, Request $request, int $itemId)
    {
        $userId = $request->user()->id;

        $result = $useCase->execute($userId, $itemId, false);

        return response()->json([
            'favorited' => false,
            'message' => 'お気に入りを削除しました',
            'favorites_count' => $result['favorites_count'],
        ]);
    }
}
