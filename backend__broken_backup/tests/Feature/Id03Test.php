<?php

// ログアウト機能のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class Id03Test extends TestCase
{
    use RefreshDatabase;


 //ID03-１（１）ログインしているユーザーが正常にログアウトできることをテスト
    public function test_authenticated_user_can_logout()
    {

        // 1. テスト用のユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();

        // actingAs()は、指定したユーザーとしてHTTPリクエストを実行するためのヘルパーメソッド
        $this->actingAs($user);

        // ユーザーが認証されていることを確認（前提条件の検証）
        $this->assertAuthenticated();

        // 2. ログアウト用のルートにPOSTリクエストを送信

    //ID03-１（２）ログイン中のユーザーがログアウトできるかのテスト
        $response = $this->post('/logout');

        // 3. ログアウトが成功し、リダイレクトされることを検証
        $response->assertStatus(302);

        // ログアウト後のリダイレクト先（通常はトップページ）を確認
        $response->assertRedirect('/');

        // ユーザーが認証されていない状態に戻ったことを確認
        $this->assertGuest();
    }
}