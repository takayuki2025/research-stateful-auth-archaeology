<?php

// 商品検索機能のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Good;


class Id06Test extends TestCase
{
    use RefreshDatabase;


    // ID06-01部分一致商品検索機能が正しく動作することをテストします
    public function test_item_search_function_works_correctly_on_all_tab()
    {
        // 1. 検索キーワードに「テスト」を含む商品と、含まない商品を作成
        $matchingItem1 = Item::factory()->create(['name' => 'テスト商品A']);
        $nonMatchingItem = Item::factory()->create(['name' => '検索にヒットしない商品']);
        $matchingItem2 = Item::factory()->create(['name' => '商品テスト']);

        // 2. 検索クエリ「テスト」を付けておすすめタブにアクセス
        // これはコントローラーの`all_item_search`に対応します。
        $searchQuery = 'テスト';
        $response = $this->get('/?all_item_search=' . $searchQuery);

        // 3. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('front_page');

        // 検索にヒットする商品名が表示されていることを確認
        $response->assertSeeText($matchingItem1->name);
        $response->assertSeeText($matchingItem2->name);

        // 検索にヒットしない商品名が表示されていないことを確認
        $response->assertDontSeeText($nonMatchingItem->name);
    }


    // ID06-2検索状態がマイリストでも持続されることをテストします。
    public function test_search_query_is_persisted_on_mylist_tab()
    {
        // 1. テスト用のユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 検索クエリ「テスト」に一致する、ログインユーザーがいいねした商品を作成
        $likedAndMatchingItem = Item::factory()->create(['name' => 'テスト商品A']);
        Good::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedAndMatchingItem->id,
        ]);

        // 3. 検索クエリに一致するが、いいねしていない商品を作成
        $nonLikedAndMatchingItem = Item::factory()->create(['name' => 'テスト商品B']);

        // 4. マイリストタブにアクセスし、検索クエリも同時に指定
        $searchQuery = 'テスト';
        $response = $this->get('/?tab=mylist&all_item_search=' . $searchQuery);

        // 5. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('front_page');

        // いいねした上で検索に一致する商品が表示されていることを確認
        $response->assertSeeText($likedAndMatchingItem->name);

        // いいねしていない商品は、検索に一致しても表示されていないことを確認
        $response->assertDontSeeText($nonLikedAndMatchingItem->name);
    }
}