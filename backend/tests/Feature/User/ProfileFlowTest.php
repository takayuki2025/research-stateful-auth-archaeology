<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
// UseCases
use App\Modules\User\Application\UseCase\GetProfileUseCase;
use App\Modules\User\Application\UseCase\CreateProfileUseCase;
use App\Modules\User\Application\UseCase\UpdateProfileUseCase;
// DTOs
use App\Modules\User\Application\Dto\CreateProfileInput;
use App\Modules\User\Application\Dto\UpdateProfileInput;
// Exceptions
use App\Modules\User\Domain\Exception\ProfileAlreadyExistsException;

class ProfileFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * =====================================================
     * ① Profile は明示的に作成されるまで存在しない
     * =====================================================
     */
    public function test_profile_is_not_created_until_explicit_create()
    {
        $user = User::factory()->create([
            'name' => '', // NOT NULL 対策
        ]);

        $getProfile = app(GetProfileUseCase::class);
        $this->assertNull($getProfile->handle($user->id));

        $created = app(CreateProfileUseCase::class)->handle(
            $user->id,
            new CreateProfileInput('', null, null, null)
        );

        $this->assertSame('', $created->displayName);

        $updated = app(UpdateProfileUseCase::class)->handle(
            $user->id,
            new UpdateProfileInput(
                'テスト太郎',
                '123-4567',
                '東京都',
                null
            )
        );

        $this->assertSame('テスト太郎', $updated->displayName);
    }

    /**
     * =====================================================
     * ② Profile は二重作成できない
     * =====================================================
     */
    public function test_profile_cannot_be_created_twice()
    {
        $user = User::factory()->create(['name' => '']);

        $create = app(CreateProfileUseCase::class);

        $create->handle(
            $user->id,
            new CreateProfileInput('', null, null, null)
        );

        $this->expectException(ProfileAlreadyExistsException::class);

        $create->handle(
            $user->id,
            new CreateProfileInput('', null, null, null)
        );
    }

    /**
     * =====================================================
     * ③ GetProfileUseCase 単体確認
     * =====================================================
     */
    public function test_get_profile_returns_null_or_dto()
    {
        $user = User::factory()->create(['name' => '']);

        $get = app(GetProfileUseCase::class);
        $this->assertNull($get->handle($user->id));

        app(CreateProfileUseCase::class)->handle(
            $user->id,
            new CreateProfileInput('取得テスト', null, null, null)
        );

        $profile = $get->handle($user->id);
        $this->assertSame('取得テスト', $profile->displayName);
    }

    /**
     * =====================================================
     * ④ API レベル：has_profile が正しく返る
     * =====================================================
     */
    public function test_profile_api_has_profile_flag()
{
    $this->withoutMiddleware();

    $user = User::factory()->create([
        'name' => '',
    ]);

    // ★ ここが重要：Request に user を明示注入
    $this->actingAs($user);

    // Profile なし
    $res = $this->getJson('/api/mypage/profile');

    $res->assertOk();
    $res->assertJson([
        'has_profile' => false,
    ]);

    // Profile 作成
    app(CreateProfileUseCase::class)->handle(
        $user->id,
        new CreateProfileInput(
            displayName: 'API確認',
            postNumber: null,
            address: null,
            building: null,
        )
    );

    // Profile あり
    $res = $this->getJson('/api/mypage/profile');

    $res->assertOk();
    $res->assertJson([
        'has_profile' => true,
    ]);
}
}
