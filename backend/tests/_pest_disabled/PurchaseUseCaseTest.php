<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Purchase\PurchaseUseCase;
use App\Domain\Repository\ItemRepository;
use App\Domain\Repository\OrderHistoryRepository;
use App\Application\Port\StripePaymentPort;
use App\Domain\Entity\Item;
use Exception;

//
// 1️⃣ 正常系：購入成功
//
it('completes purchase flow successfully', function () {
    $userId = 1;
    $itemId = 10;

    $item = new Item(
        id: 10,
        name: "Camera",
        price: 15000,
        remain: 3
    );

    $items = mock(ItemRepository::class);
    $orders = mock(OrderHistoryRepository::class);
    $stripe = mock(StripePaymentPort::class);

    $items->shouldReceive('find')->with($itemId)->andReturn($item);
    $orders->shouldReceive('exists')->with($userId, $itemId)->andReturn(false);
    $stripe->shouldReceive('createCheckoutSession')
           ->with($userId, $itemId, 15000)
           ->andReturn("sess_abc123");

    // 注文作成
    $orders->shouldReceive('create')->with($userId, $itemId, 15000)->andReturn(true);

    // 在庫減少
    $items->shouldReceive('decreaseStock')->with($itemId)->andReturn(true);

    $useCase = new PurchaseUseCase($items, $orders, $stripe);

    $result = $useCase->purchase($userId, $itemId);

    expect($result['session_id'])->toBe("sess_abc123");
    expect($result['result'])->toBeTrue();
});


//
// 2️⃣ 在庫不足 → SOLD_OUT
//
it('throws SOLD_OUT when remain is zero', function () {
    $itemId = 5;
    $userId = 1;

    $item = new Item(
        id: 5,
        name: "Book",
        price: 1000,
        remain: 0
    );

    $items = mock(ItemRepository::class)
        ->shouldReceive('find')
        ->with($itemId)
        ->andReturn($item)
        ->getMock();

    $useCase = new PurchaseUseCase($items, mock(OrderHistoryRepository::class), mock(StripePaymentPort::class));

    expect(fn () => $useCase->purchase($userId, $itemId))
        ->toThrow(Exception::class, "SOLD_OUT");
});


//
// 3️⃣ 二重購入 → ALREADY_PURCHASED
//
it('throws ALREADY_PURCHASED when order already exists', function () {
    $userId = 10;
    $itemId = 5;

    $item = new Item(
        id: 5,
        name: "PC",
        price: 80000,
        remain: 5
    );

    $items = mock(ItemRepository::class);
    $orders = mock(OrderHistoryRepository::class);

    $items->shouldReceive('find')->with($itemId)->andReturn($item);
    $orders->shouldReceive('exists')->with($userId, $itemId)->andReturn(true);

    $useCase = new PurchaseUseCase($items, $orders, mock(StripePaymentPort::class));

    expect(fn () => $useCase->purchase($userId, $itemId))
        ->toThrow(Exception::class, "ALREADY_PURCHASED");
});


//
// 4️⃣ Stripe 側の例外（外部APIエラー）
//
it('throws when Stripe session creation fails', function () {
    $userId = 10;
    $itemId = 7;

    $item = new Item(
        id: 7,
        name: "Bag",
        price: 3200,
        remain: 2
    );

    $items = mock(ItemRepository::class);
    $orders = mock(OrderHistoryRepository::class);
    $stripe = mock(StripePaymentPort::class);

    $items->shouldReceive('find')->with($itemId)->andReturn($item);
    $orders->shouldReceive('exists')->with($userId, $itemId)->andReturn(false);

    // Stripe error
    $stripe->shouldReceive('createCheckoutSession')
           ->with($userId, $itemId, 3200)
           ->andThrow(new Exception("Stripe error"));

    $useCase = new PurchaseUseCase($items, $orders, $stripe);

    expect(fn () => $useCase->purchase($userId, $itemId))
        ->toThrow(Exception::class, "Stripe error");
});


//
// 5️⃣ Item が存在しない → ITEM_NOT_FOUND
//
it('throws ITEM_NOT_FOUND when item does not exist', function () {
    $userId = 9;
    $itemId = 9999;

    $items = mock(ItemRepository::class)
        ->shouldReceive('find')
        ->with($itemId)
        ->andReturn(null)
        ->getMock();

    $useCase = new PurchaseUseCase($items, mock(OrderHistoryRepository::class), mock(StripePaymentPort::class));

    expect(fn () => $useCase->purchase($userId, $itemId))
        ->toThrow(Exception::class, "ITEM_NOT_FOUND");
});
