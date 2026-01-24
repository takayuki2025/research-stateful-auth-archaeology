<?php

namespace App\Modules\User\Infrastructure\Persistence\Repository;

use App\Models\Item;
use App\Modules\User\Domain\Repository\MypageRepository;
use Illuminate\Support\Facades\DB;

final class EloquentMypageRepository implements MypageRepository
{
    /**
     * 出品した商品一覧
     *
     * ✔ USER_PERSONAL のみ
     * ✔ created_by_user_id が自分のものだけ
     * ✔ SHOP_MANAGED（Seeder商品）は一切出さない
     */
    public function listSellItems(int $userId): array
{
    // ユーザーが関与しているショップID一覧
    $shopIds = DB::table('role_user')
        ->where('user_id', $userId)
        ->pluck('shop_id')
        ->filter()
        ->toArray();

    return Item::query()

        ->where('name', 'not like', 'SEED_DUMMY__%')

        ->where(function ($q) use ($userId, $shopIds) {
            // 個人出品
            $q->where(function ($q) use ($userId) {
                $q->where('item_origin', 'user_personal')
                  ->where('created_by_user_id', $userId);
            });

            // ショップ出品（関与ショップ）
            if (!empty($shopIds)) {
                $q->orWhere(function ($q) use ($shopIds) {
                    $q->where('item_origin', 'shop_managed')
                      ->whereIn('shop_id', $shopIds);
                });
            }
        })
        ->orderByDesc('id')
        ->get()
        ->map(fn ($item) => [
            'row_id'     => 'sell-' . $item->id,
            'item_id'    => $item->id,
            'name'       => $item->name,
            'item_image' => $item->item_image,
            'price'      => $item->price,
            'item_origin'=> $item->item_origin,
            'shop_id'    => $item->shop_id,
        ])
        ->toArray();
}
    /**
     * 購入した商品一覧
     * ※ ここは Order 側が完成するまで既存ロジック維持でOK
     */
    public function listBoughtItems(int $userId): array
    {
        return DB::table('order_histories')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($row) => [
                'row_id'     => $row->order_id . '-' . $row->item_id,
                'item_id'    => $row->item_id,
                'name'       => $row->item_name,      // ✅ 修正
                'item_image' => $row->item_image,
                'order_id'   => $row->order_id,
                'price'      => $row->price_amount,   // ✅ 修正
            ])
            ->toArray();
    }

    /**
     * 購入前の住所フォーム
     */
    public function findAddressForm(int $userId, int $itemId): array
    {
        $user = \App\Models\User::findOrFail($userId);
        $item = Item::findOrFail($itemId);

        return [
            'user' => [
                'id'          => $user->id,
                'post_number' => $user->post_number,
                'address'     => $user->address,
                'building'    => $user->building,
            ],
            'item' => [
                'id'    => $item->id,
                'name'  => $item->name,
                'price' => $item->price,
            ],
        ];
    }

    /**
     * 住所更新
     */
    public function updateAddress(
        int $userId,
        int $itemId,
        string $postNumber,
        string $address,
        ?string $building
    ): bool {
        return \App\Models\User::where('id', $userId)->update([
            'post_number' => $postNumber,
            'address'     => $address,
            'building'    => $building,
        ]) > 0;
    }
}
