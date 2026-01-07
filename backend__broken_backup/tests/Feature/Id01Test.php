<?php

// 会員登録機能のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session; // Sessionファサードをインポート

class Id01Test extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テストメソッドの前に実行される処理
     * 日本語バリデーションメッセージを確実にするためロケールを設定
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. テスト環境のロケールを「ja」に設定
        App::setLocale('ja');

        // 2. セッションを強制的に開始し、ロケール設定を適用させる
        //    これにより、Laravelが使用する多言語メッセージが日本語としてロードされることを保証します。
        Session::start();
    }


    // ID1無効なデータでバリデーションが失敗するテスト
    public function test_registration_with_empty_email_fails_validation_with_specific_message()
    {
        // -----------------------------------------------------------
        // ID1-1　名前が入力されていない場合
        // -----------------------------------------------------------
        $response = $this->post('/register', [
            'name' => '', //名前を空にする
            'email' => 'valid.email@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        // バリデーションエラーによって302が返されることを確認（リダイレクト）
        $response->assertStatus(302);
        
        // nameフィールドに特定の日本語メッセージがあることを確認
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください。']);


        // -----------------------------------------------------------
        // ID1-2　メールアドレスが入力されていない場合
        // -----------------------------------------------------------
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '', // メールアドレスを空にする
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);


        // -----------------------------------------------------------
        // ID1-3　パスワードが入力されていない場合
        // -----------------------------------------------------------
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'valid.email@example.com',
            'password' => '', //パスワードをからにする
            'password_confirmation' => 'password',
        ]);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);


        // -----------------------------------------------------------
        // ID1-4　パスワードが７文字以下の場合
        // -----------------------------------------------------------
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'valid.email@example.com',
            'password' => 'pass',//パスワードを４文字にする
            'password_confirmation' => 'password',
        ]);
        
        $response->assertStatus(302);
        // パスワードの文字数不足に関する日本語メッセージを確認
        // min:8のルールに対するメッセージであることを想定
        $response->assertSessionHasErrors(['password' => 'パスワードは８文字以上で入力してください。']);


        // -----------------------------------------------------------
        // ID1-5　パスワードと確認パスワードが違う場合
        // -----------------------------------------------------------
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'valid.email@example.com',
            'password' => 'password',
            'password_confirmation' => 'password2233',//パスワードと違う入力をする
        ]);
        
        $response->assertStatus(302);
        // confirmedルールに関する日本語メッセージを確認
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません。']);

    }


    // ID1-６　入力正常用のアクション
    public function test_registration_with_valid_data_is_successful()
    {
        // setUp()でロケールが設定済みのため、ここでは不要。
        
        // ID1-6　全ての項目を入力して次の画面に移動する場合
        // 有効なデータで登録リクエストを送信
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'valid.email@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 登録後、意図したページにリダイレクトされるか確認
        $response->assertRedirect('/onetime');//応用のメール認証を実装したので、メール認証確認画面に移動するかテスト

        // セッションにエラーメッセージがないことを確認
        $response->assertSessionDoesntHaveErrors();

        // データベースからユーザーを取得
        $user = DB::table('users')->where('email', 'valid.email@example.com')->first();

        // ユーザーが存在することと、パスワードが正しくハッシュ化されていることを確認
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
    }


    public function test_non_existent_route_returns_404()
    {
        $response = $this->post('/no_route');
        $response->assertStatus(404);
    }
}
