<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\UseCase\Comment\CommentUseCase;
use App\Domain\Repository\CommentRepository;
use App\Domain\Entity\Comment;

//
// 1️⃣ 一覧取得（正常系）
//
it('returns comment list for an item', function () {
    $itemId = 3;

    $expected = [
        ['id' => 1, 'user_id' => 10, 'comment' => 'Good!', 'created_at' => '2024-01-01'],
        ['id' => 2, 'user_id' => 11, 'comment' => 'Nice item', 'created_at' => '2024-01-02'],
    ];

    $mock = mock(CommentRepository::class)
        ->shouldReceive('listByItem')
        ->with($itemId)
        ->andReturn($expected)
        ->getMock();

    $useCase = new CommentUseCase($mock);

    $result = $useCase->listByItem($itemId);

    expect($result)->toBeArray()
                   ->toHaveCount(2);
});


//
// 2️⃣ 投稿（正常系）
//
it('creates a new comment successfully', function () {
    $userId = 10;
    $itemId = 5;
    $commentText = "This is a test comment.";

    $entity = new Comment(
        id: 1,
        userId: $userId,
        itemId: $itemId,
        comment: $commentText,
        createdAt: now()->toDateTimeString()
    );

    $mock = mock(CommentRepository::class)
        ->shouldReceive('create')
        ->with($userId, $itemId, $commentText)
        ->andReturn($entity)
        ->getMock();

    $useCase = new CommentUseCase($mock);

    $result = $useCase->post($userId, $itemId, $commentText);

    expect($result)->toBeInstanceOf(Comment::class);
});


//
// 3️⃣ 500文字超過によるバリデーションエラー
//
it('throws exception when comment exceeds 500 chars', function () {
    $useCase = new CommentUseCase(mock(CommentRepository::class));

    $longText = str_repeat('a', 501);

    expect(fn () => $useCase->post(10, 3, $longText))
        ->toThrow(\InvalidArgumentException::class);
});


//
// 4️⃣ itemId が存在しない場合 → Repository が例外を投げる
//
it('throws exception if item does not exist', function () {
    $userId = 10;
    $itemId = 9999; // 存在しない ID
    $commentText = "test";

    $mock = mock(CommentRepository::class)
        ->shouldReceive('create')
        ->with($userId, $itemId, $commentText)
        ->andThrow(new \RuntimeException("Item not found"))
        ->getMock();

    $useCase = new CommentUseCase($mock);

    expect(fn () => $useCase->post($userId, $itemId, $commentText))
        ->toThrow(\RuntimeException::class);
});
