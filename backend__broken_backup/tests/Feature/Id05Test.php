<?php

// マイリスト一覧取得のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Good;

class Id05Test extends TestCase
{
    use RefreshDatabase;


        // * マイリストタブにアクセスした際、ユーザーがいいねした商品のみが表示されることをテストします。
    // ID05-1ユーザーがいいねした商品のみが表示されることをテストします。
    public function test_only_liked_items_are_displayed_on_mylist_tab()
    {
        // 1. テスト用のユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログインユーザーがいいねした商品と、いいねしていない商品を作成
        // データベースの文字数制限に収まるように、短くて一意な商品名を設定
        $likedItem = Item::factory()->create(['name' => 'Liked Item']);
        Good::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // 3. 他のユーザーがいいねした商品も作成（表示されないことを確認するため）
        $otherUser = User::factory()->create();
        $otherUserLikedItem = Item::factory()->create(['name' => 'Other User Item']);
        Good::factory()->create([
            'user_id' => $otherUser->id,
            'item_id' => $otherUserLikedItem->id,
        ]);
        
        // 4. ログインユーザーがいいねしていない、全く関係のない商品を作成
        $unlikedItem = Item::factory()->create(['name' => 'Unliked Item']);

        // 5. マイリストタブにアクセス
        $response = $this->get('/?tab=mylist');

        // 6. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('front_page');

        // いいねした商品名が表示されていることを確認
        $response->assertSeeText($likedItem->name);

        // 他のユーザーがいいねした商品が表示されていないことを確認
        $response->assertDontSeeText($otherUserLikedItem->name);
        
        // ログインユーザーがいいねしていない商品が表示されていないことを確認
        $response->assertDontSeeText($unlikedItem->name);
    }


    // ID05-2マイリストで、購入済みの商品に'sold'が表示されることをテストします。
    public function test_sold_item_is_displayed_correctly_on_mylist()
    {
        // 1. テスト用のユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログインユーザーがいいねした、在庫が0の商品を作成
        $soldLikedItem = Item::factory()->create(['remain' => 0]);
        Good::factory()->create([
            'user_id' => $user->id,
            'item_id' => $soldLikedItem->id,
        ]);

        // 3. ログインユーザーがいいねした、在庫が1の商品を作成（比較用）
        $unsoldLikedItem = Item::factory()->create(['remain' => 1]);
        Good::factory()->create([
            'user_id' => $user->id,
            'item_id' => $unsoldLikedItem->id,
        ]);

        // 4. マイリストタブにアクセス
        $response = $this->get('/?tab=mylist');

        // 5. レスポンスの検証
        $response->assertStatus(200);

        // 購入済み（在庫0）の商品名と'sold'が正しく表示されていることを確認
        $response->assertSeeTextInOrder([$soldLikedItem->name, 'sold']);

        // 在庫がある商品の名前が表示されていることを確認
        $response->assertSeeText($unsoldLikedItem->name);

        // 在庫がある商品に'sold'が表示されていないことを確認
        $response->assertDontSeeText($unsoldLikedItem->name . 'sold', false);
    }



    //ID05-3未認証のユーザーがマイリストにアクセスした場合商品が何も表示されないテストします。
    public function test_unauthenticated_user_sees_no_items_on_mylist_tab()
    {
        // 1. 未認証のままマイリストタブにアクセス
        // actingAs()は使用しない
        $response = $this->get('/?tab=mylist');

        // 2. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('front_page');

        // ビューに渡されたアイテムの件数が0であることを確認
        $response->assertViewHas('items', function ($viewItems) {
            return $viewItems->count() === 0;
        });
    }
}