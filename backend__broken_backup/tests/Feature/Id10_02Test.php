<?php

// （応用）カード支払いを選択して購入ボタンを押すと購入処理されて完了するテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\OrderHistory;

class Id10_02Test extends TestCase
{
    // テストごとにデータベースをリフレッシュ（初期化）するLaravelの機能
    use RefreshDatabase;
    // テスト用のダミーデータを生成するLaravelの機能
    use WithFaker;


    // ID10-02(1)ログイン中のユーザーがカード決済Stripeでアイテムを購入できることをテストします。
    public function test_logged_in_user_can_purchase_item_with_stripe()
    {
        // 準備（Setup）
        // テスト用のユーザーとアイテムをデータベースに作成する
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'remain' => 1, // 在庫を1に設定
            'price' => 1000 // 価格を1000円に設定
        ]);

        // 作成したユーザーとしてログイン状態をシミュレートする
        $this->actingAs($user);

        // thanks_buy_createルートで必須となる配送先住所のデータを組み立てる
        // この形式は、Stripeへのリダイレクト前にバリデーションで求められる形式と一致させる
        $address = "{$user->name}\n{$user->post_number}\n{$user->address}\n{$user->building}";

        // 実行
        // `thanks_buy_create`ルートに購入リクエストを送信する
        // 支払い方法を「カード支払い」とし、必要な住所情報も一緒に送る
        $response = $this->post(route('thanks_buy_create'), [
            'item_id' => $item->id,
            'payment' => 'カード支払い',
            'address' => $address, // バリデーションエラーを回避するため、配送先住所を追加
        ]);

        // 検証（Assertion）
        // Stripeへのリダイレクトが正しく行われたか検証する
        // `303`ステータスコードは、POSTリクエスト後のリダイレクトが成功したことを示す
        $response->assertStatus(303);
    }


    // ID10-02(２)Stripeでの決済成功後の処理が、購入を正しく完了することをテストします。
    public function test_stripe_success_method_correctly_completes_purchase()
    {
        // 準備（Setup）
        // `buy_address`の作成に必要な情報を含むテスト用ユーザーを作成する
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'post_number' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
        ]);

        // 在庫数が1のテスト用アイテムを作成する
        $item = Item::factory()->create([
            'remain' => 1,
            'price' => 1000,
        ]);

        // 作成したユーザーとしてログイン状態をシミュレートする
        $this->actingAs($user);

        // 検証に使用する期待値（データベースに保存されるはずのデータ）を定義する
        // コントローラーで生成される`buy_address`の形式と一致させる
        $expectedBuyAddress = "{$user->name}\n{$user->post_number}\n{$user->address}\n{$user->building}";
        $expectedOrderHistoryData = [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'buy_address' => $expectedBuyAddress,
            'payment' => 'カード支払い',
        ];

        // 実行
        // `stripe_success`ルートにGETリクエストを送信する
        // ルート定義がGETメソッドであるため、`post()`ではなく`get()`を使用
        $response = $this->get(route('stripe_success', [
            'item_id' => $item->id,
        ]));

        // 検証（Assertion）
        // `order_histories`テーブルに新しい購入履歴が、期待通りのデータで作成されたか確認する
        $this->assertDatabaseHas('order_histories', $expectedOrderHistoryData);

        // アイテムの在庫数が1つ減り、0になったか確認する
        $this->assertEquals(0, Item::find($item->id)->remain);

        // 購入完了後に`thanks_buy`ルートへリダイレクトされたか確認する
        $response->assertRedirect(route('thanks_buy'));
    }
}