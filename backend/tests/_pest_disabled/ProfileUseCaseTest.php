<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Profile\ProfileUseCase;
use App\Domain\Repository\ProfileRepository;

beforeEach(function () {
    $this->userId = 10;
});

/**
 * プロフィール取得
 */
it('gets user profile', function () {
    $expected = [
        'id' => $this->userId,
        'name' => 'Taro',
        'email' => 'taro@example.com',
        'user_image' => '/img/user.png',
    ];

    $mock = mock(ProfileRepository::class)
        ->shouldReceive('find')
        ->with($this->userId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new ProfileUseCase($mock);

    $result = $useCase->getProfile($this->userId);

    expect($result['name'])->toBe('Taro');
});

/**
 * プロフィール更新
 */
it('updates user profile', function () {
    $updateData = [
        'name'        => 'Hanako',
        'post_number' => '100-0001',
        'address'     => 'Tokyo',
        'building'    => 'Test Mansion 101',
        'user_image'  => '/storage/user.png',
    ];

    $mock = mock(ProfileRepository::class)
        ->shouldReceive('update')
        ->with($this->userId, $updateData)
        ->andReturn(true)
        ->getMock();

    $useCase = new ProfileUseCase($mock);

    $result = $useCase->updateProfile($this->userId, $updateData);

    expect($result)->toBeTrue();
});

/**
 * プロフィール画像のみ更新（user_image）
 */
it('updates user image only', function () {
    $updateData = [
        'user_image' => '/storage/new-user.png',
    ];

    $mock = mock(ProfileRepository::class)
        ->shouldReceive('update')
        ->with($this->userId, $updateData)
        ->andReturn(true)
        ->getMock();

    $useCase = new ProfileUseCase($mock);

    $result = $useCase->updateProfile($this->userId, $updateData);

    expect($result)->toBeTrue();
});
