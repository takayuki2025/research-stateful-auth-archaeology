<?php

// コンビニ払いを選択して購入ボタンを押すと購入処理されて完了するテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\OrderHistory;

class Id10Test extends TestCase
{
    use RefreshDatabase;


    //ID10-1コンビニ払いで商品を購入できることをテスト
    public function test_authenticated_user_can_purchase_with_convenience_store_payment()
    {
        // 事前準備: 認証済みユーザーと購入対象の商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create(['remain' => 1, 'price' => 1000]);

        // 実行: 認証済みユーザーとして購入リクエストを送信
        $response = $this->actingAs($user)->post('/thanks_buy', [
            'item_id' => $item->id,
            'payment' => 'コンビニ払い',
            'address' => '東京都港区',
        ]);

        // 検証1: リダイレクトが成功し、ステータスコードが302であることを確認
        $response->assertStatus(302);
        $response->assertRedirect('/thanks_buy');

        // 検証2: order_historiesテーブルにデータが追加されたことを確認
        // 'address' と 'status' カラムが存在しないため、検証から除外
        $this->assertDatabaseHas('order_histories', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment' => 'コンビニ払い',
        ]);

        // 検証3: itemsテーブルの在庫が1減っていることを確認
        $this->assertDatabaseHas('items', ['id' => $item->id, 'remain' => 0]);
    }



    //ID10-2購入済みの商品（在庫が0の商品）はSoldと表示されることをテスト
    public function test_sold_out_item_is_displayed_as_sold()
    {
        // 事前準備: 認証済みユーザーと在庫が0の商品を作成
        $user = User::factory()->create();
        $soldOutItem = Item::factory()->create(['remain' => 0]);

        // 実行: 認証済みユーザーとして在庫切れ商品の購入ページにアクセス
        $response = $this->actingAs($user)->get("/purchase/{$soldOutItem->id}");

        // 検証: ページに「sold」というテキストが含まれていることを確認
        // 購入ページで在庫切れの場合、購入ボタンが表示されず、代わりに「sold」が表示されることを想定
        $response->assertStatus(200);
        $response->assertSee('sold');
    }


    // ID10-3購入した商品タブを選択すると、マイページの購入した商品が表示されることをテスト
    public function test_purchased_items_are_displayed_when_buy_tab_is_selected()
    {
        // 1. 事前準備：テストユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'remain' => 0, // 在庫を0にして購入済みにする
        ]);

        // 2. ユーザーの購入履歴を作成
        OrderHistory::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment' => 'コンビニ支払い',
            // buy_address が必須のため、ダミーデータを追加
            'buy_address' => 'テスト住所',
        ]);

        // 3. 認証済みユーザーとしてマイページにアクセス（購入タブを選択）
        $response = $this->actingAs($user)->get('/mypage?page=buy');

        // 4. 検証：ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 5. 検証：ページに購入した商品の名前が表示されていることを確認
        $response->assertSee($item->name);

        // 6. 検証：ページに「購入した商品」というテキストが表示されていることを確認
        $response->assertSee('購入した商品');
    }
}