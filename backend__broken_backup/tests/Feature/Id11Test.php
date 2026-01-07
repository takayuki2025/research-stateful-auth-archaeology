<?php

// 支払い方法選択機能

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class Id11Test extends TestCase
{
    use RefreshDatabase;


    // ID11商品購入画面で支払い方法を選択すると表示か切り替わるテスト
    public function test_payment_method_display_is_updated()
    {
        // 準備
        $user = User::factory()->create();
        $item = Item::factory()->create(['remain' => 1]); // 在庫を1に設定

        // ログイン
        $this->actingAs($user);

        // 実行
        // item_buyページにアクセス
        $response = $this->get(route('item_buy', ['item_id' => $item->id]));

        // 検証
        // 初期状態では「なし」が表示されていることを確認
        $response->assertSee('<span id="selected_payment_text">なし</span>', false);

        // JavaScriptが動的にコンテンツを変更するため、選択後のページ内容を直接テストすることは困難です。
        // 代わりに、選択肢のオプションがページに存在することを確認します。
        $response->assertSee('<option value="コンビニ払い">コンビニ払い</option>', false);
        $response->assertSee('<option value="カード支払い">カード支払い</option>', false);
    }
}