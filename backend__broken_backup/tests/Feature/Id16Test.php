<?php

// （応用）メール認証機能のテスト

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

class Id16Test extends TestCase
{
    use RefreshDatabase;


    // ID16-1(1)ユーザー登録時に認証メールが送信されることをテストします。
    public function test_a_verification_email_is_sent_on_user_registration()
    {
        // Notificationファサードをモックする
        Notification::fake();

        // ユーザー登録をシミュレートするPOSTリクエストを送信
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 登録されたユーザーを取得
        $user = User::where('email', 'test@example.com')->first();

        // 認証メールが送信されたことをアサート
        Notification::assertSentTo(
            [$user], VerifyEmail::class
        );
    }


    // ID16-1(２)未認証ユーザーが認証通知ページにリダイレクトされることをテストします。
    public function test_unverified_user_is_redirected_to_verification_notice()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertStatus(200);
    }


    // ID16-1(３)再送ボタンが2通目の認証メールを送信することをテストします。
    public function test_resend_button_sends_a_second_verification_email()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'));

        // 通知が送信されたことをアサート
        Notification::assertSentTo(
            [$user], VerifyEmail::class
        );
    }


    // ID16-1(４)未認証ユーザーがメール認証ページからMailHogにリダイレクトされることをテストします。
    public function test_unverified_user_is_redirected_to_mailhog_from_verification_page()
    {
        // メール未認証のユーザーを作成します。
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 作成したユーザーとして認証し、認証通知ページにアクセスします。
        $response = $this->actingAs($user)->get(route('verification.notice'));

        // ページのコンテンツを検証します。
        // ここでは、指定されたHTMLのリンクが正しく存在し、MailHogのURLを指しているかを確認します。
        $response->assertSee('<a href="http://localhost:8025" target="_blank" class="verification-button">認証はこちらから</a>', false);
    }


    // ID16-1(５)認証メールリンクをクリックした後、ユーザーがプロファイル編集ページにリダイレクトされることをテストします。
    public function test_user_is_redirected_to_profile_edit_page_after_email_verification()
    {
        // 1. 未認証のユーザーを作成する
        $user = User::factory()->create([
            'email_verified_at' => null, // 未認証の状態
        ]);

        // 2. 認証イベントが発行されないようにモックする（リダイレクトテストに集中するため）
        Event::fake([Verified::class]);

        // 3. ユーザーの認証リンクURLを生成する
        // Laravelの認証URLは署名付きURLなので、`URL::temporarySignedRoute`を使用します
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60), // リンクの有効期限
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // 4. 未認証ユーザーとして認証し、生成したURLにアクセスする
        $response = $this->actingAs($user)->get($verificationUrl);

        // 5. 正しいページにリダイレクトされたことをアサートする
        // `route('profile_edit')`にリダイレクトされることを検証
        $response->assertRedirect(route('profile_edit'));

        // 6. リダイレクト後にセッションに「verified」キーがセットされていることをアサートする
        $response->assertSessionHas('verified', true);

        // 7. ユーザーが本当に認証済みになったことをデータベースで検証する
        $this->assertNotNull($user->fresh()->email_verified_at);
    }


    // ID16-1(６)認証済みユーザーが認証リンクにアクセスした場合、プロフィール編集ページにリダイレクトされることをテストします。
    public function test_verified_user_redirects_to_profile_page()
    {
        // 1. 既に認証済みのユーザーを作成する
        $user = User::factory()->create([
            'email_verified_at' => now(), // 認証済みの状態
        ]);

        // 2. 認証リンクURLを生成する
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // 3. 認証済みユーザーとして生成したURLにアクセスする
        $response = $this->actingAs($user)->get($verificationUrl);

        // 4. プロファイルページにリダイレクトされることをアサートする
        // `verify`メソッドの実装に合わせて、`/mypage/profile`へのリダイレクトを確認します。
        $response->assertRedirect('/mypage/profile');
    }


    // ID16(追加：メール認証が済んでいないと出品、購入ができないテスト)メール認証済みのユーザーが商品出品ページにアクセスできるかテストします。
    public function test_authenticated_and_verified_user_can_access_sell_page()
    {
        $user = User::factory()->verified()->create();

        $response = $this->actingAs($user)->get(route('item_sell'));

        $response->assertStatus(200);
        $response->assertViewIs('item_sell');
    }


    // メール未認証のユーザーが商品出品ページにアクセスしようとすると、ログインページにリダイレクトされるかテストします。
    public function test_authenticated_but_unverified_user_is_redirected_from_sell_page_to_login()
    {
        $user = User::factory()->create([
            'email_verified_at' => null, // メール未認証の状態
        ]);

        $response = $this->actingAs($user)->get(route('item_sell'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }


    // メール認証済みのユーザーが商品購入ページにアクセスできるかテストします。
    public function test_authenticated_and_verified_user_can_access_buy_page()
    {
        $user = User::factory()->verified()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get(route('item_buy', ['item_id' => $item->id]));

        $response->assertStatus(200);
        $response->assertViewIs('item_buy');
    }


    // メール未認証のユーザーが商品購入ページにアクセスしようとすると、ログインページにリダイレクトされるかテストします。
    public function test_authenticated_but_unverified_user_is_redirected_from_buy_page_to_login()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get(route('item_buy', ['item_id' => $item->id]));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }


    // ゲストユーザーが商品出品ページにアクセスしようとすると、ログインページにリダイレクトされるかテストします。
    public function test_guest_is_redirected_from_sell_page()
    {
        $response = $this->get(route('item_sell'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }


    // ゲストユーザーが商品購入ページにアクセスしようとすると、ログインページにリダイレクトされるかテストします。
    public function test_guest_is_redirected_from_buy_page()
    {
        $item = Item::factory()->create();
        $response = $this->get(route('item_buy', ['item_id' => $item->id]));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}