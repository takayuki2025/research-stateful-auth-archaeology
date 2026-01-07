<?php

// ログイン機能のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Id02Test extends TestCase
{
    use RefreshDatabase;

    //　ID02有効な資格情報でログインが成功するテスト
    public function test_login_validation_with_specific_message()
    {
        // テスト用のユーザーを事前にデータベースに作成
        // パスワードはFortifyが自動でハッシュ化します
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),//テスト用に事前に登録してあるようにしています。
        ]);


        // ログインリクエストを送信

    //ID2-1ログイン時メールアドレスが空の場合
        $response = $this->post('/login', [
            'email' => '',//メールアドレスが空の状態の場合
            'password' => 'password',
        ]);

        // バリデーションエラーによって302が返されることを確認
        $response->assertStatus(302);
        // emailフィールドに特定のメッセージがあることを確認
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);


    //ID2-2ログイン時パスワードが空の場合
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',//パスワードが空の状態の場合
        ]);

        // バリデーションエラーによって302が返されることを確認
        $response->assertStatus(302);
        // emailフィールドに特定のメッセージがあることを確認
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);


    //ID2-3(1)ログイン時アドレスは合っているがパスワードが間違っている時
        $response = $this->post('/login', [
            'email' => 'test123456789@example.com',//登録メールアドレスが間違っていてパスワードがあっている場合
            'password' => 'password',
        ]);

        // バリデーションエラーによって302が返されることを確認
        $response->assertStatus(302);
        // emailフィールドに特定のメッセージがあることを確認
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません。']);

    }


    //  * 有効な資格情報でログインが成功するテスト
    public function test_login_with_valid_credentials_is_successful()
    {
        // テスト用のユーザーを事前にデータベースに作成
        // パスワードはFortifyが自動でハッシュ化します
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),//テスト用に事前に登録してあるようにしています。
        ]);

        // ログインリクエストを送信
    //ID2-4ログイン時登録情報を正常に入力した場合
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password', //テスト用のユーザー入力
        ]);

        // ログイン後、意図したページにリダイレクトされるか確認
        $response->assertStatus(302);
        $response->assertRedirect('/onetime');//どんな時のログイン画面でも/onetimeで処理するようにしています。

        // ユーザーが認証された状態になったことを確認
        $this->assertAuthenticated();
    }
}