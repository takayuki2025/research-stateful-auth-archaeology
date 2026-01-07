<?php

// 出品商品情報登録のテスト

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use PHPUnit\Framework\Attributes\DataProvider; // PHP Attributes のための追加

class Id15Test extends TestCase
{
    use RefreshDatabase;
    use WithFaker;


    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // ログイン済みのテストユーザーを作成
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }


    // ID15-1ログインユーザーが商品出品を正常に完了できるかテスト。
    public function test_authenticated_user_can_sell_an_item_successfully()
    {
        // テスト用の画像ファイルを生成
        $fakeImage = UploadedFile::fake()->image('item.jpg');

        // 商品画像のアップロードフォームをテスト
        $this->post('/upload', ['item_image' => $fakeImage])
            ->assertSessionHas('image_path');

        // セッションから画像パスを取得
        $imagePath = session('image_path');

        // 有効な商品データ
        $itemData = [
            '_token' => csrf_token(),
            'item_image' => $imagePath,
            'name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 99999),
            'brand' => $this->faker->word,
            'explain' => $this->faker->sentence,
            'condition' => '良好',
            'category' => ['ファッション', 'メンズ'],
        ];

        // 商品出品フォームを送信
        $response = $this->post('/thanks_sell', $itemData);

        // 正常にビューが表示されることを確認
        $response->assertStatus(200);
        $response->assertViewIs('thanks_sell');

        // データベースに商品が正しく保存されていることを確認
        // categoryはJSON形式で保存されるため、エンコードして比較する
        $expectedDatabaseData = array_merge($itemData, [
            'user_id' => $this->user->id,
            'remain' => 1,
            'category' => json_encode($itemData['category']),
        ]);
        unset($expectedDatabaseData['_token']);

        $this->assertDatabaseHas('items', $expectedDatabaseData);
    }


    #[DataProvider('validationDataProvider')] // Doc-commentからAttributesに修正
    // バリデーションエラーが発生するケースをテスト。
    public function test_item_submission_validation_fails_with_invalid_data(array $data, string $field, string $message)
    {
        // バリデーションが失敗することをテスト
        $response = $this->post('/thanks_sell', $data);

        // 元のページにリダイレクトされることを確認
        $response->assertStatus(302);

        // 指定されたフィールドでバリデーションエラーが発生し、期待されるメッセージが含まれていることを確認
        $response->assertSessionHasErrors([$field => $message]);
    }


    // バリデーションテスト用のデータプロバイダ。
    public static function validationDataProvider(): array
    {
        // テスト用のダミーデータを生成
        $validData = [
            'item_image' => 'path/to/image.jpg',
            'name' => 'テスト商品名',
            'price' => 5000,
            'brand' => 'ブランド名',
            'explain' => '商品の説明文です。',
            'condition' => '良好',
            'category' => ['家電'],
        ];

        return [
            // name バリデーション
            'nameが未入力' => [array_merge($validData, ['name' => null]), 'name', '商品名を入力してください。'],
            'nameが21文字以上' => [array_merge($validData, ['name' => str_repeat('a', 21)]), 'name', '名前を20文字以下で入力してください。'],

            'brandが21文字以上' => [array_merge($validData, ['brand' => str_repeat('a', 21)]), 'brand', 'ブランド名は20文字以下で入力してください。'],

            // price バリデーション
            'priceが未入力' => [array_merge($validData, ['price' => null]), 'price', '金額を入力してください。'],
            'priceが数値ではない' => [array_merge($validData, ['price' => 'abc']), 'price', '数値で入力してください。'],
            'priceが100円未満' => [array_merge($validData, ['price' => 99]), 'price', '１００円以上の金額で入力してください。'],
            'priceが2000000001円以上' => [array_merge($validData, ['price' => 2000000001]), 'price', '２０億円以下の金額で入力してください。'],

            // explain バリデーション
            'explainが未入力' => [array_merge($validData, ['explain' => null]), 'explain', '商品説明を入力してください。'],
            'explainが256文字以上' => [array_merge($validData, ['explain' => str_repeat('a', 256)]), 'explain', '商品説明を２５５文字以内で入力してください。'],

            // condition バリデーション
            'conditionが未選択' => [array_merge($validData, ['condition' => null]), 'condition', '商品状態を選択してください。'],

            // category バリデーション
            'categoryが未選択' => [array_merge($validData, ['category' => null]), 'category', 'カテゴリーを選択してください。'],
            'categoryが空の配列' => [array_merge($validData, ['category' => []]), 'category', 'カテゴリーを選択してください。'],

            // item_image バリデーション
            'item_imageが未入力' => [array_merge($validData, ['item_image' => null]), 'item_image', '商品画像ファイルをアップロードしてください。'],
        ];
    }
}