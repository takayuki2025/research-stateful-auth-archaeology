<?php

// ユーザー情報変更のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class Id14Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // テストユーザーを作成し、認証状態にする
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }


    // ID14-1(1)全ての入力が有効な場合にプロフィール更新が成功するかテスト
    public function test_profile_update_succeeds_with_valid_data()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => 'Updated User Name',
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => 'テストビル',
        ]);

        // 正しくリダイレクトされたか、セッションに成功メッセージがあるかを確認
        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'プロフィールを更新しました');

        // データベースが正しく更新されたことを確認
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated User Name',
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => 'テストビル',
        ]);
    }

    // ID14-1(2)郵便番号のフォーマットが無効な場合にバリデーションが失敗するかテスト
    public function test_profile_update_validation_fails_with_invalid_post_number_format()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => 'Test User',
            'post_number' => 'invalid-format', // 無効な郵便番号
            'address' => '東京都',
        ]);
        // 特定のエラーメッセージを確認するように修正
        $response->assertSessionHasErrors([
            'post_number' => 'ハイフンありの８桁で入力してください。',
        ]);
    }


    // ID14-1(3)建物名が空の場合でもプロフィール更新が成功するかテスト
    public function test_profile_update_succeeds_without_building_name()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => 'Updated User Name',
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => '', // 建物名は空でもOK
        ]);

        // 正しくリダイレクトされたか、セッションに成功メッセージがあるかを確認
        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'プロフィールを更新しました');

        // データベースが正しく更新されたことを確認
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated User Name',
            'post_number' => '123-4567',
            'address' => '東京都港区',
            'building' => null,
        ]);
    }


    // ID14-1(4)プロフィール画像のバリデーションメッセージを検証
    public function test_user_image_mimes_validation_message()
    {
        // storage/app/public/images/tmp に画像を保存する設定
        Storage::fake('public');

        // 無効なファイルタイプ（テキストファイル）を作成
        $file = UploadedFile::fake()->create('document.txt', 10, 'text/plain');

        // 画像アップロードのPOSTリクエストをシミュレート
        $response = $this->post('/upload2', [
            'user_image' => $file,
        ]);

        // 指定されたエラーメッセージがセッションに存在するか確認
        $response->assertSessionHasErrors([
            'user_image' => 'ユーザー画像ファイルは.jpegまたは.png形式でアップロードしてください。',
        ]);

        // リダイレクトを確認
        $response->assertStatus(302);
    }


    // ID14-1(5)名前の未入力時のバリデーションメッセージを検証
    public function test_name_required_validation_message()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => '',
            'post_number' => '123-4567',
            'address' => '東京都',
        ]);

        $response->assertSessionHasErrors([
            'name' => '名前を入力してください。',
        ]);
    }


    // ID14-1(6)名前の最大文字数超過時のバリデーションメッセージを検証
    public function test_name_max_validation_message()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => str_repeat('a', 21), // 21文字の文字列
            'post_number' => '123-4567',
            'address' => '東京都',
        ]);

        $response->assertSessionHasErrors([
            'name' => '名前は20文字以内で入力してください。',
        ]);
    }


    // ID14-1(7)郵便番号の未入力時のバリデーションメッセージを検証
    public function test_post_number_required_validation_message()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => 'Test User',
            'post_number' => '',
            'address' => '東京都',
        ]);

        $response->assertSessionHasErrors([
            'post_number' => '郵便番号を入力してください。',
        ]);
    }


    // ID14-1(8)住所の未入力時のバリデーションメッセージを検証
    public function test_address_required_validation_message()
    {
        $response = $this->patch('/profile_update', [ // URLを'/profile_update'に修正
            'name' => 'Test User',
            'post_number' => '123-4567',
            'address' => '',
        ]);

        $response->assertSessionHasErrors([
            'address' => '住所を入力してください。',
        ]);
    }


    // ID14-1(9)プロフィール編集フォームにユーザー情報が初期値として入力されているかテスト
    public function test_profile_edit_form_is_pre_populated_with_user_data()
    {
        // 認証済みのユーザーとしてプロフィール編集ページにアクセス
        $response = $this->actingAs($this->user)->get('/mypage/profile');

        // ページが正常に表示されることを確認
        $response->assertStatus(200);

        // ビューにユーザー情報が渡されているかを確認
        $response->assertViewHas('user', function ($viewUser) {
            return $viewUser->name === $this->user->name &&
                $viewUser->post_number === $this->user->post_number &&
                $viewUser->address === $this->user->address;
        });

        // HTMLコンテンツ内に各フィールドのvalue属性が正しく設定されていることを確認
        $response->assertSee('value="' . e($this->user->name) . '"', false);
        $response->assertSee('value="' . e($this->user->post_number) . '"', false);
        $response->assertSee('value="' . e($this->user->address) . '"', false);
    }
}