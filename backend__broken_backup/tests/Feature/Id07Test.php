<?php

// 商品詳細情報取得テスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Good;
use App\Models\Comment;

class Id07Test extends TestCase
{
    use RefreshDatabase;


    //ID7商品詳細ページがすべての情報を正しく表示し、コメントしたユーザーの画像も表示されることをテストします。
    public function test_item_detail_page_displays_all_information_and_comment_user_images_correctly()
    {
        // 1. テスト用のユーザーと商品、いいね、コメントを作成
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2つのカテゴリーをJSON形式の文字列として作成
        $categories = ['カテゴリー1', 'カテゴリー2'];
        $item = Item::factory()->create([
            'category' => json_encode($categories),
            'brand' => 'ブランドA',
            'remain' => 1,
            'condition' => '新品同様',
        ]);

        // いいねを作成
        Good::factory()->count(5)->create(['item_id' => $item->id]);

        // コメントユーザーを作成し、それぞれに異なるuser_imageを設定
        $commentUser1 = User::factory()->create([
            'name' => '加藤 結衣',
            'user_image' => 'images/profiles/kato-yui.jpg'
        ]);
        $commentUser2 = User::factory()->create([
            'name' => '原田 裕樹',
            'user_image' => 'images/profiles/harada-yuki.png'
        ]);

        Comment::factory()->create([
            'item_id' => $item->id,
            'user_id' => $commentUser1->id,
            'comment' => 'これは素晴らしい商品ですね！'
        ]);
        Comment::factory()->create([
            'item_id' => $item->id,
            'user_id' => $commentUser2->id,
            'comment' => '購入を検討しています。'
        ]);

        // 2. 商品詳細ページにアクセス
        $response = $this->get('/item/' . $item->id);

        // 3. レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('item_detail');

        // 各情報が正しく表示されているかを確認
        $response->assertSeeText($item->name);
        $response->assertSeeText('ブランド名');
        $response->assertSeeText($item->brand);
        $response->assertSeeText('¥' . number_format($item->price));
        $response->assertSeeText($item->explain);
        $response->assertSeeText($item->condition);

        // カテゴリーが正しく表示されているかを確認
        foreach ($categories as $category) {
            $response->assertSeeText($category);
        }

        // いいね数とコメント数が正しく表示されているかを確認
        $response->assertSeeText('5');
        $response->assertSeeText('2');

        // コメントとユーザー名が正しい順序で表示されていることを確認
        $response->assertSeeTextInOrder([
            '加藤 結衣',
            'これは素晴らしい商品ですね！',
            '原田 裕樹',
            '購入を検討しています。',
        ]);

        // コメントしたユーザー名が正しく表示されていることを確認
        $response->assertSeeText('加藤 結衣');
        $response->assertSeeText('原田 裕樹');

        // コメントしたユーザーのプロフィール画像の存在を検証
        // asset()ヘルパが生成するURLを正規表現で検証
        $response->assertSee(asset($commentUser1->user_image), false);
        $response->assertSee(asset($commentUser2->user_image), false);
    }
}