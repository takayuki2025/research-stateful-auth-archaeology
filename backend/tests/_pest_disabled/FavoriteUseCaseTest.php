<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Favorite\FavoriteUseCase;
use App\Domain\Repository\FavoriteRepository;

beforeEach(function () {
    $this->userId = 10;
    $this->itemId = 3;
});

/**
 * お気に入り登録（オン）
 */
it('adds favorite when not yet favorited', function () {

    $mock = mock(FavoriteRepository::class);

    // isFavorited = false → createFavorite が呼ばれる
    $mock->shouldReceive('isFavorited')
        ->with($this->userId, $this->itemId)
        ->andReturn(false);

    $mock->shouldReceive('createFavorite')
        ->with($this->userId, $this->itemId)
        ->andReturn(true);

    $useCase = new FavoriteUseCase($mock);

    $result = $useCase->toggleFavorite($this->userId, $this->itemId);

    expect($result)->toBeTrue();
});

/**
 * お気に入り解除（オフ）
 */
it('removes favorite when already favorited', function () {

    $mock = mock(FavoriteRepository::class);

    // isFavorited = true → deleteFavorite が呼ばれる
    $mock->shouldReceive('isFavorited')
        ->with($this->userId, $this->itemId)
        ->andReturn(true);

    $mock->shouldReceive('deleteFavorite')
        ->with($this->userId, $this->itemId)
        ->andReturn(true);

    $useCase = new FavoriteUseCase($mock);

    $result = $useCase->toggleFavorite($this->userId, $this->itemId);

    expect($result)->toBeTrue();
});

/**
 * 明示的削除 removeFavorite()
 */
it('explicitly removes favorite', function () {

    $mock = mock(FavoriteRepository::class)
        ->shouldReceive('deleteFavorite')
        ->with($this->userId, $this->itemId)
        ->andReturn(true)
        ->getMock();

    $useCase = new FavoriteUseCase($mock);

    $result = $useCase->removeFavorite($this->userId, $this->itemId);

    expect($result)->toBeTrue();
});

/**
 * お気に入り一覧 list()
 */
it('lists user favorites', function () {

    $expected = [
        ['item_id' => 1, 'name' => 'Book'],
        ['item_id' => 2, 'name' => 'Camera'],
    ];

    $mock = mock(FavoriteRepository::class)
        ->shouldReceive('listFavorites')
        ->with($this->userId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new FavoriteUseCase($mock);

    $result = $useCase->list($this->userId);

    expect($result)->toHaveCount(2);
});
