<?php

// 配送先変更機能に伴い画面の表示の切り替えとコンビニ払い時の購入完了処理のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Route;

class Id12Test extends TestCase
{
    use RefreshDatabase;

    // 各テスト実行前にアプリケーションをリフレッシュして状態汚染を防ぐ
    protected function setUp(): void
    {
        $this->refreshApplication();
        parent::setUp();
    }


    // ID12-1(1)購入ページにユーザーの住所が正しく表示されることをテスト
    public function test_user_address_is_displayed_on_purchase_page()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create([
            'post_number' => '111-2222',
            'address' => '神奈川県横浜市',
            'building' => 'テストマンション101',
        ]);
        $this->actingAs($user);

        // テスト用の商品を作成
        $item = Item::factory()->create();

        // 購入ページにアクセス
        $response = $this->get(route('item_buy', ['item_id' => $item->id]));

        $response->assertStatus(200); // 正常なレスポンスを確認

        // ページにユーザーの住所が表示されていることを確認
        $response->assertSee('111-2222');
        $response->assertSee('神奈川県横浜市');
        $response->assertSee('テストマンション101');
    }


    //  ID12-1(2)住所変更ページで住所情報を変更後、購入ページに新しい住所が反映されることをテスト
    public function test_address_change_is_reflected_on_purchase_page()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create([
            'post_number' => '999-8888',
            'address' => '北海道札幌市',
            'building' => '北のビル303',
        ]);
        $this->actingAs($user);

        // テスト用の商品を作成
        $item = Item::factory()->create([
            'user_id' => $user->id
        ]);

        // 住所変更エンドポイントにリクエスト
        $response = $this->patch(route('item.purchase.update', ['item_id' => $item->id, 'user_id' => $user->id]), [
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => 'レガシービル101',
        ]);

        // データベースのユーザーモデルをリフレッシュ
        $user->refresh();

        // 住所変更後の購入ページにアクセス
        $response = $this->get(route('item_buy', ['item_id' => $item->id]));

        $response->assertStatus(200); // 正常なレスポンスを確認

        // ページに新しい住所が表示されていることを確認
        $response->assertSee('123-4567');
        $response->assertSee('東京都港区');
        $response->assertSee('レガシービル101');
    }


    // ID12-1(3)ログインユーザーが住所を更新できることをテスト
    public function test_logged_in_user_can_update_address()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // ルートの存在確認
        $this->assertTrue(Route::has('item.purchase.update'), 'Route with name item.purchase.update does not exist.');

        // ログインユーザーに関連付けられたアイテムを作成
        $item = Item::factory()->create([
            'user_id' => $user->id
        ]);

        // 購入情報更新エンドポイントにリクエスト
        $response = $this->patchJson(route('item.purchase.update', ['item_id' => $item->id, 'user_id' => $user->id]), [
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => 'レガシービル101',
        ]);

        $response->assertStatus(302); // リダイレクトを確認

        // データベースに情報が正しく保存されたことを確認
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => 'レガシービル101',
        ]);
    }


    // ID12-2コンビニ払いで購入が完了し、注文履歴にすべての関連情報が正しく保存されることをテスト
    public function test_purchase_completes_and_saves_all_data_to_order_history()
    {
        // ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'post_number' => '987-6543',
            'address' => '大阪府大阪市',
            'building' => 'テストビル202',
        ]);
        $this->actingAs($user);

        // テスト用の商品を作成
        $item = Item::factory()->create(['remain' => 1]);

        // ルートの存在確認
        $this->assertTrue(Route::has('thanks_buy_create'), 'Route with name thanks_buy_create does not exist.');

        // コンビニ払いをシミュレート
        // from()メソッドを追加することで、リクエストのリファラーを設定し、リダイレクトが不安定になるのを防ぐ
        $response = $this->from(route('item_buy', ['item_id' => $item->id]))
                        ->post(route('thanks_buy_create', ['item_id' => $item->id]), [
            'item_id' => $item->id,
            'payment' => 'コンビニ払い',
            'name' => $user->name,
            'post_number' => $user->post_number,
            'address' => $user->address,
            'building' => $user->building,
        ]);

        $response->assertSessionHasNoErrors(); // バリデーションエラーがないことを確認
        $response->assertStatus(302); // リダイレクトを確認
        $response->assertRedirect(route('thanks_buy')); // 購入後のリダイレクト先を確認

        // order_historiesテーブルにレコードが作成されたこと、および情報が正しく保存されたことを確認
        $this->assertDatabaseHas('order_histories', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment' => 'コンビニ払い',
            'buy_address' => "テストユーザー\n987-6543\n大阪府大阪市\nテストビル202",
        ]);

        // 在庫が減っていることを確認
        $updatedItem = Item::find($item->id);
        $this->assertEquals($item->remain - 1, $updatedItem->remain);
    }
}