<?php

// 商品一覧取得のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;

class Id04Test extends TestCase
{
    use RefreshDatabase;


    //ID4-1全商品を取得できるテスト
    public function test_all_items_are_displayed_on_front_page()
    {
        // 1. テスト用のユーザーと商品データを作成
        $items = Item::factory()->count(3)->create();

        // 2. フロントページにアクセス
        $response = $this->get('/');

        // 3. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('front_page');

        // ビューに 'items' という変数名でデータが渡されていることを確認
        // また、渡された件数が、作成した商品データと一致することを確認
        $response->assertViewHas('items', function ($viewItems) use ($items) {
            return $viewItems->count() === $items->count();
        });

        // 作成した各商品の名前がページ内に表示されていることを確認
        foreach ($items as $item) {
            $response->assertSeeText($item->name);
        }
    }



    //ID4-2購入済みの商品はsoldと表示されるテスト
    public function test_sold_item_is_displayed_correctly()
    {
        // 1. 在庫が0の商品を作成
        $soldItem = Item::factory()->create(['remain' => 0]);
        // 2. 在庫がある商品も作成（比較用）
        $unsoldItem = Item::factory()->create(['remain' => 1]);

        // 3. フロントページにアクセス
        $response = $this->get('/');

        // 4. レスポンスの検証
        $response->assertStatus(200);

        // 未購入の商品名が表示され、その後にsoldは表示されないことを確認
        $response->assertSeeText($unsoldItem->name);
        $response->assertDontSeeText($unsoldItem->name . 'sold', false);

        // 購入済みの商品名と、その後に'sold'というテキストが正しく表示されていることを確認
        $response->assertSeeTextInOrder([$soldItem->name, 'sold']);
    }



    //ID4-3自分の出品した商品は表示されないテスト
    public function test_user_is_not_displayed_their_own_items_on_all_tab()
    {
        // 1. テスト用のユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログインユーザーが出品した商品と、他のユーザーが出品した商品を作成
        // 'at' という文字列を含まない商品名を使用
        $myOwnItem = Item::factory()->create(['user_id' => $user->id, 'name' => 'My Item for sale']);
        $otherUserItem = Item::factory()->create(); // user_idは自動で別のIDが割り当てられる

        // 3. フロントページ（'all'タブ）にアクセス
        $response = $this->get('/');

        // 4. レスポンスの検証
        $response->assertStatus(200);

        // 他のユーザーが出品した商品が表示されていることを確認
        $response->assertSeeText($otherUserItem->name);

        // ログインユーザーが出品した商品が、表示されていないことを確認
        $response->assertDontSeeText($myOwnItem->name);
    }
}
