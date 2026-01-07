<?php

namespace App\Modules\User\Domain\Repository;

interface MypageRepository
{
    // ProfileUseCase に置き換わらない、Mypage特有のメソッドを定義
    public function listSellItems(int $userId): array;

    public function listBoughtItems(int $userId): array;

    public function findAddressForm(int $userId, int $itemId): array;

    public function updateAddress(
        int $userId,
        int $itemId,
        string $postNumber,
        string $address,
        ?string $building
    ): bool;
}
