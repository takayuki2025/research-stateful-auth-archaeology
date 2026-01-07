<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Item\ItemUseCase;
use App\Domain\Entity\Item;
use App\Domain\Repository\ItemRepository;

use function Pest\Mock\mock;

beforeEach(function () {
    //
});

/**
 * 🔍 検索：キーワードなし（全件取得）
 */
it('returns all items when keyword is null', function () {

    $expected = [
        new Item(id: 1, name: 'Apple', price: 100, remain: 5),
        new Item(id: 2, name: 'Banana', price: 200, remain: 0),
    ];

    $mock = mock(ItemRepository::class)
        ->shouldReceive('search')
        ->with(null)
        ->andReturn($expected)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->getAllItems(null);

    expect($result)->toHaveCount(2);
    expect($result[0]->name)->toBe('Apple');
});

/**
 * 🔍 検索：キーワード検索（部分一致）
 */
it('filters items by keyword', function () {

    $keyword = 'app';

    $expected = [
        new Item(id: 1, name: 'Apple', price: 100, remain: 5),
    ];

    $mock = mock(ItemRepository::class)
        ->shouldReceive('search')
        ->with($keyword)
        ->andReturn($expected)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->getAllItems($keyword);

    expect($result)->toHaveCount(1);
    expect($result[0]->name)->toBe('Apple');
});

/**
 * 🔍 詳細：存在する商品を取得
 */
it('finds a single item by id', function () {

    $expected = new Item(id: 5, name: 'Camera', price: 5000, remain: 10);

    $mock = mock(ItemRepository::class)
        ->shouldReceive('find')
        ->with(5)
        ->andReturn($expected)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->find(5);

    expect($result)->not->toBeNull();
    expect($result->name)->toBe('Camera');
});

/**
 * 🔍 詳細：存在しない商品は null
 */
it('returns null when item does not exist', function () {

    $mock = mock(ItemRepository::class)
        ->shouldReceive('find')
        ->with(999)
        ->andReturn(null)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->find(999);

    expect($result)->toBeNull();
});

/**
 * 🔍 在庫チェック：在庫がある場合 true
 */
it('returns true when stock is available', function () {

    $mock = mock(ItemRepository::class)
        ->shouldReceive('getStock')
        ->with(10)
        ->andReturn(3)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->checkStock(10);

    expect($result)->toBeTrue();
});

/**
 * 🔍 在庫チェック：在庫ゼロは false
 */
it('returns false when stock is zero', function () {

    $mock = mock(ItemRepository::class)
        ->shouldReceive('getStock')
        ->with(10)
        ->andReturn(0)
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->checkStock(10);

    expect($result)->toBeFalse();
});

/**
 * 🔍 在庫チェック：商品自体が存在しない → false（購入不可）
 */
it('returns false when item does not exist in stock check', function () {

    $mock = mock(ItemRepository::class)
        ->shouldReceive('getStock')
        ->with(999)
        ->andReturn(null)  // Repository側が null の可能性あり
        ->getMock();

    $useCase = new ItemUseCase($mock);

    $result = $useCase->checkStock(999);

    expect($result)->toBeFalse();
});
