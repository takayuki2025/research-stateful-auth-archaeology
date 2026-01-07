<?php

// ユーザー情報取得テスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\OrderHistory;

class Id13Test extends TestCase
{
    use RefreshDatabase;


    //ID13-1マイページに移動して、ユーザー情報と出品商品が表示されることを確認するテスト。
    public function test_my_page_displays_user_info_and_selling_items()
    {
        // テストユーザーを作成
        $user = User::factory()->create([
            'user_image' => 'https://example.com/user_image.jpg' // テスト用の画像URL
        ]);

        // ログイン
        $this->actingAs($user);

        // ユーザーが出品した商品を複数作成
        $sellingItems = Item::factory()->count(2)->create(['user_id' => $user->id]);

        // マイページにアクセス
        $response = $this->get('/mypage');

        // レスポンスが成功したことを確認
        $response->assertStatus(200);

        // ユーザー名とユーザー画像が表示されていることを確認
        $response->assertSee($user->name);
        $response->assertSee($user->user_image);

        // 出品した商品が複数表示されていることを確認
        $response->assertSee($sellingItems[0]->name);
        $response->assertSee($sellingItems[1]->name);
    }


    // ID13-1マイページで「購入した商品」が正しく表示されることを確認するテスト。
    public function test_my_page_displays_purchased_items()
    {
        // テストユーザーを作成
        $user = User::factory()->create();

        // ログイン
        $this->actingAs($user);

        // 購入済み商品を作成
        // 別のユーザーが販売した商品として作成
        $seller = User::factory()->create();
        $purchasedItem = Item::factory()->create(['user_id' => $seller->id]);

        // OrderHistoryレコードを作成し、ユーザーが商品を購入したことをシミュレート
        OrderHistory::create([
            'item_id' => $purchasedItem->id,
            'user_id' => $user->id,
            'payment' => 'credit_card', // 支払い方法
            'buy_address' => '東京都渋谷区' // 購入時の住所
        ]);

        // マイページの「購入した商品」タブにアクセス
        $response = $this->get('/mypage?page=buy');

        // レスポンスが成功したことを確認
        $response->assertStatus(200);

        // 購入済み商品が表示されていることを確認
        $response->assertSee($purchasedItem->name);
    }
}