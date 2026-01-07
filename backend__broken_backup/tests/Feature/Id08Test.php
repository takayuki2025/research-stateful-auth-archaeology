<?php

// いいね機能テスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Good;

class Id08Test extends TestCase
{
    use RefreshDatabase;

    // ID08-１(１)、ID08-３(1)、 いいねの登録と解除が正しく行われることをテストします。
    public function test_logged_in_user_can_favorite_an_item_and_unfavorite_it()
    {
        // 準備
        // テスト用のユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーを認証状態にする
        $this->actingAs($user);

        // 実行（1回目：いいね登録）
        // いいね登録ルートにPOSTリクエストを送信
        $response = $this->post(route('item.favorite', ['item' => $item->id]));

        // 検証
        // goodsテーブルにいいねレコードが作成されたことを確認
        $this->assertDatabaseHas('goods', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 元のページに戻るリダイレクトを確認
        $response->assertRedirect();

        // 実行（2回目：いいね解除）
        // もう一度同じルートにPOSTリクエストを送信
        $response = $this->post(route('item.favorite', ['item' => $item->id]));

        // 検証
        // goodsテーブルからいいねレコードが削除されたことを確認
        $this->assertDatabaseMissing('goods', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 再び元のページに戻るリダイレクトを確認
        $response->assertRedirect();
    }


    // ID08-1(２) いいね登録時にgoodsテーブルにレコードが作成されることをテストします。
    public function test_good_is_created_for_new_favorite()
    {
        // 準備
        // テスト用のユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーを認証状態にする
        $this->actingAs($user);

        // 実行
        // いいね登録ルートにPOSTリクエストを送信
        $this->post(route('item.favorite', ['item' => $item->id]));

        // 検証
        // goodsテーブルに新しいレコードが正しく作成されたことを確認
        $this->assertDatabaseHas('goods', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }


    // ID08-1(３) いいね解除時に既存のレコードがgoodsテーブルから削除されることをテストします。
    public function test_existing_good_is_deleted_when_unfavorited()
    {
        // 準備
        // テスト用のユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // 事前にいいねレコードを作成しておく
        Good::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // ユーザーを認証状態にする
        $this->actingAs($user);

        // 実行
        // いいね解除ルートにPOSTリクエストを送信
        $this->post(route('item.favorite', ['item' => $item->id]));

        // 検証
        // goodsテーブルから既存のレコードが削除されたことを確認
        $this->assertDatabaseMissing('goods', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    //ID08-1(４)いいね合計値が増加するテスト
    public function test_good_count_increases_when_item_is_favorited()
    {
        // テスト用のユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーを認証状態にする
        $this->actingAs($user);

        // いいねする前のgoodsテーブルのレコード数を取得
        $initialGoodsCount = Good::count();

        // いいね登録ルートにPOSTリクエストを送信
        $this->post(route('item.favorite', ['item' => $item->id]));

        // いいね後のgoodsテーブルのレコード数を取得
        $finalGoodsCount = Good::count();

        // goodsテーブルのレコード数が1増加したことを確認
        $this->assertEquals($initialGoodsCount + 1, $finalGoodsCount);
    }


    //ID08-２(１)いいね登録後に塗りつぶされた星が表示されるかテストします。
    public function test_favorited_item_shows_filled_heart()
    {
        // 準備
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // いいねを事前に登録
        Good::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user);

        // 実行
        $response = $this->get(route('item_detail', ['item_id' => $item->id]));

        // 検証
        $response->assertSee('★', false);
        $response->assertDontSee('⭐︎');
    }

    //ID08-２(２)いいねしていないときに中抜きの星が表示されるかテストします。
    public function test_unfavorited_item_shows_empty_heart()
    {
        // 準備
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $this->actingAs($user);

        // 実行
        $response = $this->get(route('item_detail', ['item_id' => $item->id]));

        // 検証
        $response->assertSee('⭐︎', false);
        $response->assertDontSee('★');
    }


    //ID08-3(2)いいね合計値が減少するテスト。
    public function test_good_count_decreases_when_item_is_unfavorited()
    {
        // テスト用のユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーを認証状態にする
        $this->actingAs($user);

        // goodsテーブルにいいねレコードを事前に作成
        Good::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // いいね解除前のgoodsテーブルのレコード数を取得
        $initialGoodsCount = Good::count();

        // いいね解除ルートにPOSTリクエストを送信
        $this->post(route('item.favorite', ['item' => $item->id]));

        // いいね解除後のgoodsテーブルのレコード数を取得
        $finalGoodsCount = Good::count();

        // goodsテーブルのレコード数が1減少したことを確認
        $this->assertEquals($initialGoodsCount - 1, $finalGoodsCount);
    }
}