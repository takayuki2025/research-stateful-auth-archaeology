<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Mypage\MypageUseCase;
use App\Domain\Repository\MypageRepository;

beforeEach(function () {
    $this->userId = 10;
    $this->itemId = 5;
});

/**
 * 出品一覧（sell items）
 */
it('lists sell items', function () {
    $expected = [
        ['id' => 1, 'name' => 'Book'],
        ['id' => 2, 'name' => 'Shoes'],
    ];

    $mock = mock(MypageRepository::class)
        ->shouldReceive('listSellItems')
        ->with($this->userId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new MypageUseCase($mock);

    $result = $useCase->listSellItems($this->userId);

    expect($result)->toHaveCount(2);
});

/**
 * 購入済み一覧（bought items）
 */
it('lists bought items', function () {
    $expected = [
        ['id' => 9, 'name' => 'Camera'],
        ['id' => 12, 'name' => 'Laptop'],
    ];

    $mock = mock(MypageRepository::class)
        ->shouldReceive('listBoughtItems')
        ->with($this->userId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new MypageUseCase($mock);

    $result = $useCase->listBoughtItems($this->userId);

    expect($result)->toHaveCount(2);
});

/**
 * 住所編集の取得
 */
it('gets address form for purchase', function () {
    $expected = [
        'item_id' => 5,
        'user_id' => 10,
        'post_number' => '150-0001',
        'address' => 'Shibuya',
        'building' => 'Hikarie 10F',
    ];

    $mock = mock(MypageRepository::class)
        ->shouldReceive('findAddressForm')
        ->with($this->userId, $this->itemId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new MypageUseCase($mock);

    $result = $useCase->getAddressForm($this->userId, $this->itemId);

    expect($result['address'])->toBe('Shibuya');
});

/**
 * 住所更新
 */
it('updates address for purchase', function () {
    $data = [
        'post_number' => '160-0022',
        'address' => 'Shinjuku',
        'building' => 'Tower 55',
    ];

    $mock = mock(MypageRepository::class)
        ->shouldReceive('updateAddress')
        ->with(
            userId: $this->userId,
            itemId: $this->itemId,
            postNumber: $data['post_number'],
            address: $data['address'],
            building: $data['building']
        )
        ->andReturn(true)
        ->getMock();

    $useCase = new MypageUseCase($mock);

    $result = $useCase->updateAddress($this->userId, $this->itemId, $data);

    expect($result)->toBeTrue();
});
