<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Domain\Repository\MypageRepository;

final class MypageUseCase
{
    public function __construct(
        private MypageRepository $mypages
    ) {
    }

    /**
     * 出品した商品一覧（自分のものだけ）
     */
    public function listSellItems(int $userId): array
    {
        return $this->mypages->listSellItems($userId);
    }

    /**
     * 購入した商品一覧
     */
    public function listBoughtItems(int $userId): array
    {
        return $this->mypages->listBoughtItems($userId);
    }

    public function getAddressForm(int $userId, int $itemId): array
    {
        return $this->mypages->findAddressForm($userId, $itemId);
    }

    public function updateAddress(
        int $userId,
        int $itemId,
        string $postNumber,
        string $address,
        ?string $building
    ): bool {
        return $this->mypages->updateAddress(
            $userId,
            $itemId,
            $postNumber,
            $address,
            $building
        );
    }
}
