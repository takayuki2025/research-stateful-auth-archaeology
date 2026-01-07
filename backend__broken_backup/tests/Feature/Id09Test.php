<?php

// コメント送信機能テスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class Id09Test extends TestCase
{
    use RefreshDatabase;
    use WithFaker;


    //ID09-1(1)認証ユーザーがコメントを送信した時に保存されることをテスト
    public function test_authenticated_user_can_submit_a_comment_and_it_is_saved()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $commentText = 'これはテストコメントです。';

        // ログイン
        $this->actingAs($user);

        // コメントを送信
        $response = $this->post(route('comment_create'), [
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);

        // 成功時のリダイレクトを確認
        $response->assertRedirect(route('item_detail', ['item_id' => $item->id]));
        $response->assertSessionHas('success', 'コメントが送信されました。');

        // データベースにコメントが保存されたことを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);
    }


    //ID09-1(2)コメント送信後にコメントの総数が増加することをテスト
    public function test_comment_count_increases_after_submission()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $commentText = 'これは2番目のテストコメントです。';

        // ログイン
        $this->actingAs($user);

        // コメント送信前のコメント数を取得
        $initialCommentCount = Comment::count();

        // コメントを送信
        $this->post(route('comment_create'), [
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);

        // コメント送信後のコメント数を取得
        $finalCommentCount = Comment::count();

        // コメント数が1増えたことを確認
        $this->assertEquals($initialCommentCount + 1, $finalCommentCount);
    }


    // ID09-2未認証ユーザーはコメントフォームを見ることができないことをテスト
    public function test_unauthenticated_user_cannot_see_comment_form()
    {
        // テストに必要なデータを作成
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('item_detail', ['item_id' => $item->id]));

        $response->assertStatus(200);
        $response->assertSee('コメント');
        $response->assertDontSee('商品へのコメント'); // コメントフォームのヘッダーが表示されないことを確認
    }


    // ID09-3コメント入力のバリデーションエラーメッセージが正しいことをテスト（未入力）
    public function test_validation_message_for_required_comment_is_correct()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->from(route('item_detail', ['item_id' => $item->id]))->post(route('comment_create'), [
            'item_id' => $item->id,
            'comment' => '',
        ]);

        // セッションに正しいエラーメッセージが存在することを確認
        $response->assertSessionHasErrors(['comment' => 'コメントを入力してください。']);
    }


    // ID09-4コメント入力のバリデーションエラーメッセージが正しいことをテスト（文字数超過）
    public function test_validation_message_for_max_length_is_correct()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $longComment = str_repeat('a', 256);

        $response = $this->from(route('item_detail', ['item_id' => $item->id]))->post(route('comment_create'), [
            'item_id' => $item->id,
            'comment' => $longComment,
        ]);

        // セッションに正しいエラーメッセージが存在することを確認
        $response->assertSessionHasErrors(['comment' => '２５５文字以内で入力してください。']);
    }
}